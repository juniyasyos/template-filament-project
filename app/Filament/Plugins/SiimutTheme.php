<?php

namespace App\Filament\Plugins;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Enums\ThemeMode;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;

class SiimutTheme implements Plugin
{
    protected string $id = 'siimut-theme';
    protected string $viteThemePath = 'resources/css/filament/siimut/theme.css';
    protected array $colors = [];
    protected ThemeMode $defaultMode;

    // Branding properties
    protected string | Htmlable | Closure | null $brandName = null;
    protected $brandLogo = null;
    protected $darkBrandLogo = null;
    protected $brandLogoHeight = null;
    protected $favicon = null;

    // Sidebar properties
    protected bool $sidebarCollapsibleOnDesktop = true;
    protected string $sidebarWidth = '18rem';
    protected string $collapsedSidebarWidth = '7rem';

    // Authentication properties
    protected string | Closure | null $loginUrl = null;
    protected string | Closure | null $passwordResetUrl = null;
    protected string | Closure | null $passwordResetResponseUrl = null;
    protected string | Closure | null $registrationUrl = null;

    // Notifications properties
    protected bool $databaseNotifications = true;
    protected string $databaseNotificationsPolling = '30s';

    // Global search properties
    protected bool $globalSearch = true;
    protected array $globalSearchKeyBindings = ['cmd+k', 'ctrl+k'];

    public function __construct()
    {
        $this->colors = (array) config('siimut-theme.colors', []);

        $this->defaultMode = match (strtolower((string) config('siimut-theme.default_mode', 'system'))) {
            'light' => ThemeMode::Light,
            'dark' => ThemeMode::Dark,
            default => ThemeMode::System,
        };

        // Load branding configuration
        $brand = (array) config('siimut-theme.brand', []);
        $this->brandName = $brand['name'] ?? null;
        $this->brandLogo = $this->resolveAssetUrl($brand['logo'] ?? null);
        $this->darkBrandLogo = $this->resolveAssetUrl($brand['logo_dark'] ?? null);
        $this->brandLogoHeight = $brand['logo_height'] ?? null;
        $this->favicon = $this->resolveAssetUrl($brand['favicon'] ?? null);

        // Load UI configuration
        $ui = (array) config('siimut-theme.ui', []);
        $this->sidebarCollapsibleOnDesktop = $ui['sidebar_collapsible'] ?? true;
        $this->sidebarWidth = $ui['sidebar_width'] ?? '18rem';
        $this->collapsedSidebarWidth = $ui['collapsed_sidebar_width'] ?? '7rem';

        // Load authentication configuration
        $auth = (array) config('siimut-theme.authentication', []);
        $this->loginUrl = $auth['login_url'] ?? '/login';
        $this->passwordResetUrl = $auth['password_reset_url'] ?? '/forgot-password';
        $this->passwordResetResponseUrl = $auth['password_reset_response_url'] ?? '/reset-password';
        $this->registrationUrl = $auth['registration_url'] ?? '/register';

        // Load notifications configuration
        $notifications = (array) config('siimut-theme.notifications', []);
        $this->databaseNotifications = $notifications['database_enabled'] ?? true;
        $this->databaseNotificationsPolling = $notifications['polling_interval'] ?? '30s';

        // Load global search configuration
        $search = (array) config('siimut-theme.global_search', []);
        $this->globalSearch = $search['enabled'] ?? true;
        $this->globalSearchKeyBindings = $search['key_bindings'] ?? ['cmd+k', 'ctrl+k'];
    }

    public static function make(): static
    {
        return new static();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->viteTheme($this->viteThemePath)
            ->colors($this->colors)
            ->defaultThemeMode($this->defaultMode);

        // Apply branding
        if ($this->brandName !== null) {
            $panel->brandName($this->brandName);
        }
        if ($this->brandLogo !== null) {
            $panel->brandLogo($this->brandLogo);
        }
        if ($this->darkBrandLogo !== null) {
            $panel->darkModeBrandLogo($this->darkBrandLogo);
        }
        if ($this->brandLogoHeight !== null) {
            $panel->brandLogoHeight($this->brandLogoHeight);
        }
        if ($this->favicon !== null) {
            $panel->favicon($this->favicon);
        }

        // Apply sidebar configuration
        if ($this->sidebarCollapsibleOnDesktop) {
            $panel->sidebarCollapsibleOnDesktop();
        }
        $panel->sidebarWidth($this->sidebarWidth);
        $panel->collapsedSidebarWidth($this->collapsedSidebarWidth);

        // Apply authentication configuration
        if ($this->loginUrl) {
            $panel->login(fn () => redirect()->to(url($this->loginUrl)));
        }
        if ($this->passwordResetUrl && $this->passwordResetResponseUrl) {
            $panel->passwordReset(
                fn () => redirect()->to(url($this->passwordResetUrl)),
                fn () => redirect()->to(url($this->passwordResetResponseUrl)),
            );
        }
        if ($this->registrationUrl) {
            $panel->registration(fn () => redirect()->to(url($this->registrationUrl)));
        }

        // Apply notifications configuration
        if ($this->databaseNotifications) {
            $panel->databaseNotifications();
            $panel->databaseNotificationsPolling($this->databaseNotificationsPolling);
        }

        // Apply global search configuration
        if ($this->globalSearch) {
            $panel->globalSearch();
            $panel->globalSearchKeyBindings($this->globalSearchKeyBindings);
        }
    }

    public function boot(Panel $panel): void
    {
        // No additional setup required
    }

    protected function resolveAssetUrl(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $lower = strtolower($value);
        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://') || str_starts_with($value, '/')) {
            return $value;
        }

        return asset($value);
    }
}
