<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItems extends Model
{
    //

    protected $fillable = [
        'invoice_id',
        'product_name',
        'model',
        'qty',
        'tax_percentage',
        'tax_amount',
        'unit_price',
        'total',
    ];


    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
