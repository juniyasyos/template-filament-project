<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate;

uses()->group('shield-lite-traits');

beforeEach(function () {
    config(['shield-lite.driver' => 'spatie']);
});

it('can use HasShieldRoles trait on user model', function () {
    $user = User::factory()->create();

    // Check trait methods are available
    expect(method_exists($user, 'shieldRoles'))->toBeTrue();
    expect(method_exists($user, 'assignShieldRole'))->toBeTrue();
    expect(method_exists($user, 'removeShieldRole'))->toBeTrue();
    expect(method_exists($user, 'hasShieldRole'))->toBeTrue();
});

it('can use HasShieldPermissions trait on user model', function () {
    $user = User::factory()->create();

    // Check trait methods are available
    expect(method_exists($user, 'shieldPermissions'))->toBeTrue();
    expect(method_exists($user, 'giveShieldPermission'))->toBeTrue();
    expect(method_exists($user, 'revokeShieldPermission'))->toBeTrue();
    expect(method_exists($user, 'hasShieldPermission'))->toBeTrue();
});

it('can use AuthorizesShield trait on user model', function () {
    $user = User::factory()->create();

    // Check trait methods are available
    expect(method_exists($user, 'canShield'))->toBeTrue();
});

it('can create and assign roles through trait', function () {
    $user = User::factory()->create();

    // Create role through Spatie
    $role = \Spatie\Permission\Models\Role::create(['name' => 'Editor', 'guard_name' => 'web']);

    // Assign role through trait
    $user->assignShieldRole('Editor');

    // Check role assignment
    expect($user->hasShieldRole('Editor'))->toBeTrue();
    expect($user->hasShieldRole('Admin'))->toBeFalse();

    // Cleanup
    $user->delete();
    $role->delete();
});

it('can create and assign permissions through trait', function () {
    $user = User::factory()->create();

    // Create permission through Spatie
    $permission = \Spatie\Permission\Models\Permission::create(['name' => 'edit.posts', 'guard_name' => 'web']);

    // Assign permission through trait
    $user->giveShieldPermission('edit.posts');

    // Check permission assignment
    expect($user->hasShieldPermission('edit.posts'))->toBeTrue();
    expect($user->hasShieldPermission('delete.posts'))->toBeFalse();

    // Cleanup
    $user->delete();
    $permission->delete();
});

it('can check permissions through AuthorizesShield trait', function () {
    $user = User::factory()->create();

    // Create permission
    $permission = \Spatie\Permission\Models\Permission::create(['name' => 'view.dashboard', 'guard_name' => 'web']);
    $user->giveShieldPermission('view.dashboard');

    // Check through trait method
    expect($user->canShield('view.dashboard'))->toBeTrue();
    expect($user->canShield('admin.panel'))->toBeFalse();

    // Cleanup
    $user->delete();
    $permission->delete();
});
