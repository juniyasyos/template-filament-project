<?php

use Filament\Support\Colors\Color;

return [
    // The ID of the theme (used if you ever register a Theme asset by name)
    'id' => 'siimut',

    // Brand + status colors. You can use hex (e.g. '#f59e0b') or full palettes.
    // Passing a single hex will generate the full palette automatically.
    // Modern, health-friendly palette defaults using Filament Colors.
    // You can still override `primary` via env (hex), but palettes are recommended.
    'colors' => [
        'primary' => env('SIIMUT_THEME_PRIMARY') ?: Color::Blue,
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
    'default_mode' => env('SIIMUT_THEME_MODE', 'system'),

    // Branding settings for logo, name, favicon, etc.
    // You can pass absolute URLs (https://...), root-relative paths (/images/logo.svg),
    // or public-relative paths (images/logo.svg) which will be wrapped with asset().
    'brand' => [
        'name' => env('SIIMUT_BRAND_NAME'),
        'logo' => env('SIIMUT_BRAND_LOGO'),
        'logo_dark' => env('SIIMUT_BRAND_LOGO_DARK'),
        // Default height used by Filament is 1.5rem if null; set to override.
        'logo_height' => env('SIIMUT_BRAND_LOGO_HEIGHT'),
        'favicon' => env('SIIMUT_BRAND_FAVICON'),
    ],

    /**
     * UI configuration
     */
    'ui' => [
        'sidebar_collapsible' => env('SIIMUT_SIDEBAR_COLLAPSIBLE', true),
        'sidebar_width' => env('SIIMUT_SIDEBAR_WIDTH', '18rem'),
        'collapsed_sidebar_width' => env('SIIMUT_COLLAPSED_SIDEBAR_WIDTH', '7rem'),
    ],

    /**
     * Authentication configuration
     */
    'authentication' => [
        'login_url' => env('SIIMUT_LOGIN_URL', '/login'),
        'password_reset_url' => env('SIIMUT_PASSWORD_RESET_URL', '/forgot-password'),
        'password_reset_response_url' => env('SIIMUT_PASSWORD_RESET_RESPONSE_URL', '/reset-password'),
        'registration_url' => env('SIIMUT_REGISTRATION_URL', '/register'),
    ],

    /**
     * Notifications configuration
     */
    'notifications' => [
        'database_enabled' => env('SIIMUT_DATABASE_NOTIFICATIONS', true),
        'polling_interval' => env('SIIMUT_NOTIFICATIONS_POLLING', '30s'),
    ],

    /**
     * Global search configuration
     */
    'global_search' => [
        'enabled' => env('SIIMUT_GLOBAL_SEARCH', true),
        'key_bindings' => ['cmd+k', 'ctrl+k'],
    ],
];
