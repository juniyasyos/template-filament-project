<?php

return [
    "model" => [
        "folder" => \Juniyasyos\FilamentMediaManager\Models\Folder::class,
        "media" => \Juniyasyos\FilamentMediaManager\Models\Media::class,
    ],

    "api" => [
        "active" => false,
        "middlewares" => [
            "api",
            "auth:sanctum"
        ],
        "prefix" => "api/media-manager",
        "resources" => [
            "folders" => \Juniyasyos\FilamentMediaManager\Http\Resources\FoldersResource::class,
            "folder" => \Juniyasyos\FilamentMediaManager\Http\Resources\FolderResource::class,
            "media" => \Juniyasyos\FilamentMediaManager\Http\Resources\MediaResource::class
        ]
    ],

    "filament" => [
        "active" => true,
        "resources" => [
            \Juniyasyos\FilamentMediaManager\Resources\FolderResource::class,
            \Juniyasyos\FilamentMediaManager\Resources\MediaResource::class,
        ]
    ],

    "user" => [
        'column_name' => 'name',
    ],

    'allow_user_access' => true,

    'slug_folder' => 'folder',

    "navigation_sort" => 0, // Backward-compat default sort

    // Filament navigation control
    'navigation' => [
        // Global group name for all resources
        'group' => null, // e.g. 'Media Manager' (null = use translations)

        // FolderResource navigation settings
        'folders' => [
            'register' => true,
            'label' => null, // e.g. 'Folders' (null = use translations)
            'icon' => 'heroicon-o-folder',
            'sort' => null, // null = fallback to `navigation_sort`
        ],

        // MediaResource navigation settings
        'media' => [
            'register' => false,
            'label' => null, // e.g. 'Media' (null = use translations)
            'icon' => 'heroicon-o-photo',
            'sort' => null, // null = fallback to `navigation_sort`
        ],
    ],
];
