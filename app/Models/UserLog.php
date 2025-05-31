<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserLog extends Model
{
    //
    protected $fillable = [
        'user_id',
        'action',
        'details',
        'performed_by'        
    ];


    public function status()
    {
        return $this->belongsTo('App\Models\Status','status_id','id');
    }
}
