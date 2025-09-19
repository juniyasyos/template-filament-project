<?php

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Install Spatie Permission if needed
    if (!Schema::hasTable('roles')) {
        $this->artisan('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
        ]);

        $this->artisan('migrate', ['--force' => true]);
    }
});

test('it only uses spatie permission tables', function () {
    // Check that only Spatie tables exist, no Shield tables
    expect(Schema::hasTable('roles'))->toBeTrue();
    expect(Schema::hasTable('permissions'))->toBeTrue();
    expect(Schema::hasTable('model_has_roles'))->toBeTrue();
    expect(Schema::hasTable('model_has_permissions'))->toBeTrue();
    expect(Schema::hasTable('role_has_permissions'))->toBeTrue();

    // Ensure Shield tables don't exist
    expect(Schema::hasTable('shield_roles'))->toBeFalse();
    expect(Schema::hasTable('shield_role_user'))->toBeFalse();
});

test('shield lite commands work with spatie only', function () {
    // Test role creation
    $this->artisan('shield-lite:role', ['name' => 'Test Role'])
        ->expectsOutput('✅ Role \'Test Role\' created successfully!')
        ->assertExitCode(0);

    // Verify role is in Spatie table
    $this->assertDatabaseHas('roles', [
        'name' => 'Test Role',
        'guard_name' => 'web'
    ]);

    // Test user creation
    $this->artisan('shield-lite:user', [
        '--email' => 'test@example.com',
        '--password' => 'password123'
    ])
        ->expectsOutput('✅ Super Admin created successfully!')
        ->assertExitCode(0);

    // Verify user has Super-Admin role via Spatie
    $user = User::where('email', 'test@example.com')->first();
    expect($user->hasRole('Super-Admin'))->toBeTrue();
});

test('user model only uses spatie traits', function () {
    $user = User::factory()->create();

    // Test Spatie trait methods exist
    expect(method_exists($user, 'roles'))->toBeTrue();
    expect(method_exists($user, 'permissions'))->toBeTrue();
    expect(method_exists($user, 'hasRole'))->toBeTrue();
    expect(method_exists($user, 'can'))->toBeTrue();

    // Test Shield trait methods exist
    expect(method_exists($user, 'isSuperAdmin'))->toBeTrue();
    expect(method_exists($user, 'getRoleNamesArray'))->toBeTrue();
});

test('no shield role model references exist', function () {
    // Test that no ShieldRole class exists
    expect(class_exists('juniyasyos\ShieldLite\Models\ShieldRole'))->toBeFalse();

    // Test that Role resource uses Spatie Role
    $model = \juniyasyos\ShieldLite\Resources\Roles\RoleResource::getModel();
    expect($model)->toBe(\Spatie\Permission\Models\Role::class);
});

test('permissions and roles work end to end', function () {
    // Create Super-Admin role first
    Role::create(['name' => 'Super-Admin']);

    // Create test permission
    $permission = Permission::create(['name' => 'test.view']);

    // Create test role and assign permission
    $role = Role::create(['name' => 'Test Manager']);
    $role->givePermissionTo($permission);

    // Create user and assign role
    $user = User::factory()->create();
    $user->assignRole($role);

    // Test permissions work
    expect($user->hasRole('Test Manager'))->toBeTrue();
    expect($user->can('test.view'))->toBeTrue();

    // Test Super Admin bypass
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('Super-Admin');
    expect($superAdmin->isSuperAdmin())->toBeTrue();
    expect($superAdmin->can('any.permission'))->toBeTrue();
});test('single permission system consistency', function () {
    // Create roles and permissions via different methods
    $this->artisan('shield-lite:role', ['name' => 'Manager']);

    Permission::create(['name' => 'users.create']);
    Permission::create(['name' => 'users.edit']);

    // Assign permissions to role
    $role = Role::findByName('Manager');
    $role->givePermissionTo(['users.create', 'users.edit']);

    // Create user and test
    $user = User::factory()->create();
    $user->assignRole('Manager');

    // Verify single source of truth
    expect(Role::where('name', 'Manager')->count())->toBe(1);
    expect($user->hasRole('Manager'))->toBeTrue();
    expect($user->can('users.create'))->toBeTrue();
    expect($user->can('users.edit'))->toBeTrue();
    expect($user->can('users.delete'))->toBeFalse();
});
