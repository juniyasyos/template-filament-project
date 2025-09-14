<?php

namespace Database\Seeders;

use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
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

        ShieldRole::query()->updateOrCreate(
            [
                'name' => $name,
                'guard' => $guard,
            ],
            [
                'created_by_name' => 'system',
                'access' => $access,
            ]
        );
    }
}

