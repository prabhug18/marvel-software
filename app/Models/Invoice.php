<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{    
    protected $fillable = [
        'customer_id',
        'user_id',
        'customer_name',
        'invoice_number',
        'invoice_date',
        'dc_number',
        'cgst',
        'sgst',
        'igst',
        'grand_total',
        'warehouse_id',
        'vehicle_type',
        'vehicle_details',
        'status',
        'approved_by',
        'approved_at',
        'reconciliation_done',
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class, 'customer_id');
    }
    
    public function items()
    {
        return $this->hasMany(\App\Models\InvoiceItems::class, 'invoice_id');
    }
    
    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class, 'invoice_id');
    }
    
    public function warehouse()
    {
        return $this->belongsTo(\App\Models\Warehouse::class, 'warehouse_id');
    }

    public function deliveryAddress()
    {
        return $this->hasOne(DeliveryAddress::class);
    }
}
