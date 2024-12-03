<?php

namespace UltraProject\UConfig\Permissions\Contracts;

interface PermissionAdapterContract
{
    /**
     * Controlla se un utente ha un permesso specifico.
     */
    public function can($user, $permission): bool;

    /**
     * Controlla se un utente ha un ruolo specifico.
     */
    public function hasRole($user, $role): bool;
}
