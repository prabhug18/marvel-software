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
            $product->model_no,
            $product->series,
            $product->hsn_code,
            $product->tax_percentage,
            $product->price,
            $product->offer_price,
            $product->foc_months,
            $product->prorata_months,
            $product->capacity,
            $product->specification,
            $product->remarks,
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'Category',
            'Brand',
            'Model',
            'Model No',
            'Warranty',
            'HSN Code',
            'Tax %',
            'Price',
            'Offer Price',
            'FOC Months',
            'Pro-rata Months',
            'Capacity',
            'Specification',
            'Remarks',
        ];
    }
}
