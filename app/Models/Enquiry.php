<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Enquiry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'enquiry_number',
        'name',
        'mobile_no',
        'email',
        'address',
        'state_id',
        'city_id',
        'source',
        'product_interest',
        'brand_interest',
        'remarks',
        'status',
        'assigned_to',
        'warehouse_id',
        'user_id',
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($enquiry) {
            $latest = self::orderBy('id', 'desc')->first();
            if (!$latest) {
                $enquiry->enquiry_number = 'ENQ-0001';
            } else {
                $number = intval(substr($latest->enquiry_number, 4)) + 1;
                $enquiry->enquiry_number = 'ENQ-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
