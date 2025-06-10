<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'customer_id',
        'user_id',
        'customer_name',
        'invoice_number',
        'invoice_id',
        'grand_total',
        'balance_amount',
        'paid_amount',
        'payment_mode',
        'payment_date',
        'description',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
}
