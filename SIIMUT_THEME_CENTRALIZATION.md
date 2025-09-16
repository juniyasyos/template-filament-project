# Centralized Theme Configuration - SiimutTheme Plugin

## Overview

Theme dan UI properties telah dipusatkan ke dalam plugin `SiimutTheme` untuk meningkatkan organisasi kode dan konfigurasi yang terpusat.

## Changes Made

### 1. Enhanced SiimutTheme Plugin (`app/Filament/Plugins/SiimutTheme.php`)

Plugin telah diperluas untuk menangani:
- **Branding**: Brand name, logo, dark mode logo, logo height, favicon
- **Sidebar**: Collapsible sidebar, width configuration
- **Authentication**: Login, password reset, registration URLs
- **Notifications**: Database notifications dengan polling interval
- **Global Search**: Search functionality dengan key bindings

### 2. Extended Configuration (`config/siimut-theme.php`)

Konfigurasi telah diperluas dengan section baru:

```php
'ui' => [
    'sidebar_collapsible' => env('SIIMUT_SIDEBAR_COLLAPSIBLE', true),
    'sidebar_width' => env('SIIMUT_SIDEBAR_WIDTH', '18rem'),
    'collapsed_sidebar_width' => env('SIIMUT_COLLAPSED_SIDEBAR_WIDTH', '7rem'),
],

'authentication' => [
    'login_url' => env('SIIMUT_LOGIN_URL', '/login'),
    'password_reset_url' => env('SIIMUT_PASSWORD_RESET_URL', '/forgot-password'),
    'password_reset_response_url' => env('SIIMUT_PASSWORD_RESET_RESPONSE_URL', '/reset-password'),
    'registration_url' => env('SIIMUT_REGISTRATION_URL', '/register'),
],

'notifications' => [
    'database_enabled' => env('SIIMUT_DATABASE_NOTIFICATIONS', true),
    'polling_interval' => env('SIIMUT_NOTIFICATIONS_POLLING', '30s'),
],

'global_search' => [
    'enabled' => env('SIIMUT_GLOBAL_SEARCH', true),
    'key_bindings' => ['cmd+k', 'ctrl+k'],
],
```

### 3. Simplified Panel Provider (`app/Providers/Filament/SiimutPanelProvider.php`)

Panel provider sekarang sangat sederhana dan mendelegasikan semua konfigurasi UI ke plugin:

```php
public function panel(Panel $panel): Panel
{
    return $panel
        ->id('siimut')
        ->path('siimut')
        ->authGuard('web')
        // All UI configuration is now centralized in SiimutTheme plugin
        ->plugins([
            SiimutTheme::make(),
            ShieldLite::make(),
            FilamentLaravelBackupPlugin::make(),
            FilamentMediaManagerPlugin::make()
                ->allowUserAccess(true)
                ->allowSubFolders(true),
        ])
        // ... rest of configuration
```

## Environment Variables Support

Semua konfigurasi dapat dikustomisasi via environment variables:

```env
# Sidebar Configuration
SIIMUT_SIDEBAR_COLLAPSIBLE=true
SIIMUT_SIDEBAR_WIDTH=18rem
SIIMUT_COLLAPSED_SIDEBAR_WIDTH=7rem

# Authentication URLs
SIIMUT_LOGIN_URL=/login
SIIMUT_PASSWORD_RESET_URL=/forgot-password
SIIMUT_PASSWORD_RESET_RESPONSE_URL=/reset-password
SIIMUT_REGISTRATION_URL=/register

# Notifications
SIIMUT_DATABASE_NOTIFICATIONS=true
SIIMUT_NOTIFICATIONS_POLLING=30s

# Global Search
SIIMUT_GLOBAL_SEARCH=true

# Branding
SIIMUT_BRAND_NAME="My App"
SIIMUT_BRAND_LOGO=images/logo.svg
SIIMUT_BRAND_LOGO_DARK=images/logo-dark.svg
SIIMUT_BRAND_LOGO_HEIGHT=2rem
SIIMUT_BRAND_FAVICON=favicon.ico

# Theme
SIIMUT_THEME_PRIMARY=#3b82f6
SIIMUT_THEME_MODE=system
```

## Benefits

1. **Centralized Configuration**: Semua theme properties terpusat di satu plugin
2. **Environment Support**: Mudah dikustomisasi per environment
3. **Clean Panel Provider**: Panel provider lebih sederhana dan fokus
4. **Reusable Plugin**: Plugin dapat digunakan di panel lain
5. **Type Safety**: Full type hints untuk semua properties
6. **Filament v4 Compatibility**: Menggunakan API yang benar untuk Filament v4

## Usage

Plugin secara otomatis memuat konfigurasi dari file `config/siimut-theme.php` dan menerapkannya ke panel Filament. Tidak perlu konfigurasi tambahan.

Untuk mengubah konfigurasi, edit file `config/siimut-theme.php` atau set environment variables yang sesuai.
