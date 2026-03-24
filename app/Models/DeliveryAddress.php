<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryAddress extends Model
{
    protected $fillable = [
        'invoice_id',
        'address',
        'state_id',
        'city_id',
        'pincode',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
