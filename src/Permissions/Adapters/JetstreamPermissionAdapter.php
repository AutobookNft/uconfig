<?php

namespace UltraProject\UConfig\Permissions\Adapters;

use UltraProject\UConfig\Permissions\Contracts\PermissionAdapterContract;


/**
 * JetstreamPermissionAdapter
 * 
 * Questo adattatore implementa l'interfaccia PermissionAdapterContract 
 * per integrare UCM con il sistema di gestione dei permessi di Jetstream.
 * 
 * Funzionalità principali:
 * - Verifica se un utente può eseguire un'azione basata sui permessi definiti a livello di team.
 * - Controlla se un utente appartiene a un ruolo specifico all'interno del team corrente.
 * 
 * Dipendenze:
 * - Jetstream deve essere configurato correttamente con ruoli e permessi.
 * 
 * Limiti:
 * - Richiede che l'utente sia associato a un team per funzionare.
 */

class JetstreamPermissionAdapter implements PermissionAdapterContract
{
    public function can($user, $permission): bool
    {
        return $user->currentTeam && $user->currentTeam->can($user, $permission);
    }

    public function hasRole($user, $role): bool
    {
        return $user->currentTeam && $user->currentTeam->userHasRole($user, $role);
    }
}
