<?php

namespace UltraProject\UConfig\Models;

use Illuminate\Database\Eloquent\Model;

class UConfigVersion extends Model
{
    protected $table = 'uconfig_versions';

    protected $fillable = [
        'key',
        'category',
        'value',
        'note',
        'user_id',
        'action',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }
} 