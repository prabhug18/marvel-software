<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    //
    protected $fillable = [
        'category_id',
        'brand_id',
        'model',
        'series',
        'processor',
        'memory',
        'operating_system',
        'price',
        'user_id',
        'product_images',
        'product_images_original',
        'tax_percentage',
        'hsn_code',
        'SoftDeletes'        
    ];
    
    public function category() {
        return $this->belongsTo(\App\Models\Category::class);
    }
    public function brand() {
        return $this->belongsTo(\App\Models\Brand::class);
    }
}


