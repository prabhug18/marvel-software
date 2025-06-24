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
        'specification',
        'price',
        'offer_price',
        'tax_percentage',
        'hsn_code',
        'product_images',
        'product_images_original',
        'user_id',
    ];
    
    public function category() {
        return $this->belongsTo(\App\Models\Category::class);
    }
    public function brand() {
        return $this->belongsTo(\App\Models\Brand::class);
    }
    public function invoices()
    {
        return $this->hasMany(\App\Models\InvoiceItems::class, 'model', 'model');
    }
}


