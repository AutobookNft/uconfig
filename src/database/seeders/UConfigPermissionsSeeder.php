<?php

namespace UltraProject\UConfig\database\seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UConfigPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Crea i permessi per UConfig
        $permissions = [
            'read-config',
            'create-config',
            'update-config',
            'delete-config',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assegna i permessi al ruolo 'admin'
        $role = Role::findByName('admin');
        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }
}
