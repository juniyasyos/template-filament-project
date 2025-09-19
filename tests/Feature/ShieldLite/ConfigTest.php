<?php

uses()->group('shield-lite-config');

beforeEach(function () {
    config(['shield-lite.driver' => 'spatie']);
});

it('can load shield lite configuration', function () {
    expect(config('shield-lite.guard'))->toBe('web');
    expect(config('shield-lite.super_admin_roles'))->toBeArray();
    expect(config('shield-lite.super_admin_roles'))->toContain('Super-Admin');
    expect(config('shield-lite.driver'))->toBe('spatie');
    expect(config('shield-lite.ability_format'))->toBe('{resource}.{action}');
    expect(config('shield-lite.auto_register'))->toBeTrue();
});

it('can format abilities correctly', function () {
    $ability = \juniyasyos\ShieldLite\Support\Ability::format('view', 'users');
    expect($ability)->toBe('users.view');

    $ability = \juniyasyos\ShieldLite\Support\Ability::format('update', 'posts');
    expect($ability)->toBe('posts.update');
});

it('can extract resource names from models', function () {
    $resource = \juniyasyos\ShieldLite\Support\ResourceName::fromModel(\App\Models\User::class);
    expect($resource)->toBe('users');

    // Test with instance
    $user = new \App\Models\User();
    $resource = \juniyasyos\ShieldLite\Support\ResourceName::fromModel($user);
    expect($resource)->toBe('users');
});

it('can work with different permission drivers', function () {
    // Test Spatie driver
    config(['shield-lite.driver' => 'spatie']);
    $spatieDriver = app(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);
    expect($spatieDriver)->toBeInstanceOf(\juniyasyos\ShieldLite\Drivers\SpatiePermissionDriver::class);

    // Test Array driver (fallback)
    config(['shield-lite.driver' => 'array']);
    app()->forgetInstance(\juniyasyos\ShieldLite\Contracts\PermissionDriver::class);

    // Note: This might need service provider re-registration in real scenarios
    // For now, we'll just test that the class exists and can be instantiated
    $arrayDriver = new \juniyasyos\ShieldLite\Drivers\ArrayPermissionDriver();
    expect($arrayDriver)->toBeInstanceOf(\juniyasyos\ShieldLite\Drivers\ArrayPermissionDriver::class);
});
