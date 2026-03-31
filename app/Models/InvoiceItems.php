<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends Model
{
    //

    protected $fillable = [
        'invoice_id',
        'product_id',
        'user_id',
        'product_name',
        'model',
        'model_no',
        'qty',
        'tax_percentage',
        'tax_amount',
        'unit_price',
        'total',
        'serial_no',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id');
    }
}
