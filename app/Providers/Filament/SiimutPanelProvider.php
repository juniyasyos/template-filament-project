<?php

namespace App\Providers\Filament;

use App\Filament\Plugins\SiimutTheme;
use Juniyasyos\FilamentMediaManager\FilamentMediaManagerPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use juniyasyos\ShieldLite\ShieldLite;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Juniyasyos\FilamentLaravelBackup\FilamentLaravelBackupPlugin;

class SiimutPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('siimut')
            ->path('siimut')
            ->default()
            ->authGuard('web')
            // All UI configuration is now centralized in SiimutTheme plugin
            ->plugins([
                SiimutTheme::make(),
                ShieldLite::make(),
                FilamentLaravelBackupPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Siimut/Resources'), for: 'App\Filament\Siimut\Resources')
            ->discoverPages(in: app_path('Filament/Siimut/Pages'), for: 'App\Filament\Siimut\Pages')
            ->navigationGroups([
                'User Managements',
                'Settings'
            ])
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
