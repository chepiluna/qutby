<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Finance',
                'email' => 'finance@qutby.com',
                'password' => 'password',
                'role' => 'finance',
            ],
            [
                'name' => 'Operasional',
                'email' => 'operasional@qutby.com',
                'password' => 'password',
                'role' => 'operasional',
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user,
            );
        }

        User::whereIn('email', [
            'finance@example.com',
            'operasional@example.com',
        ])->delete();
    }
}
