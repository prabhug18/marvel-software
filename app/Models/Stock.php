<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $fillable = [
        'warehouse_id', 'category_id', 'brand_id', 'model', 'model_no', 'qty', 'user_id'
    ];
    
    public function category() {
        return $this->belongsTo(\App\Models\Category::class);
    }
    public function brand() {
        return $this->belongsTo(\App\Models\Brand::class);
    }

    public function warehouse() {
        return $this->belongsTo(\App\Models\Warehouse::class);
    }
}

