<?php

namespace App\Providers\Filament;

use App\Filament\Plugins\SiimutTheme;
// use App\Filament\Siimut\Pages\Login as SiimutLogin; // no longer used when delegating auth to Vue
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

class SiimutPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('siimut')
            ->path('siimut')
            ->authGuard('web')
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('18rem')
            ->collapsedSidebarWidth('7rem')
            // Delegate authentication to Vue / Fortify routes
            ->login(fn () => redirect()->to(url('/login')))
            ->passwordReset(
                fn () => redirect()->to(url('/forgot-password')),
                fn () => redirect()->to(url('/reset-password')),
            )
            ->registration(fn () => redirect()->to(url('/register')))
            // Enable database notifications in the topbar
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            // Enable global search UI + keyboard shortcut
            ->globalSearch()
            ->globalSearchKeyBindings(['cmd+k', 'ctrl+k'])
            // Use a plugin-based theme API similar to `resmatech/filament-awin-theme`.
            ->plugins([
                SiimutTheme::make(),
                FilamentMediaManagerPlugin::make()
                    ->allowUserAccess(true)
                    ->allowSubFolders(true),
                ShieldLite::make(),
            ])
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
