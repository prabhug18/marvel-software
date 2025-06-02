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
        $header = $rows->first();
        foreach ($rows->skip(1) as $row) {
            // Map columns based on export order
            $category = Category::where('name', $row[1])->first();
            $brand = Brand::where('name', $row[2])->first();
            $model = $row[3];
            $series = $row[4];
            $processor = $row[5];
            $memory = $row[6];
            $operating_system = $row[7];
            $price = is_numeric($row[8]) ? $row[8] : preg_replace('/[^\d.]/', '', $row[8]);

            if ($category && $brand && $model) {
                $product = Product::where([
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'model' => $model,
                ])->first();

                if ($product) {
                    // Update existing product
                    $product->series = $series;
                    $product->processor = $processor;
                    $product->memory = $memory;
                    $product->operating_system = $operating_system;
                    $product->price = $price;
                    $product->user_id = Auth::id() ?? 1;
                    $product->save();
                } else {
                    // Create new product
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
                    ]);
                }
            }
        }
    }
}
