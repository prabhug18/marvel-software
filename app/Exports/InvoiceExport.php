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
        ])->flatMap(function ($row) {
            $createdAt = $row->created_at;
            try {
                if ($createdAt instanceof \DateTimeInterface) {
                    $createdAt = $createdAt->format('d-m-Y');
                } else {
                    $createdAt = date('d-m-Y', strtotime($createdAt));
                }
            } catch (\Exception $e) {
                $createdAt = (string) $row->created_at;
            }
            // Fetch InvoiceItems for this invoice
            $items = \App\Models\InvoiceItems::where('invoice_id', $row->id)->get(['product_name', 'model', 'qty', 'unit_price', 'tax_percentage', 'tax_amount', 'total']);
            // If no items, return a single row with empty item columns
            if ($items->isEmpty()) {
                return [[
                    $row->id,
                    $row->invoice_number,
                    $row->customer_name,
                    $row->grand_total,
                    $createdAt,
                    '', '', '', '', '', '', ''
                ]];
            }
            // Otherwise, return one row per item
            return $items->map(function($item) use ($row, $createdAt) {
                return [
                    $row->id,
                    $row->invoice_number,
                    $row->customer_name,
                    $row->grand_total,
                    $createdAt,
                    $item->product_name,
                    $item->model,
                    $item->qty,
                    $item->unit_price,
                    $item->tax_percentage,
                    $item->tax_amount,
                    $item->total
                ];
            });
        });
    }

    public function headings(): array
    {
        return [
            'ID', 'Invoice Number', 'Customer Name', 'Grand Total', 'Created At',
            'Product Name', 'Model', 'Qty', 'Unit Price', 'Tax %', 'Tax Amount', 'Total'
        ];
    }
}
