<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use juniyasyos\ShieldLite\Models\ShieldRole;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure base roles exist
        $roles = [
            ['name' => 'Admin', 'guard' => 'web'],
            ['name' => 'Manager', 'guard' => 'web'],
            ['name' => 'Staff', 'guard' => 'web'],
        ];

        $roleIdsByName = [];
        foreach ($roles as $role) {
            $record = ShieldRole::query()->updateOrCreate(
                [
                    'name' => $role['name'],
                    'guard' => $role['guard'],
                ],
                [
                    'created_by_name' => 'system',
                ]
            );
            $roleIdsByName[$role['name']] = $record->id;
        }

        // Seed users and assign their roles
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'password' => 'password',
                'role' => 'Admin',
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

            $roleId = $roleIdsByName[$data['role']] ?? null;
            if ($roleId) {
                // Attach role and set as default
                $user->roles()->syncWithoutDetaching([$roleId]);
                $user->default_role_id = $roleId;
                $user->save();
            }
        }
    }
}
