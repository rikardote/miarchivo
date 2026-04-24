<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $super = User::firstOrCreate(
            ['email' => 'admin@archivo.local'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('password'),
            ]
        );
        $super->assignRole('superuser');

        $admin = User::firstOrCreate(
            ['email' => 'rh@archivo.local'],
            [
                'name' => 'Usuario RH',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        $user = User::firstOrCreate(
            ['email' => 'usuario@archivo.local'],
            [
                'name' => 'Usuario Consulta',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('user');
    }
}
