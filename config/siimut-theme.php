<?php

return [
    // The ID of the theme (used if you ever register a Theme asset by name)
    'id' => 'siimut',

    // Brand + status colors. You can use hex (e.g. '#f59e0b') or full palettes.
    // Passing a single hex will generate the full palette automatically.
    'colors' => [
        'primary' => env('SIIMUT_THEME_PRIMARY', '#f59e0b'), // amber-like
        'gray' => env('SIIMUT_THEME_GRAY', '#3f3f46'),
        'success' => env('SIIMUT_THEME_SUCCESS', '#16a34a'),
        'warning' => env('SIIMUT_THEME_WARNING', '#f59e0b'),
        'danger' => env('SIIMUT_THEME_DANGER', '#dc2626'),
        'info' => env('SIIMUT_THEME_INFO', '#2563eb'),
    ],

    // Optional: register extra CSS variables usable inside your CSS.
    'css_variables' => [
        // 'brand-radius' => '12px',
        // 'brand-gap' => '0.75rem',
    ],

    // system | light | dark
    'default_mode' => env('SIIMUT_THEME_MODE', 'system'),
];
