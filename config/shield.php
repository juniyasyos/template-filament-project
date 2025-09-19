<?php

return [
    'guard' => env('SHIELD_LITE_GUARD', 'web'),
    'super_admin_roles' => [env('SHIELD_LITE_SUPER_ADMIN_ROLE', 'Super-Admin')],

    // Permission backend driver: 'spatie' (only supported driver)
    'driver' => env('SHIELD_LITE_DRIVER', 'spatie'),

    // Auto-register permissions from defineGates() in resources
    'auto_register' => env('SHIELD_LITE_AUTO_REGISTER', true),

    // Permission format: "{resource}.{action}" -> "users.update", "posts.viewAny"
    'ability_format' => '{resource}.{action}',
];
