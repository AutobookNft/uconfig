<?php

namespace UltraProject\UConfig\Models;

use Illuminate\Database\Eloquent\Model;

class UConfigAudit extends Model
{
    protected $table = 'uconfig_audit';

    protected $fillable = [
        'user_id',
        'action',
        'key',
        'details',
        'ip_address',
        'user_agent',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
} 