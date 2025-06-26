<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    protected $table = 'consultation_sessions';
    
    protected $fillable = [
        'user_id',
        'symptoms',
        'notes',
        'doctor_type',
        'voice_profile_id',
    ];
}
