<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'customer_name',
        'invoice_number',
        'invoice_date',
        'dc_number',
        'cgst',
        'sgst',
        'igst',
        'grand_total',
        'warehouse_id',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
    
    public function items()
    {
        return $this->hasMany(\App\Models\InvoiceItems::class, 'invoice_id');
    }
}
