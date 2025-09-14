<?php

return [
    'models' => [
        'role' => \juniyasyos\ShieldLite\Models\ShieldRole::class,
        'user' => \App\Models\User::class,
    ],

    // Optional: define custom permissions to appear under the "Custom" tab
    // Example:
    // 'custom_permissions' => [
    //     'reports.download' => 'Download Reports',
    //     'system.flush_cache' => 'Flush System Cache',
    // ],
    'custom_permissions' => [
        // Add your custom permission keys here => 'Human readable label'
    ],

    // If true, users without any role are treated as superusers (full access)
    // For security, it's recommended to keep this false in production.
    'superuser_if_no_role' => false,

    // Cache configuration for gates/role lists
    'cache' => [
        'enabled' => true,
        // Time to live in seconds
        'ttl' => 3600,
        // Optional cache store (null = default store)
        'store' => null,
    ],

    // Navigation settings (label & group for the plugin menu item)
    'navigation' => [
        'role_label' => 'Role & Permissions',
        'role_group' => 'User Managements',
        'users_label' => 'Users',
        'users_group' => 'User Managements',
    ],

    // Toggle auto-registration of Filament resources
    'register_resources' => [
        'roles' => true,
        'users' => true,
    ],

    // Which Resource classes to register on the Panel.
    // You can point these to App\Filament\Resources\... after publishing stubs.
    'resources' => [
        'roles' => \App\Filament\Siimut\Resources\Roles\RoleResource::class,
        'users' => \App\Filament\Siimut\Resources\Users\UserResource::class,
    ],

    // Super admin defaults for example seeder
    'superadmin' => [
        'name' => 'Super Admin',
        'guard' => 'web',
    ],
];
