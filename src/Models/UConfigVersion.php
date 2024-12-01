<?php

namespace UltraProject\UConfig\Models;

use Illuminate\Database\Eloquent\Model;

class UConfigVersion extends Model
{
    protected $table = 'uconfig_versions';

    protected $fillable = [
        'uconfig_id',
        'version',
        'key',
        'category',
        'note',
        'value',
    ];

    public function user()
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function uconfig()
    {
        return $this->belongsTo(UConfig::class);
    }

    
} 