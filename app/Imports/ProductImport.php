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
                // Defensive: check for missing columns
                if (count($row) < 10) {
                    $hasError = true;
                    $errorMessages[] = 'Row '.($index+2).': Missing columns';
                    continue;
                }
                // Map columns based on export order, trim values
                $categoryName = trim($row->get(1));
                $brandName = trim($row->get(2));
                $category = Category::where('name', $categoryName)->first();
                $brand = Brand::where('name', $brandName)->first();
                $model = trim($row->get(3));
                $model_no = trim($row->get(4));
                $series = trim($row->get(5));
                $hsn_code = trim($row->get(6));
                $tax_percentage = is_numeric($row->get(7)) ? $row->get(7) : null;
                $price_raw = $row->get(8);
                $price = is_numeric($price_raw) ? $price_raw : preg_replace('/[^\x00-\x7F]+/', '', $price_raw);
                $offer_price = ($row->get(9) !== null && is_numeric($row->get(9))) ? $row->get(9) : null;
                $specification = ($row->get(10) !== null) ? trim($row->get(10)) : null;

                if (!$category || !$brand || empty($model)) {
                    $missing = [];
                    if (!$category) $missing[] = 'Category: ' . $categoryName;
                    if (!$brand) $missing[] = 'Brand: ' . $brandName;
                    if (empty($model)) $missing[] = 'Model No';
                    $msg = 'ProductImport: Required fields missing ' . implode(', ', $missing);
                    $hasError = true;
                    $errorMessages[] = $msg;
                    continue;
                }

                try {
                    // Always create a new product as requested
                    Product::create([
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'model' => $model,
                        'model_no' => $model_no,
                        'series' => $series, // using 'series' internally as it matches DB column
                        'hsn_code' => $hsn_code,
                        'tax_percentage' => $tax_percentage,
                        'price' => $price,
                        'offer_price' => $offer_price,
                        'specification' => $specification,
                        'user_id' => Auth::id() ?? 1,
                        'product_images' => '',
                        'product_images_original' => '',
                    ]);
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
