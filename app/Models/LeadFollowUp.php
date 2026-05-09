<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'follow_up_date',
        'notes',
        'outcome',
        'next_follow_up',
        'user_id',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
