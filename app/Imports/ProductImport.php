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
                throw new \Exception('Excel file is empty or contains only headers');
            }
            $header = $rows->first();
            foreach ($rows->skip(1) as $index => $row) {
                // Defensive: check for missing columns
                if (count($row) < 9) {
                    \Log::error('ProductImport: Row '.($index+2).' has missing columns', ['row' => $row]);
                    continue;
                }
                // Map columns based on export order, trim values
                $category = Category::where('name', trim($row[1]))->first();
                $brand = Brand::where('name', trim($row[2]))->first();
                $model = trim($row[3]);
                $series = trim($row[4]);
                $processor = trim($row[5]);
                $memory = trim($row[6]);
                $operating_system = trim($row[7]);
                $price = is_numeric($row[8]) ? $row[8] : preg_replace('/[^\d.]/', '', $row[8]);

                if (!$category || !$brand || empty($model)) {
                    \Log::error('ProductImport: Required fields missing on row '.($index+2), [
                        'category' => $row[1], 'brand' => $row[2], 'model' => $model
                    ]);
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
                        $product->processor = $processor;
                        $product->memory = $memory;
                        $product->operating_system = $operating_system;
                        $product->price = $price;
                        $product->user_id = Auth::id() ?? 1;
                        $product->save();
                    } else {
                        Product::create([
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                            'model' => $model,
                            'series' => $series,
                            'processor' => $processor,
                            'memory' => $memory,
                            'operating_system' => $operating_system,
                            'price' => $price,
                            'user_id' => Auth::id() ?? 1,
                            'product_images' => '',
                            'product_images_original' => '',
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('ProductImport: Exception on row '.($index+2).': '.$e->getMessage());
                }
            }
        } catch (\Exception $e) {
            \Log::error('ProductImport: Import failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
