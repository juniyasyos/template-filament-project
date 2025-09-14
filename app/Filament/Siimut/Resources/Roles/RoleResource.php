<?php

namespace App\Filament\Siimut\Resources\Roles;

/**
 * Minimal stub: extends the package RoleResource.
 * Override only what you need to customize.
 */
class RoleResource extends \juniyasyos\ShieldLite\Resources\Roles\RoleResource
{
    // protected static bool $shouldRegisterNavigation = false;
    protected static ?int $navigationSort = 10;
    // Example override:
    // public static function getModelLabel(): string
    // {
    //     return 'Roles & Permissions';
    // }
}
