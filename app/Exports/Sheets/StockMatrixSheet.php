<?php

namespace App\Exports\Sheets;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Brand;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class StockMatrixSheet implements FromCollection, WithHeadings, WithTitle
{
    protected $warehouses;

    public function __construct()
    {
        $this->warehouses = Warehouse::orderBy('id')->get();
    }

    public function collection()
    {
        // Get 5 real products as sample models
        $samples = Product::with(['category', 'brand'])
            ->limit(5)
            ->get();

        $rows = [];
        foreach ($samples as $item) {
            $row = [
                $item->category->name ?? 'N/A',
                $item->brand->name ?? 'N/A',
                $item->model,
                $item->model_no ?? '',
                '', // Sample Invoice Number
                '', // Sample Purchase Rate
            ];
            
            // Fill 0 for each warehouse
            foreach ($this->warehouses as $wh) {
                $row[] = 0;
            }

            // Total Stock and Serial Numbers (Reference columns for export, usually blank for import)
            $row[] = 0;
            $row[] = '';
            
            $rows[] = $row;
        }

        return collect($rows);
    }

    public function headings(): array
    {
        $warehouseNames = $this->warehouses->pluck('name')->toArray();
        return array_merge(
            ['Category', 'Brand', 'Model', 'Model No', 'Invoice Number', 'Purchase Rate'], 
            $warehouseNames, 
            ['Total Stock (Auto)', 'Serial Numbers (Reference)']
        );
    }

    public function title(): string
    {
        return 'Stock Matrix';
    }
}
