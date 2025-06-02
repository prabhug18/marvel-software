<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Product::with(['category', 'brand'])->get();
    }

    public function map($product): array
    {
        return [
            $product->id,
            $product->category->name ?? '',
            $product->brand->name ?? '',
            $product->model,
            $product->series,
            $product->processor,
            $product->memory,
            $product->operating_system,
            $product->price           
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Category',
            'Brand',
            'Model',
            'Series',
            'Processor',
            'Memory',
            'Operating System',
            'Price'
        ];
    }
}
