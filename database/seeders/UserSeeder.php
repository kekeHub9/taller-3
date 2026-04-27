<?php
// AGREGAR GENTE ACA FUE HORRIBLE, hay que hacerlo manualmente por powershield

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $users = [
            [
                'name' => 'Administrador Sistema',
                'email' => 'admin@neurovida.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'departamento' => 'Sistemas'
            ],
            [
                'name' => 'Douglas Perez - Tecnico',
                'email' => 'tecnico@neurovida.com',
                'password' => Hash::make('password123'),
                'role' => 'tecnico',
                'departamento' => 'Mantenimiento'
            ],
            [
                'name' => 'Dra. Maira Lopez',
                'email' => 'medico@neurovida.com',
                'password' => Hash::make('password123'),
                'role' => 'medico',
                'departamento' => 'UCI'
            ],
            [
                'name' => 'Efrain Rodriguez - Auditor',
                'email' => 'auditor@neurovida.com',
                'password' => Hash::make('password123'),
                'role' => 'auditor',
                'departamento' => 'Calidad'
            ]
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('Usuarios de prueba creados con roles');
        $this->command->info('Credenciales: email: admin@neurovida.com / password: password123');
    }
}