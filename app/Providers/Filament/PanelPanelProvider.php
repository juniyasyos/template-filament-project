<?php

namespace App\Providers\Filament;

use App\Filament\Plugins\PanelTheme;
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
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Juniyasyos\FilamentLaravelBackup\FilamentLaravelBackupPlugin;

class PanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('panel')
            ->path('panel')
            ->default()
            ->authGuard('web')
            // All UI configuration is now centralized in PanelTheme plugin
            ->plugins([
                PanelTheme::make(),
                FilamentLaravelBackupPlugin::make(),
            ])
            ->discoverResources(in: app_path('Filament/Panel/Resources'), for: 'App\Filament\Panel\Resources')
            ->discoverPages(in: app_path('Filament/Panel/Pages'), for: 'App\Filament\Panel\Pages')
            ->navigationGroups([
                'User Managements',
                'Settings'
            ])
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Panel/Widgets'), for: 'App\Filament\Panel\Widgets')
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
