<?php

namespace Ultra\UltraConfigManager\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class EncryptedCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            // Se il valore è null, restituisci null direttamente
            return null;
        }

        try {
            // Tenta di decriptare il valore
            return Crypt::decryptString($value);
        } catch (DecryptException $e) {
            // Se c'è un problema di decrittazione, significa che il valore non è criptato, restituisci com'è
            return $value;
        }
    }

    public function set($model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            // Se il valore è null, restituisci null direttamente
            return null;
        }

        // Cripta il valore prima di salvarlo nel database
        return Crypt::encryptString($value);
    }
}
