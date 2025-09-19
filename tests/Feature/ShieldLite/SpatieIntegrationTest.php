<?php

use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

describe('Shield Lite Spatie Integration', function () {

    it('user with permission can access resource', function () {
        $guard = config('shield-lite.guard', 'web');
        Permission::findOrCreate('posts.update', $guard);

        $role = Role::findOrCreate('Editor', $guard);
        $role->givePermissionTo('posts.update');

        $user = User::factory()->create();
        $user->assignRole('Editor');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        expect($user->can('posts.update'))->toBeTrue();
    });

    it('super admin bypasses all checks', function () {
        $guard = config('shield-lite.guard', 'web');
        Role::findOrCreate('Super-Admin', $guard);

        $user = User::factory()->create();
        $user->assignRole('Super-Admin');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Super admin should pass any permission check
        expect($user->can('any.permission'))->toBeTrue();
        expect($user->can('nonexistent.permission'))->toBeTrue();
        expect($user->isSuperAdmin())->toBeTrue();
    });

    it('denies without permission', function () {
        $user = User::factory()->create();
        
        // Create permission first, but don't assign to user
        $guard = config('shield-lite.guard', 'web');
        Permission::findOrCreate('posts.update', $guard);
        
        expect($user->can('posts.update'))->toBeFalse();
    });

    it('resets permission cache safely', function () {
        $this->artisan('permission:cache-reset')->assertSuccessful();
    });

    it('formats abilities correctly', function () {
        $result = \juniyasyos\ShieldLite\Support\Ability::format('update', 'posts');
        expect($result)->toBe('posts.update');
    });

    it('converts model to resource name', function () {
        $user = new \App\Models\User();
        $resource = \juniyasyos\ShieldLite\Support\ResourceName::fromModel($user);
        expect($resource)->toBe('users');
    });

    it('generic policy works with magic call', function () {
        $guard = config('shield-lite.guard');
        Permission::findOrCreate('users.view', $guard);

        $role = Role::findOrCreate('Viewer', $guard);
        $role->givePermissionTo('users.view');

        $user = \App\Models\User::factory()->create();
        $user->assignRole('Viewer');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $policy = new \juniyasyos\ShieldLite\Policies\GenericPolicy();
        $model = new \App\Models\User();

        expect($policy->view($user, $model))->toBeTrue();
    });
});
