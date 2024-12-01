<?php

namespace UltraProject\UConfig\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class EncryptedCast implements CastsAttributes
{
    // Metodo per criptare il valore prima di salvarlo nel database
    public function set($model, string $key, $value, array $attributes)
    {
        return Crypt::encryptString($value);
    }

    // Metodo per decriptare il valore quando viene caricato dal database
    public function get($model, string $key, $value, array $attributes)
    {
        return Crypt::decryptString($value);
    }
}
