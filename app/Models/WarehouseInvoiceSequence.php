<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseInvoiceSequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'current_number',
    ];
}
