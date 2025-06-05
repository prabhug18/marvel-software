<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'customer_name',
        'invoice_number',
        'invoice_date',
        'dc_number',
        'cgst',
        'sgst',
        'igst',
        'grand_total',
    ];
}
