<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BrandLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'action',
        'performed_by',
        'details',
    ];
}
