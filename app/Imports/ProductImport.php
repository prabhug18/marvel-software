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
                if (count($row) < 9) {
                    $hasError = true;
                    $errorMessages[] = 'Row '.($index+2).': Missing columns';
                    continue;
                }
                // Map columns based on export order, trim values
                $category = Category::where('name', trim($row[1]))->first();
                $brand = Brand::where('name', trim($row[2]))->first();
                $model = trim($row[3]);
                $series = trim($row[4]);
                $hsn_code = trim($row[5]);
                $tax_percentage = is_numeric($row[6]) ? $row[6] : null;
                $price = is_numeric($row[7]) ? $row[7] : preg_replace('/[^\u0000-\u007F]+|[^\u0000-\u007F]+/', '', $row[7]);
                $offer_price = isset($row[8]) && is_numeric($row[8]) ? $row[8] : null;
                $specification = isset($row[9]) ? trim($row[9]) : null;

                if (!$category || !$brand || empty($model)) {
                    $missing = [];
                    if (!$category) $missing[] = 'Category: ' . ($row[1] ?? '');
                    if (!$brand) $missing[] = 'Brand: ' . ($row[2] ?? '');
                    if (empty($model)) $missing[] = 'Model';
                    $msg = 'ProductImport: Required fields missing ' . implode(', ', $missing);
                    $hasError = true;
                    $errorMessages[] = $msg;
                    continue;
                }

                try {
                    $product = Product::where([
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'model' => $model,
                    ])->first();

                    if ($product) {
                        $product->series = $series;
                        $product->hsn_code = $hsn_code;
                        $product->tax_percentage = $tax_percentage;
                        $product->price = $price;
                        $product->offer_price = $offer_price;
                        $product->specification = $specification;
                        $product->user_id = Auth::id() ?? 1;
                        $product->save();
                    } else {
                        Product::create([
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                            'model' => $model,
                            'series' => $series,
                            'hsn_code' => $hsn_code,
                            'tax_percentage' => $tax_percentage,
                            'price' => $price,
                            'offer_price' => $offer_price,
                            'specification' => $specification,
                            'user_id' => Auth::id() ?? 1,
                            'product_images' => '',
                            'product_images_original' => '',
                        ]);
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
