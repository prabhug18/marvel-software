<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_number',
        'enquiry_id',
        'customer_id',
        'name',
        'mobile_no',
        'email',
        'address',
        'state_id',
        'city_id',
        'source',
        'product_interest',
        'brand_interest',
        'expected_value',
        'priority',
        'status',
        'next_follow_up',
        'assigned_to',
        'warehouse_id',
        'user_id',
    ];

    public function enquiry()
    {
        return $this->belongsTo(Enquiry::class, 'enquiry_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

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

    public function followUps()
    {
        return $this->hasMany(LeadFollowUp::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($lead) {
            $latest = self::orderBy('id', 'desc')->first();
            if (!$latest) {
                $lead->lead_number = 'LEAD-0001';
            } else {
                $number = intval(substr($latest->lead_number, 5)) + 1;
                $lead->lead_number = 'LEAD-' . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}
