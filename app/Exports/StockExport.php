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
                Category::find($combo['category_id'])?->name ?? '',
                Brand::find($combo['brand_id'])?->name ?? '',
                $combo['model'],
                $combo['model_no'] ?? '',
            ];
            foreach ($this->warehouses as $warehouse) {
                $qty = Stock::where('category_id', $combo['category_id'])
                    ->where('brand_id', $combo['brand_id'])
                    ->where('model', $combo['model'])
                    ->where('model_no', $combo['model_no'])
                    ->where('warehouse_id', $warehouse->id)
                    ->first();
                if (is_null($qty)) {
                    $row[] = '0';
                } elseif ($qty->qty == '0') {
                    $row[] = '0';
                } else {
                    $row[] = (int)$qty->qty;
                }
            }
            $rows[] = $row;
        }
        return collect($rows);
    }

    public function headings(): array
    {
        $warehouseNames = $this->warehouses->pluck('name')->toArray();
        return array_merge(['Category', 'Brand', 'Model', 'Model No'], $warehouseNames);
    }
}