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

        foreach ($rows->skip(1) as $row) {
            $category = Category::where('name', $row[0])->first();
            $brand = Brand::where('name', $row[1])->first();
            $model = $row[2];
            $count = 0;
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
                        // Overwrite existing qty with imported value
                        $stock->qty = $qtyToAdd;
                        $stock->user_id = Auth::id() ?? 1;
                        $stock->save();
                    } else {
                        // Create new stock
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
    }
}