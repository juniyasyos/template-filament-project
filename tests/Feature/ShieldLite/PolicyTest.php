<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use juniyasyos\ShieldLite\Policies\GenericPolicy;

uses()->group('shield-lite-policies');

beforeEach(function () {
    config(['shield-lite.driver' => 'spatie']);
});

it('can resolve generic policy for models', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    // Create permission
    $permission = \Spatie\Permission\Models\Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $user->givePermissionTo($permission);

    // Test generic policy resolution
    $policy = new GenericPolicy();
    expect($policy->view($user, $targetUser))->toBeTrue();

    // Cleanup
    $user->delete();
    $targetUser->delete();
    $permission->delete();
});

it('can handle super admin bypass through generic policy', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    // Create super admin role
    $superRole = \Spatie\Permission\Models\Role::create(['name' => 'Super-Admin', 'guard_name' => 'web']);
    $user->assignRole($superRole);

    // Super admin should bypass all checks
    $policy = new GenericPolicy();
    expect($policy->viewAny($user))->toBeTrue();
    expect($policy->view($user, $targetUser))->toBeTrue();
    expect($policy->create($user))->toBeTrue();
    expect($policy->update($user, $targetUser))->toBeTrue();
    expect($policy->delete($user, $targetUser))->toBeTrue();

    // Cleanup
    $user->delete();
    $targetUser->delete();
    $superRole->delete();
});

it('can use magic method calls in generic policy', function () {
    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    // Create custom permission
    $permission = \Spatie\Permission\Models\Permission::create(['name' => 'users.customAction', 'guard_name' => 'web']);
    $user->givePermissionTo($permission);

    // Test magic method call
    $policy = new GenericPolicy();
    $result = $policy->customAction($user, $targetUser);
    expect($result)->toBeTrue();

    // Cleanup
    $user->delete();
    $targetUser->delete();
    $permission->delete();
});

it('can handle policy registration through PolicyResolver', function () {
    $resolver = new \juniyasyos\ShieldLite\Policies\PolicyResolver();

    // Test policy resolution for User model
    $policy = new GenericPolicy();
    expect($policy)->toBeInstanceOf(GenericPolicy::class);

    // Test policy registration with Gate
    Gate::policy(User::class, GenericPolicy::class);

    $user = User::factory()->create();
    $targetUser = User::factory()->create();

    // Create permission for testing
    $permission = \Spatie\Permission\Models\Permission::create(['name' => 'users.view', 'guard_name' => 'web']);
    $user->givePermissionTo($permission);

    // Test through Gate
    expect(Gate::forUser($user)->allows('view', $targetUser))->toBeTrue();

    // Cleanup
    $user->delete();
    $targetUser->delete();
    $permission->delete();
});
