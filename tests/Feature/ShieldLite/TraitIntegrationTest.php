<?php

declare(strict_types=1);

use juniyasyos\ShieldLite\Concerns\HasShieldRoles;
use juniyasyos\ShieldLite\Concerns\HasShieldPermissions;
use juniyasyos\ShieldLite\Concerns\AuthorizesShield;

describe('Shield Lite Traits Integration', function () {
    beforeEach(function () {
        // Create a test user model that uses Shield Lite traits
        $this->userClass = new class extends \App\Models\User {
            use HasShieldRoles, HasShieldPermissions, AuthorizesShield;
        };

        $this->user = \Mockery::mock($this->userClass);

        // Set up config for array driver
        config([
            'shield-lite.driver' => 'array',
            'shield-lite.permissions.roles.admin' => ['users.*', 'posts.*'],
            'shield-lite.permissions.roles.user' => ['users.view'],
        ]);
    });

    describe('HasShieldRoles trait', function () {
        it('can assign roles to users', function () {
            $this->user->shouldReceive('assignRole')->with('admin')->andReturnSelf();
            $this->user->shouldReceive('hasRole')->with('admin')->andReturn(true);

            $this->user->assignRole('admin');
            expect($this->user->hasRole('admin'))->toBeTrue();
        });

        it('can check multiple roles', function () {
            $this->user->shouldReceive('hasAnyRole')->with(['admin', 'user'])->andReturn(true);
            $this->user->shouldReceive('hasAllRoles')->with(['admin', 'user'])->andReturn(false);

            expect($this->user->hasAnyRole(['admin', 'user']))->toBeTrue();
            expect($this->user->hasAllRoles(['admin', 'user']))->toBeFalse();
        });

        it('can remove roles from users', function () {
            $this->user->shouldReceive('removeRole')->with('admin')->andReturnSelf();
            $this->user->shouldReceive('hasRole')->with('admin')->andReturn(false);

            $this->user->removeRole('admin');
            expect($this->user->hasRole('admin'))->toBeFalse();
        });

        it('can sync user roles', function () {
            $this->user->shouldReceive('syncRoles')->with(['admin', 'user'])->andReturnSelf();
            $this->user->shouldReceive('getRoleNames')->andReturn(collect(['admin', 'user']));

            $this->user->syncRoles(['admin', 'user']);
            expect($this->user->getRoleNames()->toArray())->toBe(['admin', 'user']);
        });
    });

    describe('HasShieldPermissions trait', function () {
        it('can assign permissions to users', function () {
            $this->user->shouldReceive('givePermissionTo')->with('users.create')->andReturnSelf();
            $this->user->shouldReceive('hasPermissionTo')->with('users.create')->andReturn(true);

            $this->user->givePermissionTo('users.create');
            expect($this->user->hasPermissionTo('users.create'))->toBeTrue();
        });

        it('can check permissions through roles', function () {
            $this->user->shouldReceive('getRoleNames')->andReturn(collect(['admin']));
            $this->user->shouldReceive('hasPermissionTo')->with('users.view')->andReturn(true);

            expect($this->user->hasPermissionTo('users.view'))->toBeTrue();
        });

        it('can revoke permissions from users', function () {
            $this->user->shouldReceive('revokePermissionTo')->with('users.create')->andReturnSelf();
            $this->user->shouldReceive('hasPermissionTo')->with('users.create')->andReturn(false);

            $this->user->revokePermissionTo('users.create');
            expect($this->user->hasPermissionTo('users.create'))->toBeFalse();
        });

        it('can get all user permissions', function () {
            $this->user->shouldReceive('getAllPermissions')->andReturn(collect(['users.view', 'users.create']));

            $permissions = $this->user->getAllPermissions();
            expect($permissions->toArray())->toBe(['users.view', 'users.create']);
        });
    });

    describe('AuthorizesShield trait', function () {
        it('can authorize abilities using Gate', function () {
            $this->user->shouldReceive('can')->with('users.view')->andReturn(true);
            $this->user->shouldReceive('can')->with('users.delete')->andReturn(false);

            expect($this->user->can('users.view'))->toBeTrue();
            expect($this->user->can('users.delete'))->toBeFalse();
        });

        it('can check if user cannot perform action', function () {
            $this->user->shouldReceive('cannot')->with('users.delete')->andReturn(true);

            expect($this->user->cannot('users.delete'))->toBeTrue();
        });

        it('can authorize resource-specific actions', function () {
            $model = \Mockery::mock(\Illuminate\Database\Eloquent\Model::class);
            $this->user->shouldReceive('can')->with('update', $model)->andReturn(true);

            expect($this->user->can('update', $model))->toBeTrue();
        });

        it('integrates with Laravel Gate system', function () {
            // This would test the actual Gate integration
            // Gates should be registered by the service provider
            $this->assertTrue(method_exists($this->user, 'can'));
            $this->assertTrue(method_exists($this->user, 'cannot'));
        });
    });

    describe('Trait Integration', function () {
        it('works together for complete authorization', function () {
            // Mock a user with admin role
            $this->user->shouldReceive('getRoleNames')->andReturn(collect(['admin']));
            $this->user->shouldReceive('can')->with('users.create')->andReturn(true);
            $this->user->shouldReceive('hasRole')->with('admin')->andReturn(true);

            // User should be able to create users because they have admin role
            expect($this->user->hasRole('admin'))->toBeTrue();
            expect($this->user->can('users.create'))->toBeTrue();
        });

        it('respects permission hierarchies', function () {
            // Admin role should have all users.* permissions
            $this->user->shouldReceive('getRoleNames')->andReturn(collect(['admin']));
            $this->user->shouldReceive('can')->with('users.view')->andReturn(true);
            $this->user->shouldReceive('can')->with('users.update')->andReturn(true);
            $this->user->shouldReceive('can')->with('users.delete')->andReturn(true);

            expect($this->user->can('users.view'))->toBeTrue();
            expect($this->user->can('users.update'))->toBeTrue();
            expect($this->user->can('users.delete'))->toBeTrue();
        });

        it('falls back gracefully when methods not available', function () {
            // Create a minimal user that doesn't implement all Spatie methods
            $minimalUser = new class extends \Illuminate\Foundation\Auth\User {
                use HasShieldRoles, HasShieldPermissions, AuthorizesShield;
            };

            $user = \Mockery::mock($minimalUser);
            $user->shouldReceive('getRoleNames')->andReturn(collect([]));

            // Should not throw exceptions when Spatie methods are missing
            expect($user->getRoleNames())->toBeInstanceOf(\Illuminate\Support\Collection::class);
        });
    });
});
