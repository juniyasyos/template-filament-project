<?php

return [
    // Basic panel identity
    'id' => env('PANEL_ID', 'panel'),
    'path' => env('PANEL_PATH', 'panel'),
    'name' => env('PANEL_NAME', 'Panel'),
    'version' => env('PANEL_VERSION', null), // e.g. 1.0.0

    // Theme plugin used by the panel
    'theme' => [
        // Plugin class must implement Filament\Contracts\Plugin
        'plugin' => \App\Filament\Plugins\PanelTheme::class,
    ],
];

