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
        // Get all unique combinations of category, brand, model
        $stocks = Stock::select('category_id', 'brand_id', 'model')
            ->groupBy('category_id', 'brand_id', 'model')
            ->get();

        $rows = [];

        foreach ($stocks as $stock) {
            $row = [
                Category::find($stock->category_id)?->name ?? '',
                Brand::find($stock->brand_id)?->name ?? '',
                $stock->model,
            ];

            foreach ($this->warehouses as $warehouse) {
                $qty = Stock::where('category_id', $stock->category_id)
                    ->where('brand_id', $stock->brand_id)
                    ->where('model', $stock->model)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();
                  
                // Explicitly check for null, empty string, or false
                if(is_null($qty)){
                    $qty = '0';
                    $row[] = $qty;
                } elseif($qty->qty == '0') {
                    $row[] = '0';
                }else{
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
        return array_merge(['Category', 'Brand', 'Model'], $warehouseNames);
    }
}