<?php

return [
    'guard' => env('SHIELD_LITE_GUARD', 'web'),
    'super_admin_role' => env('SHIELD_LITE_SUPER_ADMIN_ROLE', 'Super-Admin'),

    // Permission backend driver: 'spatie' or 'array'
    'driver' => env('SHIELD_LITE_DRIVER', 'spatie'),

    // ex: "{resource}.{action}" -> "users.update", "posts.viewAny"
    'ability_format' => '{resource}.{action}',

    // daftar resource yang akan di-seed (opsional, bisa disuntik dari app)
    'resources' => [
        'users'  => ['viewAny','view','create','update','delete','restore','forceDelete'],
        'roles'  => ['viewAny','view','create','update','delete'],
        'posts'  => ['viewAny','view','create','update','delete'],
    ],

    // opsional: dukung teams Spatie (aktifkan di config/permission.php pada host app)
    'teams' => false,
];
