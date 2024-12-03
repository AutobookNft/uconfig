<?php

namespace UltraProject\UConfig\Permissions;

use UltraProject\UConfig\Permissions\Contracts\PermissionAdapterContract;
use UltraProject\UConfig\Permissions\Adapters\JetstreamPermissionAdapter;
use UltraProject\UConfig\Permissions\Adapters\SpatiePermissionAdapter;
use UltraProject\UConfig\Permissions\Adapters\GatePolicyPermissionAdapter;

class PermissionManager
{
    protected ?PermissionAdapterContract $adapter = null;

    public function __construct()
    {
        $driver = config('uconfig.roles_permissions.driver', 'jetstream');

        $this->adapter = match ($driver) {
            'jetstream' => new JetstreamPermissionAdapter(),
            'spatie' => new SpatiePermissionAdapter(),
            'gates' => new GatePolicyPermissionAdapter(),
            'none' => null, // Nessun sistema di permessi
            default => throw new \InvalidArgumentException("Driver non supportato: $driver"),
        };
    }

    public function can($user, $permission): bool
    {
        if ($this->adapter) {
            return $this->adapter->can($user, $permission);
        }
        return true; // Se nessun sistema è configurato, consenti sempre l'azione
    }

    public function hasRole($user, $role): bool
    {
        if ($this->adapter) {
            return $this->adapter->hasRole($user, $role);
        }
        return true; // Se nessun sistema è configurato, considera tutti i ruoli come "presenti"
    }
}
