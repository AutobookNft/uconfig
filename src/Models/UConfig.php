<?php

namespace UltraProject\UConfig\Models;

use App\Casts\EncryptedCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UConfig extends Model
{
    use SoftDeletes;

    protected $table = 'uconfig';

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

    protected $casts = [
        'value' => EncryptedCast::class,
    ];
}
