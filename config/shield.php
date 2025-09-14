<?php

return [
    'models' => [
        'role' => \juniyasyos\ShieldLite\Models\ShieldRole::class,
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
        'label' => 'Role & Permissions',
        'group' => 'Settings',
    ],

    // Super admin defaults for example seeder
    'superadmin' => [
        'name' => 'Super Admin',
        'guard' => 'web',
    ],
];
