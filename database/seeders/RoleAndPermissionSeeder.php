<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Crear o asegurar roles
        $admin = Role::firstOrCreate(['name' => 'administrador']);
        $bibliotecario = Role::firstOrCreate(['name' => 'bibliotecario']);

        // Crear o asegurar permisos
        $permisoCambiarContrasena = Permission::firstOrCreate(['name' => 'cambiar contraseñas']);

        // Asignar permiso al administrador
        $admin->givePermissionTo($permisoCambiarContrasena);

        // Bibliotecario NO recibe este permiso
    }
}

