<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'action',
        'performed_by',
        'details',
    ];
}
