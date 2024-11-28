<?php

namespace UltraProject\UConfig\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UConfig extends Model
{
    use SoftDeletes;

    protected $table = 'uconfig';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'category',
        'value',
        'note',
    ];
} 