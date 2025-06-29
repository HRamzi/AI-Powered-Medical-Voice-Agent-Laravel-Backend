<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Session extends Model
{
    protected $table = 'consultation_sessions';
    
    protected $fillable = [
        'user_id',
        'session_id',
        'notes',
        'selected_doctor',
        'conversation',
        'report',
        'created_by',
        'created_on',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'selected_doctor' => 'array',
        'conversation' => 'array',
        'report' => 'array',
    ];

    /**
     * Boot method to auto-generate UUID for session_id
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($session) {
            if (!$session->session_id) {
                $session->session_id = (string) Str::uuid();
            }
            if (!$session->created_on) {
                $session->created_on = now()->toString();
            }
        });
    }

    /**
     * Relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
