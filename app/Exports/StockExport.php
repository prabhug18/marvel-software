<?php

namespace App\Exports;

use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockExport implements FromCollection, WithHeadings
{
    protected $warehouses;

    public function __construct()
    {
        $this->warehouses = Warehouse::orderBy('id')->get();
    }

    public function collection()
    {
        // Get all unique combinations from product table to ensure consistency
        // Only products that exist in the master list will be shown in Stock Export
        $allCombos = \App\Models\Product::select('category_id', 'brand_id', 'model', 'model_no')
            ->groupBy('category_id', 'brand_id', 'model', 'model_no')
            ->get()
            ->map(function($item) {
                return [
                    'category_id' => $item->category_id,
                    'brand_id' => $item->brand_id,
                    'model' => $item->model,
                    'model_no' => $item->model_no,
                ];
            });

        $rows = [];
        foreach ($allCombos as $combo) {
            $row = [
                Category::find($combo['category_id'])?->name ?? 'N/A',
                Brand::find($combo['brand_id'])?->name ?? 'N/A',
                $combo['model'],
                $combo['model_no'] ?? '',
            ];
            
            $totalQty = 0;
            $allSerials = [];

            foreach ($this->warehouses as $warehouse) {
                // Sum qty to handle multiple serial entries properly
                $qtySum = Stock::where('category_id', $combo['category_id'])
                    ->where('brand_id', $combo['brand_id'])
                    ->where('model', $combo['model'])
                    ->where('model_no', $combo['model_no'])
                    ->where('warehouse_id', $warehouse->id)
                    ->where('qty', '>', 0)
                    ->sum('qty');
                
                // Collect serials for this row
                $serials = Stock::where('category_id', $combo['category_id'])
                    ->where('brand_id', $combo['brand_id'])
                    ->where('model', $combo['model'])
                    ->where('model_no', $combo['model_no'])
                    ->where('warehouse_id', $warehouse->id)
                    ->where('qty', '>', 0)
                    ->pluck('serial_no')
                    ->filter()
                    ->toArray();

                $allSerials = array_merge($allSerials, $serials);
                $row[] = (int)$qtySum;
                $totalQty += (int)$qtySum;
            }

            $row[] = $totalQty;
            $row[] = implode(', ', array_unique($allSerials));
            $rows[] = $row;
        }
        return collect($rows);
    }

    public function headings(): array
    {
        $warehouseNames = $this->warehouses->pluck('name')->toArray();
        return array_merge(['Category', 'Brand', 'Model', 'Model No'], $warehouseNames, ['Total Stock', 'Serial Numbers']);
    }
}