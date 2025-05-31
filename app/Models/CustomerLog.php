<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLog extends Model
{
    //
    protected $fillable = [
        'customer_id',
        'action',        
        'description',
        'performed_by'          
    ];
}
