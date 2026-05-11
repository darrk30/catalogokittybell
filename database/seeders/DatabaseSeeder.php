<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'kevin@gmail.com'], // La condición para buscar
            [
                'name' => 'Kevin Rivera',
                'password' => Hash::make('123123123'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'belen@gmail.com'], // La condición para buscar
            [
                'name' => 'Belen Cano',
                'password' => Hash::make('123123123'),
                'email_verified_at' => now(),
            ]
        );
    }
}
