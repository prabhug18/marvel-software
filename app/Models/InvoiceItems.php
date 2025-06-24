<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends Model
{
    //

    protected $fillable = [
        'invoice_id',
        'user_id',
        'product_name',
        'model',
        'qty',
        'tax_percentage',
        'tax_amount',
        'unit_price',
        'total',
        'serial_no',
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'model', 'model');
    }
}
