<?php

declare(strict_types=1);

namespace App\Filament\Plugins;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Enums\ThemeMode;
use Filament\Panel;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;

final class PanelTheme implements Plugin
{
    private const DEFAULT_ID = 'panel-theme';

    private string $id = self::DEFAULT_ID;

    /** @var non-empty-string */
    private string $viteThemePath = 'resources/css/filament/panel/theme.css';

    /**
     * @var array{
     *   primary?: string|array<int,string>|Color,
     *   danger?: string|array<int,string>|Color,
     *   gray?: string|array<int,string>|Color,
     *   info?: string|array<int,string>|Color,
     *   success?: string|array<int,string>|Color,
     *   warning?: string|array<int,string>|Color
     * }
     */
    private array $colors = [];

    private ThemeMode $defaultMode;

    // Branding
    private string|Htmlable|Closure|null $brandName = null;
    private string|null $brandLogo = null;
    private string|null $darkBrandLogo = null;
    /** @var non-empty-string|null */
    private ?string $brandLogoHeight = null;
    private string|null $favicon = null;

    // Sidebar
    private bool $sidebarCollapsibleOnDesktop = true;
    /** @var non-empty-string */
    private string $sidebarWidth = '18rem';
    /** @var non-empty-string */
    private string $collapsedSidebarWidth = '7rem';

    // Content
    private ?string $maxContentWidth = null;

    // Auth URLs
    private string|Closure|null $loginUrl = null;
    private string|Closure|null $passwordResetUrl = null;
    private string|Closure|null $passwordResetResponseUrl = null;
    private string|Closure|null $registrationUrl = null;

    // Notifications
    private bool $databaseNotifications = true;
    /** @var non-empty-string */
    private string $databaseNotificationsPolling = '30s';

    // Global Search
    private bool $globalSearch = true;
    /** @var list<non-empty-string> */
    private array $globalSearchKeyBindings = ['cmd+k', 'ctrl+k'];

    public function __construct()
    {
        // Colors & theme path
        $this->colors = (array) config('panel-theme.colors', []);
        $this->viteThemePath = (string) config('panel-theme.vite_path', $this->viteThemePath);

        // Mode
        $this->defaultMode = match (strtolower((string) config('panel-theme.default_mode', 'system'))) {
            'light' => ThemeMode::Light,
            'dark'  => ThemeMode::Dark,
            default => ThemeMode::System,
        };

        // Branding
        $brand                = (array) config('panel-theme.brand', []);
        $this->brandName      = Arr::get($brand, 'name');
        $this->brandLogo      = $this->resolveAssetUrl(Arr::get($brand, 'logo'));
        $this->darkBrandLogo  = $this->resolveAssetUrl(Arr::get($brand, 'logo_dark'));
        $this->brandLogoHeight = Arr::get($brand, 'logo_height');
        $this->favicon        = $this->resolveAssetUrl(Arr::get($brand, 'favicon'));

        // UI
        $ui                           = (array) config('panel-theme.ui', []);
        $this->sidebarCollapsibleOnDesktop = (bool) Arr::get($ui, 'sidebar_collapsible', true);
        $this->sidebarWidth           = (string) Arr::get($ui, 'sidebar_width', '18rem');
        $this->collapsedSidebarWidth  = (string) Arr::get($ui, 'collapsed_sidebar_width', '7rem');
        $this->maxContentWidth        = Arr::get($ui, 'max_content_width');

        // Auth
        $auth                           = (array) config('panel-theme.authentication', []);
        $this->loginUrl                 = Arr::get($auth, 'login_url', '/login');
        $this->passwordResetUrl         = Arr::get($auth, 'password_reset_url', '/forgot-password');
        $this->passwordResetResponseUrl = Arr::get($auth, 'password_reset_response_url', '/reset-password');
        $this->registrationUrl          = Arr::get($auth, 'registration_url', '/register');

        // Notifications
        $notifications                       = (array) config('panel-theme.notifications', []);
        $this->databaseNotifications         = (bool) Arr::get($notifications, 'database_enabled', true);
        $this->databaseNotificationsPolling  = (string) Arr::get($notifications, 'polling_interval', '30s');

        // Global Search
        $search                        = (array) config('panel-theme.global_search', []);
        $this->globalSearch            = (bool) Arr::get($search, 'enabled', true);
        $this->globalSearchKeyBindings = array_values((array) Arr::get($search, 'key_bindings', ['cmd+k', 'ctrl+k']));
    }

    public static function make(): self
    {
        return new self();
    }

    public function getId(): string
    {
        return $this->id;
    }

    /** Fluent overrides (opsional, untuk dipakai di ServiceProvider jika perlu) */
    public function id(string $id): self
    {
        $this->id = $id;
        return $this;
    }
    public function viteThemePath(string $path): self
    {
        $this->viteThemePath = $path;
        return $this;
    }
    public function colors(array $colors): self
    {
        $this->colors = $colors;
        return $this;
    }
    public function defaultMode(ThemeMode $mode): self
    {
        $this->defaultMode = $mode;
        return $this;
    }
    public function brandName(string|Htmlable|Closure|null $name): self
    {
        $this->brandName = $name;
        return $this;
    }
    public function brandLogo(?string $url): self
    {
        $this->brandLogo = $this->resolveAssetUrl($url);
        return $this;
    }
    public function darkBrandLogo(?string $url): self
    {
        $this->darkBrandLogo = $this->resolveAssetUrl($url);
        return $this;
    }
    public function brandLogoHeight(?string $height): self
    {
        $this->brandLogoHeight = $height;
        return $this;
    }
    public function favicon(?string $url): self
    {
        $this->favicon = $this->resolveAssetUrl($url);
        return $this;
    }
    public function sidebarCollapsibleOnDesktop(bool $on = true): self
    {
        $this->sidebarCollapsibleOnDesktop = $on;
        return $this;
    }
    public function sidebarWidth(string $width): self
    {
        $this->sidebarWidth = $width;
        return $this;
    }
    public function collapsedSidebarWidth(string $width): self
    {
        $this->collapsedSidebarWidth = $width;
        return $this;
    }
    public function maxContentWidth(?string $width): self
    {
        $this->maxContentWidth = $width;
        return $this;
    }
    public function loginUrl(string|Closure|null $url): self
    {
        $this->loginUrl = $url;
        return $this;
    }
    public function passwordResetUrls(string|Closure|null $requestUrl, string|Closure|null $responseUrl): self
    {
        $this->passwordResetUrl = $requestUrl;
        $this->passwordResetResponseUrl = $responseUrl;
        return $this;
    }
    public function registrationUrl(string|Closure|null $url): self
    {
        $this->registrationUrl = $url;
        return $this;
    }
    public function databaseNotifications(bool $on = true, string $poll = '30s'): self
    {
        $this->databaseNotifications = $on;
        $this->databaseNotificationsPolling = $poll;
        return $this;
    }
    public function globalSearch(bool $on = true, array $bindings = ['cmd+k', 'ctrl+k']): self
    {
        $this->globalSearch = $on;
        $this->globalSearchKeyBindings = array_values($bindings);
        return $this;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->viteTheme($this->viteThemePath)
            ->colors($this->colors)
            ->defaultThemeMode($this->defaultMode);

        // Branding
        if ($this->brandName !== null) {
            $panel->brandName($this->brandName);
        }
        if ($this->brandLogo !== null) {
            $panel->brandLogo($this->brandLogo);
        }
        if ($this->darkBrandLogo !== null) {
            $panel->darkModeBrandLogo($this->darkBrandLogo);
        }
        if (!empty($this->brandLogoHeight)) {
            $panel->brandLogoHeight($this->brandLogoHeight);
        }
        if ($this->favicon !== null) {
            $panel->favicon($this->favicon);
        }

        // Sidebar
        if ($this->sidebarCollapsibleOnDesktop) {
            $panel->sidebarCollapsibleOnDesktop();
        }
        $panel->sidebarWidth($this->sidebarWidth);
        $panel->collapsedSidebarWidth($this->collapsedSidebarWidth);

        // Content
        if ($this->maxContentWidth !== null) {
            $panel->maxContentWidth($this->maxContentWidth);
        }

        // Auth redirects ( Closure|string -> RedirectResponse )
        if ($this->loginUrl) {
            $panel->login(fn(): RedirectResponse => redirect($this->resolveUrl($this->loginUrl)));
        }
        if ($this->passwordResetUrl && $this->passwordResetResponseUrl) {
            $panel->passwordReset(
                fn(): RedirectResponse => redirect($this->resolveUrl($this->passwordResetUrl)),
                fn(): RedirectResponse => redirect($this->resolveUrl($this->passwordResetResponseUrl)),
            );
        }
        if ($this->registrationUrl) {
            $panel->registration(fn(): RedirectResponse => redirect($this->resolveUrl($this->registrationUrl)));
        }

        // Notifications
        if ($this->databaseNotifications) {
            $panel->databaseNotifications();
            $panel->databaseNotificationsPolling($this->databaseNotificationsPolling);
        }

        // Global Search
        if ($this->globalSearch) {
            $panel->globalSearch();
            if (!empty($this->globalSearchKeyBindings)) {
                $panel->globalSearchKeyBindings($this->globalSearchKeyBindings);
            }
        }

        // CSS Variables injection (sinkron dengan tokens.css)
        $this->injectCustomCSS($panel);
    }

    public function boot(Panel $panel): void
    {
        // Reserved: tambahkan hook lain bila diperlukan
    }

    private function resolveAssetUrl(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $v = trim($value);
        $lower = strtolower($v);

        if (str_starts_with($lower, 'http://') || str_starts_with($lower, 'https://') || str_starts_with($v, '/')) {
            return $v;
        }

        return asset($v);
    }

    /** @return non-empty-string */
    private function hookBodyStart(): string
    {
        // Gunakan constant Filament v4 jika tersedia, fallback ke string hook name
        return defined(PanelsRenderHook::class . '::BODY_START')
            ? PanelsRenderHook::BODY_START
            : 'panels::body.start';
    }

    /**
     * Inject custom CSS variables untuk menyamakan warna Filament & tokens.css
     */
    private function injectCustomCSS(Panel $panel): void
    {
        $cssVariables = $this->generateCSSVariablesFromColors();
        if ($cssVariables === '') {
            return;
        }

        $panel->renderHook($this->hookBodyStart(), static fn(): string => '<style>:root{' . $cssVariables . '}</style>');
    }

    /**
     * Generate CSS variables dari konfigurasi warna Filament.
     * Saat palette array diberikan, shade 500 dipakai sebagai warna utama.
     *
     * @return string CSS custom properties (tanpa selector)
     */
    private function generateCSSVariablesFromColors(): string
    {
        $css = '';

        // Primary
        if (isset($this->colors['primary'])) {
            $css .= $this->cssVar('--panel-primary', $this->extractColorHex($this->colors['primary']));
        }

        // (Opsional) kamu bisa tambahkan mapping lain bila perlu:
        // success / warning / danger / info / gray → tokens yang kamu gunakan di tokens.css
        // Contoh:
        foreach (['success', 'warning', 'danger', 'info', 'gray'] as $key) {
            if (isset($this->colors[$key])) {
                $css .= $this->cssVar("--panel-{$key}", $this->extractColorHex($this->colors[$key]));
            }
        }

        return $css;
    }

    private function cssVar(string $name, ?string $value): string
    {
        return $value ? sprintf('%s:%s;', $name, $value) : '';
    }

    /**
     * Terima string hex / Color / array palette → pulangkan hex utama (shade 500 bila ada).
     * @param mixed $color
     */
    private function extractColorHex(mixed $color): ?string
    {
        // if ($color instanceof Color) {
        //     // Filament\Support\Colors\Color::getHex() digunakan untuk mengambil nilai hex utama
        //     return $color->getHex();
        // }

        if (is_array($color)) {
            // shade 500 paling umum sebagai "main"
            return $color[500] ?? $color['500'] ?? (is_string(reset($color)) ? (string) reset($color) : null);
        }

        if (is_string($color) && $color !== '') {
            return $color;
        }

        return null;
    }

    /** string|Closure -> string (URL absolute/relative) */
    private function resolveUrl(string|Closure $url): string
    {
        $value = $url instanceof Closure ? (string) $url() : $url;
        return url($value);
    }
}
