<?php

namespace Database\Seeders;

use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use juniyasyos\ShieldLite\Models\ShieldRole;

class ShieldSuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = config('shield.superadmin.name', 'Super Admin');
        $guard = config('shield.superadmin.guard', 'web');

        $gates = [];
        foreach (Filament::getPanels() as $panel) {
            try {
                $panelGates = shield()->panelGates($panel);
                $gates = array_merge($gates, $panelGates);
            } catch (\Throwable $e) {
                // skip if panel not fully booted in seeding context
            }
        }

        $gates = array_values(array_unique($gates));

        // Store as a single flattened group for efficiency
        $access = [$gates];

        $role = ShieldRole::query()->updateOrCreate(
            [
                'name' => $name,
                'guard' => $guard,
            ],
            [
                'created_by_name' => 'system',
                'access' => $access,
            ]
        );

        // Assign the Super Admin role to the Admin user if present
        $admin = User::query()->where('email', 'admin@gmail.com')->first();
        if ($admin && $role) {
            $admin->roles()->syncWithoutDetaching([$role->id]);

            // Optionally mark role as default if column exists
            if (Schema::hasColumn('users', 'default_role_id')) {
                $admin->forceFill(['default_role_id' => $role->id])->save();
            }
        }
    }
}

