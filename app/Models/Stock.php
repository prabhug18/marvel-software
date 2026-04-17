<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    //
    protected $fillable = [
        'warehouse_id', 'category_id', 'brand_id', 'model', 'model_no', 'qty', 'user_id',
        'vendor_id', 'purchase_date', 'purchased_from', 'purchase_rate', 'remarks', 'serial_no', 'invoice_no'
    ];
    
    public function vendor() {
        return $this->belongsTo(\App\Models\Vendor::class);
    }
    
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

