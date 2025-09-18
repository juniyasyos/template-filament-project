<?php

use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('Shield Lite Spatie Integration', function () {

    it('allows update via mapped permission', function () {
        $guard = config('shield-lite.guard');
        Permission::findOrCreate('posts.update', $guard);

        $role = Role::findOrCreate('Editor', $guard);
        $role->givePermissionTo('posts.update');

        $user = \App\Models\User::factory()->create();
        $user->assignRole('Editor');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $post = new \App\Models\Post(); // dummy model
        expect(Gate::forUser($user)->allows('update', $post))->toBeTrue();
    });

    it('super admin bypasses all checks', function () {
        $guard = config('shield-lite.guard');
        \Spatie\Permission\Models\Role::findOrCreate('Super-Admin', $guard);

        $user = \App\Models\User::factory()->create();
        $user->assignRole('Super-Admin');
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $someModel = new \App\Models\User();
        expect(Gate::forUser($user)->allows('delete', $someModel))->toBeTrue();
    });

    it('denies without permission', function () {
        $user = \App\Models\User::factory()->create();
        $model = new \App\Models\Post();
        expect(Gate::forUser($user)->allows('update', $model))->toBeFalse();
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
