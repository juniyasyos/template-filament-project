<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Enums\ThemeMode;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class SiimutPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('siimut')
            ->path('siimut')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->collapsedSidebarWidth('3rem')
            // Use the PHP-registered colors via FilamentColor, and keep Vite theme for CSS.
            ->viteTheme('resources/css/filament/siimut/theme.css')
            ->colors(fn () => (array) config('siimut-theme.colors'))
            ->defaultThemeMode(match (strtolower((string) config('siimut-theme.default_mode'))){
                'light' => ThemeMode::Light,
                'dark' => ThemeMode::Dark,
                default => ThemeMode::System,
            })
            ->discoverResources(in: app_path('Filament/Siimut/Resources'), for: 'App\Filament\Siimut\Resources')
            ->discoverPages(in: app_path('Filament/Siimut/Pages'), for: 'App\Filament\Siimut\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Siimut/Widgets'), for: 'App\Filament\Siimut\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                \App\Http\Middleware\Authenticate::class,
            ]);
    }
}
