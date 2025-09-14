<?php

namespace Database\Seeders;

use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use juniyasyos\ShieldLite\Models\ShieldRole;

class ShieldAdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = 'Admin';
        $guard = 'web';

        $gates = [];
        foreach (Filament::getPanels() as $panel) {
            try {
                $panelGates = shield()->panelGates($panel);
                // Limit admin to User resource permissions only
                $userGates = array_values(array_filter($panelGates, fn ($gate) => Str::startsWith($gate, 'user.')));
                $gates = array_merge($gates, $userGates);
            } catch (\Throwable $e) {
                // Skip if the panel is not fully booted during seeding
            }
        }

        $gates = array_values(array_unique($gates));

        // Store as a single flattened group, as expected by Shield Lite
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

