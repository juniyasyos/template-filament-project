<?php

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\Livewire;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test permissions
    $permissions = [
        'role.index', 'role.create', 'role.update', 'role.delete',
        'users.viewAny', 'users.view', 'users.create', 'users.update',
        'users.delete', 'users.restore', 'users.forceDelete'
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission, 'guard_name' => 'web']);
    }

    // Create test roles
    $superAdmin = Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
    $admin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);

    // Assign all permissions to Super Admin
    $superAdmin->givePermissionTo(Permission::all());

    // Create test users
    $this->superAdminUser = User::factory()->create([
        'name' => 'Super Admin User',
        'email' => 'superadmin@test.com',
    ]);
    $this->superAdminUser->assignRole('Super Admin');

    $this->adminUser = User::factory()->create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
    ]);
    $this->adminUser->assignRole('Admin');
});

test('super admin can access role resource', function () {
    $this->actingAs($this->superAdminUser);

    $response = $this->get('/siimut/roles');

    $response->assertStatus(200);
    $response->assertSee('Roles');
});

test('super admin can view role edit page', function () {
    $this->actingAs($this->superAdminUser);

    $role = Role::where('name', 'Super Admin')->first();

    $response = $this->get("/siimut/roles/{$role->id}/edit");

    $response->assertStatus(200);
    $response->assertSee('Edit Role');
});

test('role edit page displays permissions', function () {
    $this->actingAs($this->superAdminUser);

    $role = Role::where('name', 'Super Admin')->first();

    // Test the Livewire component
    Livewire::test(\juniyasyos\ShieldLite\Resources\Roles\Pages\EditRole::class, ['record' => $role->id])
        ->assertStatus(200)
        ->assertSee('role.index')
        ->assertSee('users.viewAny')
        ->assertSee('users.create');
});

test('permissions are properly loaded in form', function () {
    $this->actingAs($this->superAdminUser);

    $role = Role::where('name', 'Super Admin')->first();

    Livewire::test(\juniyasyos\ShieldLite\Resources\Roles\Pages\EditRole::class, ['record' => $role->id])
        ->assertFormSet([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ])
        ->assertFormFieldExists('gates')
        ->call('save')
        ->assertHasNoFormErrors();
});

test('role permissions can be updated', function () {
    $this->actingAs($this->superAdminUser);

    $admin = Role::where('name', 'Admin')->first();
    $testPermissions = ['role.index', 'users.viewAny'];

    Livewire::test(\juniyasyos\ShieldLite\Resources\Roles\Pages\EditRole::class, ['record' => $admin->id])
        ->fillForm([
            'name' => 'Admin',
            'guard_name' => 'web',
            'gates' => $testPermissions,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $admin->refresh();
    expect($admin->permissions->pluck('name')->toArray())->toBe($testPermissions);
});

test('unauthorized user cannot access role management', function () {
    $regularUser = User::factory()->create([
        'email' => 'regular@test.com',
        'name' => 'Regular User'
    ]);
    $this->actingAs($regularUser);

    $response = $this->get('/siimut/roles');

    $response->assertStatus(403);
});

test('shield debug command works', function () {
    $this->artisan('shield:debug')
        ->expectsOutput('Shield Lite Debug Information')
        ->assertExitCode(0);
});

test('shield generate command creates permissions', function () {
    // Clear existing permissions
    Permission::query()->delete();
    Role::query()->delete();

    $this->artisan('shield:generate --super-admin')
        ->expectsOutput('Done.')
        ->assertExitCode(0);

    expect(Permission::count())->toBeGreaterThan(0);
    expect(Role::where('name', 'Super Admin')->exists())->toBeTrue();
});

test('filament login redirects to panel', function () {
    $response = $this->post('/siimut/login', [
        'email' => $this->superAdminUser->email,
        'password' => 'password', // default factory password
    ]);

    $response->assertRedirect('/siimut');
});

test('role resource table displays correctly', function () {
    $this->actingAs($this->superAdminUser);

    Livewire::test(\juniyasyos\ShieldLite\Resources\Roles\Pages\ListRoles::class)
        ->assertCanSeeTableRecords([
            Role::where('name', 'Super Admin')->first(),
            Role::where('name', 'Admin')->first(),
        ]);
});

test('user resource displays roles', function () {
    $this->actingAs($this->superAdminUser);

    $response = $this->get('/siimut/users');

    $response->assertStatus(200);
    $response->assertSee('Super Admin User');
    $response->assertSee('Admin User');
});
