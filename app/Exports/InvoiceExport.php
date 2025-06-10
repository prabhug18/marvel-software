<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InvoiceExport implements FromCollection, WithHeadings
{
    private $from;
    private $to;
    private $warehouseId;
    public $fileName = 'invoices.xlsx';

    public function __construct($from = null, $to = null, $warehouseId = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->warehouseId = $warehouseId;
    }

    public function collection()
    {
        $query = Invoice::query();
        if ($this->warehouseId) {
            $query->where('warehouse_id', $this->warehouseId);
        }
        if ($this->from) {
            $query->whereDate('created_at', '>=', $this->from);
        }
        if ($this->to) {
            $query->whereDate('created_at', '<=', $this->to);
        }
        return $query->orderBy('created_at', 'desc')->get([
            'id', 'invoice_number', 'customer_name', 'grand_total', 'created_at'
        ]);
    }

    public function headings(): array
    {
        return [
            'ID', 'Invoice Number', 'Customer Name', 'Grand Total', 'Created At'
        ];
    }
}
