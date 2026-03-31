<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address',
        'gst_no',
        'status_id',
    ];
}
