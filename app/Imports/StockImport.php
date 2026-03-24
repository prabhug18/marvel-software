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
        // Assuming first 4 columns are: Category, Brand, Model, Model No
        $warehouseNames = $header->slice(4)->toArray(); 

        $hasError = false;
        $errorMessages = [];
        foreach ($rows->skip(1) as $rowIndex => $row) {
            $categoryName = $row->get(0);
            $brandName = $row->get(1);
            $modelName = $row->get(2);
            $modelNo = $row->get(3);

            $category = $categoryName ? Category::where('name', $categoryName)->first() : null;
            $brand = $brandName ? Brand::where('name', $brandName)->first() : null;
            
            // Check if product exists for this category, brand, model, and model_no
            $productExists = \App\Models\Product::where('category_id', optional($category)->id)
                ->where('brand_id', optional($brand)->id)
                ->where('model', $modelName)
                ->where('model_no', $modelNo)
                ->exists();

            // Validation for missing category/brand/model or product
            $missing = [];
            if (!$category) $missing[] = 'Category: ' . ($categoryName ?? '');
            if (!$brand) $missing[] = 'Brand: ' . ($brandName ?? '');
            if (empty($modelName)) $missing[] = 'Model';
            if (!$productExists) $missing[] = 'Product (category, brand, model, model_no combination does not exist)';
            
            if (!empty($missing)) {
                $msg = 'StockImport Row '.($rowIndex + 1).': '. implode(', ', $missing);
                $hasError = true;
                $errorMessages[] = $msg;
                continue;
            }

            foreach ($warehouseNames as $i => $warehouseName) {
                $warehouse = Warehouse::where('name', $warehouseName)->first();
                $qtyIdx = $i + 4; // Start from index 4
                $qtyToAdd = (int) $row->get($qtyIdx);

                if ($warehouse && $category && $brand) {
                    $stock = Stock::where([
                        'warehouse_id' => $warehouse->id,
                        'category_id'  => $category->id,
                        'brand_id'     => $brand->id,
                        'model'        => $modelName,
                        'model_no'     => $modelNo,
                    ])->first();

                    if ($stock) {
                        $stock->qty = $qtyToAdd;
                        $stock->user_id = Auth::id() ?? 1;
                        $stock->save();
                    } else {
                        Stock::create([
                            'warehouse_id' => $warehouse->id,
                            'category_id'  => $category->id,
                            'brand_id'     => $brand->id,
                            'model'        => $modelName,
                            'model_no'     => $modelNo,
                            'qty'          => $qtyToAdd,
                            'user_id'      => Auth::id() ?? 1,
                        ]);
                    }
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