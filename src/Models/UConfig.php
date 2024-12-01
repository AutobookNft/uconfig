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

    /**
     * Gli attributi che possono essere assegnati in massa.
     *
     * @var array
     */
    protected $fillable = [
        'key',
        'value',
        'category',
        'note',
    ];  

    public function versions()
    {
        return $this->hasMany(UConfigVersion::class);
    }
}
