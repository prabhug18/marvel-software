<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address',
        'gst_no',
        'status_id',
    ];
}
