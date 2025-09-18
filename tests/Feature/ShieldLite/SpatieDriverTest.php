<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

uses()->group('shield-lite-spatie');

beforeEach(function () {
    // Set config to use spatie driver
    config(['shield-lite.driver' => 'spatie']);

    // Clear permission cache
    if (app()->bound('permission.cache.store')) {
        app('permission.cache.store')->flush();
    }
});

it('can resolve spatie permission driver', function () {
    $driver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);
    expect($driver)->toBeInstanceOf(\juniyasyos\ShieldLite\Drivers\SpatiePermissionDriver::class);
});

it('can create roles using spatie driver', function () {
    $driver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);

    // Test creating role
    $role = $driver->createRole('Test Role');
    expect($role)->toBeInstanceOf(Role::class);
    expect($role->name)->toBe('Test Role');

    // Cleanup
    $role->delete();
});

it('can create permissions using spatie driver', function () {
    $driver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);

    // Test creating permission
    $permission = $driver->createPermission('test.permission');
    expect($permission)->toBeInstanceOf(Permission::class);
    expect($permission->name)->toBe('test.permission');

    // Cleanup
    $permission->delete();
});

it('can assign and check roles on user', function () {
    $user = User::factory()->create();
    $driver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);

    // Create role
    $role = $driver->createRole('Tester');

    // Assign role to user
    $driver->assignRole($user, $role);

    // Check if user has role
    expect($driver->hasRole($user, 'Tester'))->toBeTrue();
    expect($driver->hasRole($user, 'NonExistent'))->toBeFalse();

    // Cleanup
    $user->delete();
    $role->delete();
});

it('can assign and check permissions on user', function () {
    $user = User::factory()->create();
    $driver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);

    // Create permission
    $permission = $driver->createPermission('test.permission');

    // Assign permission to user
    $driver->assignPermission($user, $permission);

    // Check if user has permission
    expect($driver->hasPermission($user, 'test.permission'))->toBeTrue();

    // Create another permission that user doesn't have
    $nonExistentPermission = $driver->createPermission('nonexistent.permission');
    expect($driver->hasPermission($user, 'nonexistent.permission'))->toBeFalse();

    // Cleanup
    $user->delete();
    $permission->delete();
    $nonExistentPermission->delete();
});

it('can work with role-permission hierarchy', function () {
    $user = User::factory()->create();
    $driver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);

    // Create role and permission
    $role = $driver->createRole('Editor');
    $permission = $driver->createPermission('posts.edit');

    // Assign permission to role
    $driver->assignPermissionToRole($role, $permission);

    // Assign role to user
    $driver->assignRole($user, $role);

    // User should have permission through role
    expect($driver->hasPermission($user, 'posts.edit'))->toBeTrue();

    // Cleanup
    $user->delete();
    $role->delete();
    $permission->delete();
});
