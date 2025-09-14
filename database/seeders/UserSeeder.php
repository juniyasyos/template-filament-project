<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@gmail.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@gmail.com',
                'password' => 'password',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $data) {
            User::factory()->create($data);
        }
    }
}

