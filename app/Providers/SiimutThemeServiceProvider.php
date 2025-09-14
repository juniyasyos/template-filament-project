<?php

namespace App\Providers;

use Filament\Support\Assets\Theme;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;

class SiimutThemeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register brand/status color palettes from config (PHP-driven theming).
        FilamentColor::register(static fn () => config('siimut-theme.colors'));

        // Optionally expose custom CSS variables to Filament styles.
        $variables = (array) config('siimut-theme.css_variables', []);
        if (! empty($variables)) {
            FilamentAsset::registerCssVariables($variables, package: 'app');
        }

        // If you later decide to serve a precompiled public theme instead of Vite,
        // uncomment the block below and register the asset. Then use ->theme('siimut') on the panel.
        // FilamentAsset::register([
        //     Theme::make(config('siimut-theme.id', 'siimut'), public_path('css/filament/siimut/theme.css'))
        //         ->relativePublicPath('css/filament/siimut/theme.css'),
        // ], 'app');
    }
}

