<?php
namespace App\Imports;

use App\Models\Stock;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;

class StockImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $header = $rows->first();
        $warehouseNames = $header->slice(3)->toArray(); // Assuming first 3 columns are category, brand, model

        $hasError = false;
        $errorMessages = [];
        foreach ($rows->skip(1) as $rowIndex => $row) {
            $category = Category::where('name', $row[0])->first();
            $brand = Brand::where('name', $row[1])->first();
            $model = $row[2];
            // Check if product exists for this category, brand, and model
            $productExists = \App\Models\Product::where('category_id', optional($category)->id)
                ->where('brand_id', optional($brand)->id)
                ->where('model', $model)
                ->exists();
            $count = 0;
            // Validation for missing category/brand/model or product
            $missing = [];
            if (!$category) $missing[] = 'Category: ' . ($row[0] ?? '');
            if (!$brand) $missing[] = 'Brand: ' . ($row[1] ?? '');
            if (empty($model)) $missing[] = 'Model';
            if (!$productExists) $missing[] = 'Product (category, brand, model combination does not exist)';
            if (!empty($missing)) {
                $msg = 'StockImport: Required fields missing '. implode(', ', $missing);
                $hasError = true;
                $errorMessages[] = $msg;
                continue;
            }
            foreach ($warehouseNames as $i => $warehouseName) {
                $warehouse = Warehouse::where('name', $warehouseName)->first();
                $qty = isset($row[$count + 3]) ? $row[$count + 3] : 0;
                $qtyToAdd = (int) $qty;
                if ($warehouse && $category && $brand) {
                    $stock = Stock::where([
                        'warehouse_id' => $warehouse->id,
                        'category_id' => $category->id,
                        'brand_id' => $brand->id,
                        'model' => $model,
                    ])->first();
                    if ($stock) {
                        $stock->qty = $qtyToAdd;
                        $stock->user_id = Auth::id() ?? 1;
                        $stock->save();
                    } else {
                        Stock::create([
                            'warehouse_id' => $warehouse->id,
                            'category_id' => $category->id,
                            'brand_id' => $brand->id,
                            'model' => $model,
                            'qty' => $qtyToAdd,
                            'user_id' => Auth::id() ?? 1,
                        ]);
                    }
                    $count++;
                }
            }
        }
        
        if ($hasError && count($errorMessages) > 0) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'import' => $errorMessages
            ]);
        }
    }
}