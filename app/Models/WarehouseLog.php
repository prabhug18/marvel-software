<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WarehouseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'action',
        'performed_by',
        'details',
    ];
}
