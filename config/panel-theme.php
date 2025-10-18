<?php

use Filament\Support\Colors\Color;

return [
    // The ID of the theme (used if you ever register a Theme asset by name)
    'id' => 'panel',

    // Where the Vite theme entry lives
    'vite_path' => 'resources/css/filament/panel/theme.css',

    // Brand + status colors. You can use hex (e.g. '#f59e0b') or full palettes.
    // Passing a single hex will generate the full palette automatically.
    // Modern, health-friendly palette defaults using Filament Colors.
    // You can still override `primary` via env (hex), but palettes are recommended.
    'colors' => [
        'primary' => env('PANEL_THEME_PRIMARY') ?: Color::Blue,
        'gray' => Color::Slate,
        'success' => Color::Emerald,
        'warning' => Color::Amber,
        'danger' => Color::Rose,
        'info' => Color::Sky,
    ],

    // Optional: register extra CSS variables usable inside your CSS.
    'css_variables' => [
        // 'brand-radius' => '12px',
        // 'brand-gap' => '0.75rem',
    ],

    // system | light | dark
    'default_mode' => env('PANEL_THEME_MODE', 'system'),

    // Branding settings for logo, name, favicon, etc.
    // You can pass absolute URLs (https://...), root-relative paths (/images/logo.svg),
    // or public-relative paths (images/logo.svg) which will be wrapped with asset().
    'brand' => [
        'name' => env('PANEL_BRAND_NAME'),
        'logo' => env('PANEL_BRAND_LOGO'),
        'logo_dark' => env('PANEL_BRAND_LOGO_DARK'),
        // Default height used by Filament is 1.5rem if null; set to override.
        'logo_height' => env('PANEL_BRAND_LOGO_HEIGHT'),
        'favicon' => env('PANEL_BRAND_FAVICON'),
    ],

    /**
     * UI configuration
     */
    'ui' => [
        'sidebar_collapsible' => env('PANEL_SIDEBAR_COLLAPSIBLE', true),
        'sidebar_width' => env('PANEL_SIDEBAR_WIDTH', '18rem'),
        'collapsed_sidebar_width' => env('PANEL_COLLAPSED_SIDEBAR_WIDTH', '7rem'),
        'max_content_width' => env('PANEL_MAX_CONTENT_WIDTH', '100rem'), // Set to very wide by default
    ],

    /**
     * Authentication configuration
     */
    'authentication' => [
        'login_url' => env('PANEL_LOGIN_URL', '/login'),
        'password_reset_url' => env('PANEL_PASSWORD_RESET_URL', '/forgot-password'),
        'password_reset_response_url' => env('PANEL_PASSWORD_RESET_RESPONSE_URL', '/reset-password'),
        'registration_url' => env('PANEL_REGISTRATION_URL', '/register'),
    ],

    /**
     * Notifications configuration
     */
    'notifications' => [
        'database_enabled' => env('PANEL_DATABASE_NOTIFICATIONS', true),
        'polling_interval' => env('PANEL_NOTIFICATIONS_POLLING', '30s'),
    ],

    /**
     * Global search configuration
     */
    'global_search' => [
        'enabled' => env('PANEL_GLOBAL_SEARCH', true),
        'key_bindings' => ['cmd+k', 'ctrl+k'],
    ],
];
