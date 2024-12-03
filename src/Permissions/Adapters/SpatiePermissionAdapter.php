<?php

namespace UltraProject\UConfig\Permissions\Adapters;

use UltraProject\UConfig\Permissions\Contracts\PermissionAdapterContract;


/**
 * SpatiePermissionAdapter
 * 
 * Questo adattatore implementa l'interfaccia PermissionAdapterContract 
 * per integrare UCM con il pacchetto Spatie Permission.
 * 
 * Funzionalità principali:
 * - Verifica i permessi utilizzando il metodo `can` fornito da Spatie.
 * - Controlla i ruoli di un utente con il metodo `hasRole` di Spatie.
 * 
 * Dipendenze:
 * - La libreria Spatie Permission deve essere installata e configurata correttamente.
 * 
 * Vantaggi:
 * - Supporta un'ampia gamma di funzionalità di gestione dei permessi.
 * - Adatto a progetti complessi con necessità di gestione granulare di ruoli e permessi.
 */

class SpatiePermissionAdapter implements PermissionAdapterContract
{
    public function can($user, $permission): bool
    {
        return $user->can($permission);
    }

    public function hasRole($user, $role): bool
    {
        return $user->hasRole($role);
    }
}
