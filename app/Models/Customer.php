<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    //
    //
    use HasFactory, SoftDeletes;
    

     /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'mobile_no',
        'address',
        'state_id',
        'city_id',
        'pincode',
        'user_id',
        'SoftDeletes'        
    ];

    public function state()
    {
        return $this->belongsTo('App\Models\State','state_id','id');
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City','city_id','id');
    }

}
