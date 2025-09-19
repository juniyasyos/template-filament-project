<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure base roles exist using Spatie Permission
        $roles = [
            ['name' => 'Super-Admin', 'guard_name' => 'web'],
            ['name' => 'Admin', 'guard_name' => 'web'],
            ['name' => 'Manager', 'guard_name' => 'web'],
            ['name' => 'Staff', 'guard_name' => 'web'],
        ];

        $roleIdsByName = [];
        foreach ($roles as $role) {
            $record = Role::findOrCreate($role['name'], $role['guard_name']);
            $roleIdsByName[$role['name']] = $record->id;
        }

        // Seed users and assign their roles
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => 'password',
                'role' => 'Super-Admin', // Fixed: use consistent role name
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@gmail.com',
                'password' => 'password',
                'role' => 'Manager',
            ],
            [
                'name' => 'Staff User',
                'email' => 'staff@gmail.com',
                'password' => 'password',
                'role' => 'Staff',
            ],
        ];

        foreach ($users as $data) {
            $user = User::query()->updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make($data['password']),
                    'email_verified_at' => now(),
                ]
            );

            // Use Shield Lite's HasShield trait method
            if (isset($data['role']) && isset($roleIdsByName[$data['role']])) {
                $user->assignRole($data['role']); // Use Spatie's assignRole method
            }
        }
    }
}
