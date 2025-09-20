<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed basic users without roles
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => 'password',
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@gmail.com',
                'password' => 'password',
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@gmail.com',
                'password' => 'password',
            ],
        ];

        foreach ($users as $data) {
            User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
