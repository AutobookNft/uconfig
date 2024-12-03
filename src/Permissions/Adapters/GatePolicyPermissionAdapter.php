<?php

namespace UltraProject\UConfig\Permissions\Adapters;

use UltraProject\UConfig\Permissions\Contracts\PermissionAdapterContract;
use Illuminate\Support\Facades\Gate;


/**
 * GatePolicyPermissionAdapter
 * 
 * Questo adattatore implementa l'interfaccia PermissionAdapterContract 
 * per utilizzare i Gate e le Policy di Laravel come sistema di gestione dei permessi.
 * 
 * Funzionalità principali:
 * - Verifica i permessi dinamici tramite Gate (`Gate::allows`).
 * 
 * Limiti:
 * - Non supporta direttamente la gestione dei ruoli, in quanto i Gate non hanno 
 *   un concetto nativo di ruoli.
 * 
 * Vantaggi:
 * - Leggero e adatto a progetti che non necessitano di una gestione avanzata di ruoli.
 * - Ideale per applicazioni con controlli personalizzati basati su logica business-specifica.
 */

class GatePolicyPermissionAdapter implements PermissionAdapterContract
{
    public function can($user, $permission): bool
    {
        return Gate::allows($permission, $user);
    }

    public function hasRole($user, $role): bool
    {
        // Nei Gate non c'è un concetto esplicito di ruolo
        return false;
    }
}
