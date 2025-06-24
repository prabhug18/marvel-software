<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Warehouse extends Model
{
    use HasFactory, SoftDeletes;

     /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'sub_heading',
        'company_name',
        'prefix',
        'status_id',
        'SoftDeletes',
        'user_id',
        'address',
        'email',
        'mobile',
        'image',
        'account_name',
        'account_number',
        'ifsc_code',
        'branch',
        'gstn_uin',
        'bank_name',
    ];
}
