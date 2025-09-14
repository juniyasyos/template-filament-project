<?php

namespace App\Filament\Plugins;

use Closure;
use Filament\Contracts\Plugin;
use Filament\Enums\ThemeMode;
use Filament\Panel;
use Illuminate\Contracts\Support\Htmlable;

class SiimutTheme implements Plugin
{
    /**
     * Theme ID used by Filament.
     */
    protected string $id = 'siimut-theme';

    /**
     * Path to the theme CSS used by Vite.
     */
    protected string $viteThemePath = 'resources/css/filament/siimut/theme.css';

    /**
     * Map of colors accepted by Filament's Panel::colors().
     * Accepts either full palettes or hex values.
     *
     * @var array<string, array<string,string>|string>
     */
    protected array $colors = [];

    /**
     * Default theme mode.
     */
    protected ThemeMode $defaultMode;

    /**
     * Branding options
     */
    protected string | Htmlable | Closure | null $brandName = null;

    /** @var string|Htmlable|Closure|null */
    protected $brandLogo = null;

    /** @var string|Htmlable|Closure|null */
    protected $darkBrandLogo = null;

    /** @var string|Closure|null */
    protected $brandLogoHeight = null;

    /** @var string|Closure|null */
    protected $favicon = null;

    public function __construct()
    {
        // Initialize from config to mirror plugin-style configurability with env support.
        $this->colors = (array) config('siimut-theme.colors', []);

        $this->defaultMode = match (strtolower((string) config('siimut-theme.default_mode', 'system'))) {
            'light' => ThemeMode::Light,
            'dark' => ThemeMode::Dark,
            default => ThemeMode::System,
        };

        // Branding
        $brand = (array) config('siimut-theme.brand', []);
        $this->brandName = $brand['name'] ?? null;
        $this->brandLogo = $this->resolveAssetUrl($brand['logo'] ?? null);
        $this->darkBrandLogo = $this->resolveAssetUrl($brand['logo_dark'] ?? null);
        $this->brandLogoHeight = $brand['logo_height'] ?? null;
        $this->favicon = $this->resolveAssetUrl($brand['favicon'] ?? null);
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

        // Apply branding if provided
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
    }

    public function boot(Panel $panel): void
    {
        // No-op
    }

    /**
     * Override the theme CSS path.
     */
    public function viteTheme(string $path): static
    {
        $this->viteThemePath = $path;

        return $this;
    }

    /**
     * Set only the primary color (palette array or single hex).
     * Similar to Resma Awin Theme's ->primaryColor().
     *
     * @param array<string,string>|string $color
     */
    public function primaryColor(array|string $color): static
    {
        $this->colors['primary'] = $color;

        return $this;
    }

    /**
     * Set the full color map.
     *
     * @param array<string, array<string,string>|string> $colors
     */
    public function colors(array $colors): static
    {
        $this->colors = $colors;

        return $this;
    }

    /**
     * Set the default theme mode.
     */
    public function defaultMode(ThemeMode $mode): static
    {
        $this->defaultMode = $mode;

        return $this;
    }

    /**
     * Set brand name displayed in the top bar when no logo is present.
     */
    public function brandName(string | Htmlable | Closure | null $name): static
    {
        $this->brandName = $name;

        return $this;
    }

    /**
     * Set brand logo (light mode). Accepts a URL or Htmlable.
     */
    public function brandLogo(string | Htmlable | Closure | null $logo): static
    {
        $this->brandLogo = is_string($logo) ? $this->resolveAssetUrl($logo) : $logo;

        return $this;
    }

    /**
     * Set brand logo for dark mode.
     */
    public function darkModeBrandLogo(string | Htmlable | Closure | null $logo): static
    {
        $this->darkBrandLogo = is_string($logo) ? $this->resolveAssetUrl($logo) : $logo;

        return $this;
    }

    /**
     * Set brand logo height (e.g. '1.5rem', '40px').
     */
    public function brandLogoHeight(string | Closure | null $height): static
    {
        $this->brandLogoHeight = $height;

        return $this;
    }

    /**
     * Set favicon URL.
     */
    public function favicon(string | Closure | null $url): static
    {
        $this->favicon = is_string($url) ? $this->resolveAssetUrl($url) : $url;

        return $this;
    }

    /**
     * Resolves an asset URL from a config value.
     * - Absolute (http/https) or root-relative ('/...') is returned as-is.
     * - Otherwise it is treated as public-relative and passed to asset().
     */
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
