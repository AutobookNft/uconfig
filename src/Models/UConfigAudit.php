<?php

namespace UltraProject\UConfig\Models;

use Illuminate\Database\Eloquent\Model;

class UConfigAudit extends Model
{
    protected $table = 'uconfig_audit';

    protected $fillable = [
        'uconfig_id',
        'action',
        'new_value',
        'old_value',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
} 