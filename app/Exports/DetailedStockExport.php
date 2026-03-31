<?php
  
namespace App\Exports;

use App\Models\Stock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DetailedStockExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        // Get all stock entries with relationships, only where qty > 0
        return Stock::with(['warehouse', 'category', 'brand', 'vendor'])
            ->where('qty', '>', 0)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Category',
            'Brand',
            'Model',
            'Model No',
            'Warehouse/Location',
            'Qty',
            'Serial Number',
            'Vendor',
            'Purchase Date',
            'Purchased From (Text)',
            'Purchase Rate (₹)',
            'Remarks',
            'Created At'
        ];
    }

    public function map($stock): array
    {
        return [
            $stock->id,
            $stock->category ? $stock->category->name : 'N/A',
            $stock->brand ? $stock->brand->name : 'N/A',
            $stock->model,
            $stock->model_no,
            $stock->warehouse ? $stock->warehouse->name : 'N/A',
            $stock->qty,
            $stock->serial_no,
            $stock->vendor ? $stock->vendor->name : 'N/A',
            $stock->purchase_date,
            $stock->purchased_from,
            $stock->purchase_rate,
            $stock->remarks,
            $stock->created_at ? $stock->created_at->format('Y-m-d H:i') : ''
        ];
    }
}
