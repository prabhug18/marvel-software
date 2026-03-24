<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    protected $table = 'terms';
    protected $fillable = ['content', 'active', 'created_by'];
    protected $casts = [
        'active' => 'boolean',
    ];
    public $timestamps = true;
}
