<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = 'admin@enec.gob.gt';

        $admin = User::where('email', $adminEmail)->first();

        if (!$admin) {
            $admin = User::create([
                'name' => 'Administrador',
                'email' => $adminEmail,
                'password' => bcrypt('Adminigb1'), // Cambia por una segura
            ]);

            $admin->assignRole('administrador');
        }
    }
}
