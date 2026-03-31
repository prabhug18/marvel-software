<?php
namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;

class ProductImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        try {
            if ($rows->count() <= 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'import' => ['Excel file is empty or contains only headers']
                ]);
            }
            $header = $rows->first();
            $hasError = false;
            $errorMessages = [];
            foreach ($rows->skip(1) as $index => $row) {
                // Defensive: check for missing columns (now 15 columns)
                if (count($row) < 14) {
                    $hasError = true;
                    $errorMessages[] = 'Row '.($index+2).': Missing columns (expected 15)';
                    continue;
                }
                // Map columns based on export order, clean values
                $id = $row->get(0);
                $categoryName = trim($row->get(1) ?? '');
                $brandName = trim($row->get(2) ?? '');
                $category = Category::where('name', $categoryName)->first();
                $brand = Brand::where('name', $brandName)->first();
                
                $model = trim($row->get(3) ?? '');
                // Convert empty strings to null for consistent DB matching
                $model_no = ($row->get(4) !== null && trim($row->get(4)) !== '') ? trim($row->get(4)) : null;
                $series = ($row->get(5) !== null && trim($row->get(5)) !== '') ? trim($row->get(5)) : null;
                $hsn_code = ($row->get(6) !== null && trim($row->get(6)) !== '') ? trim($row->get(6)) : null;
                
                $tax_percentage = is_numeric($row->get(7)) ? $row->get(7) : null;
                $price_raw = $row->get(8);
                $price = is_numeric($price_raw) ? $price_raw : preg_replace('/[^\x00-\x7F]+/', '', $price_raw);
                $offer_price = ($row->get(9) !== null && is_numeric($row->get(9))) ? $row->get(9) : null;
                
                // New Fields
                $foc_months = ($row->get(10) !== null && trim($row->get(10)) !== '') ? trim($row->get(10)) : null;
                $prorata_months = ($row->get(11) !== null && trim($row->get(11)) !== '') ? trim($row->get(11)) : null;
                $capacity = ($row->get(12) !== null && trim($row->get(12)) !== '') ? trim($row->get(12)) : null;
                $specification = ($row->get(13) !== null && trim($row->get(13)) !== '') ? trim($row->get(13)) : null;
                $remarks = ($row->get(14) !== null && trim($row->get(14)) !== '') ? trim($row->get(14)) : null;

                if (!$category || !$brand || empty($model)) {
                    $missing = [];
                    if (!$category) $missing[] = 'Category: ' . $categoryName;
                    if (!$brand) $missing[] = 'Brand: ' . $brandName;
                    if (empty($model)) $missing[] = 'Model';
                    $msg = 'ProductImport: Required fields missing ' . implode(', ', $missing);
                    $hasError = true;
                    $errorMessages[] = $msg;
                    continue;
                }

                try {
                    // Match ONLY by Category + Brand + Model + Model No (as requested by USER)
                    $product = Product::withTrashed()
                        ->where([
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                            'model' => $model,
                            'model_no' => $model_no,
                        ])->first();

                    $data = [
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'model' => $model,
                        'model_no' => $model_no,
                        'series' => $series, // Warranty
                        'hsn_code' => $hsn_code,
                        'tax_percentage' => $tax_percentage,
                        'price' => $price,
                        'offer_price' => $offer_price,
                        'foc_months' => $foc_months,
                        'prorata_months' => $prorata_months,
                        'capacity' => $capacity,
                        'specification' => $specification,
                        'remarks' => $remarks,
                        'user_id' => Auth::id() ?? 1,
                        'product_images' => '',
                        'product_images_original' => '',
                    ];

                    if ($product) {
                        if ($product->trashed()) {
                            $product->restore();
                        }
                        $product->update($data);
                    } else {
                        Product::create($data);
                    }
                } catch (\Exception $e) {
                    $hasError = true;
                    $errorMessages[] = 'Row '.($index+2).': ' . $e->getMessage();
                }
            }
            if ($hasError && count($errorMessages) > 0) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'import' => $errorMessages
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
