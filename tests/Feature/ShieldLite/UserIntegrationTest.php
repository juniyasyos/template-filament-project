<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('Shield Lite User Integration', function () {

    it('user model uses HasShield trait', function () {
        $user = User::factory()->create();

        // Check if HasShield methods are available
        expect(method_exists($user, 'isSuperAdmin'))->toBeTrue();
        expect(method_exists($user, 'getRoleNamesArray'))->toBeTrue();
        expect(method_exists($user, 'assignRole'))->toBeTrue(); // From Spatie
        expect(method_exists($user, 'can'))->toBeTrue(); // Overridden by HasShield
    });

    it('can assign and check roles', function () {
        $user = User::factory()->create();
        $guard = 'web';

        Role::findOrCreate('admin', $guard);

        $user->assignRole('admin');

        expect($user->hasRole('admin'))->toBeTrue();
        expect($user->hasRole('editor'))->toBeFalse();
    });

    it('can check super admin status', function () {
        $user = User::factory()->create();
        $guard = 'web';

        expect($user->isSuperAdmin())->toBeFalse();

        Role::findOrCreate('Super-Admin', $guard);
        $user->assignRole('Super-Admin');

        expect($user->isSuperAdmin())->toBeTrue();
    });

    it('super admin bypasses all permission checks', function () {
        $user = User::factory()->create();
        $guard = 'web';

        Role::findOrCreate('Super-Admin', $guard);
        $user->assignRole('Super-Admin');

        // Super admin should bypass any permission check
        expect($user->can('posts.create'))->toBeTrue();
        expect($user->can('users.delete'))->toBeTrue();
        expect($user->can('any.random.permission'))->toBeTrue();
    });

    it('returns role names as array', function () {
        $user = User::factory()->create();
        $guard = 'web';

        Role::findOrCreate('admin', $guard);
        Role::findOrCreate('editor', $guard);

        $user->assignRole(['admin', 'editor']);

        $roleNames = $user->getRoleNamesArray();

        expect($roleNames)->toBeArray();
        expect($roleNames)->toContain('admin');
        expect($roleNames)->toContain('editor');
    });

    it('normal user respects permission checks', function () {
        $user = User::factory()->create();
        $guard = 'web';

        Permission::findOrCreate('posts.create', $guard);

        // User without permission should be denied
        expect($user->can('posts.create'))->toBeFalse();

        // Give permission
        $user->givePermissionTo('posts.create');
        expect($user->can('posts.create'))->toBeTrue();
    });

});
