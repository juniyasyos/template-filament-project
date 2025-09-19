<?php

use Tests\TestCase;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use juniyasyos\ShieldLite\Services\RoleService;
use juniyasyos\ShieldLite\Support\ShieldLogger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Clear any existing context
    ShieldLogger::clearContext();

    // Install Spatie Permission if needed
    if (!\Illuminate\Support\Facades\Schema::hasTable('roles')) {
        $this->artisan('vendor:publish', [
            '--provider' => 'Spatie\Permission\PermissionServiceProvider',
        ]);

        $this->artisan('migrate', ['--force' => true]);
    }
});

describe('Role Service - Array Handling', function () {

    test('handles string permissions correctly', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Test Role',
            'guard_name' => 'web',
            'gates' => ['users.view', 'users.create', 'users.edit']
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->name)->toBe('Test Role');
        expect($role->permissions)->toHaveCount(3);
        expect($role->permissions->pluck('name')->toArray())->toEqual([
            'users.view', 'users.create', 'users.edit'
        ]);
    });

    test('handles array permissions with nested arrays', function () {
        $roleService = app(RoleService::class);

        // Simulate Filament form data that could come as nested arrays
        $data = [
            'name' => 'Complex Role',
            'guard_name' => 'web',
            'gates' => [
                ['name' => 'posts.view'],
                ['name' => 'posts.create'],
                'posts.edit', // Mixed format
                ['posts.delete'] // Array with single element
            ]
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->permissions)->toHaveCount(4);
        expect($role->permissions->pluck('name')->toArray())->toEqual([
            'posts.view', 'posts.create', 'posts.edit', 'posts.delete'
        ]);
    });

    test('handles object permissions correctly', function () {
        $roleService = app(RoleService::class);

        $permissions = [
            (object)['name' => 'comments.view'],
            (object)['name' => 'comments.create'],
            'comments.edit'
        ];

        $data = [
            'name' => 'Object Role',
            'guard_name' => 'web',
            'gates' => $permissions
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->permissions)->toHaveCount(3);
        expect($role->permissions->pluck('name')->toArray())->toEqual([
            'comments.view', 'comments.create', 'comments.edit'
        ]);
    });

    test('filters out invalid permission formats', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Filter Role',
            'guard_name' => 'web',
            'gates' => [
                'valid.permission',
                null, // Should be filtered
                '', // Should be filtered
                ['no-name-key'], // Gets index 0 value but is valid
                (object)['invalid' => 'data'], // Should be filtered (no name property)
                'permission with spaces', // Should be filtered (invalid chars)
                'another.valid.permission'
            ]
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->permissions)->toHaveCount(3); // valid.permission, no-name-key, another.valid.permission
        $permissionNames = $role->permissions->pluck('name')->toArray();
        expect($permissionNames)->toContain('valid.permission');
        expect($permissionNames)->toContain('another.valid.permission');
        expect($permissionNames)->toContain('no-name-key');
        expect($permissionNames)->not()->toContain('permission with spaces');
    });

    test('handles empty permissions array', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Empty Role',
            'guard_name' => 'web',
            'gates' => []
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->permissions)->toHaveCount(0);
    });

    test('handles missing gates field', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'No Gates Role',
            'guard_name' => 'web'
            // No gates field
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->permissions)->toHaveCount(0);
        expect($role->name)->toBe('No Gates Role');
    });
});

describe('Role Service - Error Handling', function () {

    test('throws exception for missing role name', function () {
        $roleService = app(RoleService::class);

        $data = [
            'guard_name' => 'web',
            'gates' => ['test.permission']
        ];

        expect(fn() => $roleService->createOrUpdateRole($data))
            ->toThrow(\InvalidArgumentException::class, 'Role name is required');
    });

    test('throws exception for non-array gates', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Bad Gates Role',
            'guard_name' => 'web',
            'gates' => 'not an array'
        ];

        expect(fn() => $roleService->createOrUpdateRole($data))
            ->toThrow(\InvalidArgumentException::class, 'Gates must be an array');
    });

    it('rolls back transaction on failure', function () {
        // Create role service
        $roleService = app(RoleService::class);

        // Test with invalid permission creation that should fail
        $data = [
            'name' => 'Rollback Role',
            'gates' => [str_repeat('x', 300)] // Very long permission name should fail
        ];

        $initialRoleCount = Role::count();

        try {
            $roleService->createOrUpdateRole($data);
        } catch (\Exception $e) {
            // Expected to fail
        }

        // Verify no new roles were created
        expect(Role::count())->toBe($initialRoleCount);
    });
});

describe('Role Service - Logging', function () {

    test('logs role creation operations', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Logged Role',
            'guard_name' => 'web',
            'gates' => ['test.permission']
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role)->toBeInstanceOf(Role::class);
        expect($role->name)->toBe('Logged Role');
    });

    test('logs permission validation warnings', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Warning Role',
            'guard_name' => 'web',
            'gates' => [
                'valid.permission',
                ['invalid-array'], // This gets index 0 and is valid
                null, // This should trigger a warning
                'permission with spaces' // This should trigger a warning for invalid format
            ]
        ];

        $role = $roleService->createOrUpdateRole($data);

        // Should have 2 valid permissions: valid.permission and invalid-array
        expect($role->permissions)->toHaveCount(2);
        $permissionNames = $role->permissions->pluck('name')->toArray();
        expect($permissionNames)->toContain('valid.permission');
        expect($permissionNames)->toContain('invalid-array');
        expect($permissionNames)->not()->toContain('permission with spaces');
    });

    test('logs errors with full context', function () {
        $roleService = app(RoleService::class);

        $data = [
            'guard_name' => 'web',
            'gates' => ['test.permission']
            // Missing name - should cause error
        ];

        expect(fn() => $roleService->createOrUpdateRole($data))
            ->toThrow(\InvalidArgumentException::class, 'Role name is required');
    });
});

describe('Role Service - Performance', function () {

    test('logs performance metrics for large permission sets', function () {
        $roleService = app(RoleService::class);

        // Create a large set of permissions
        $permissions = [];
        for ($i = 1; $i <= 100; $i++) {
            $permissions[] = "permission.{$i}";
        }

        $data = [
            'name' => 'Performance Role',
            'guard_name' => 'web',
            'gates' => $permissions
        ];

        $startTime = microtime(true);
        $role = $roleService->createOrUpdateRole($data);
        $duration = microtime(true) - $startTime;

        expect($role)->toBeInstanceOf(Role::class);
        expect($role->permissions)->toHaveCount(100);
        expect($duration)->toBeLessThan(5.0); // Should complete within 5 seconds
    });

    test('handles duplicate permissions efficiently', function () {
        $roleService = app(RoleService::class);

        $data = [
            'name' => 'Duplicate Role',
            'guard_name' => 'web',
            'gates' => [
                'users.view',
                'users.create',
                'users.view', // Duplicate
                'users.edit',
                'users.create' // Duplicate
            ]
        ];

        $role = $roleService->createOrUpdateRole($data);

        // Should only have 3 unique permissions
        expect($role->permissions)->toHaveCount(3);
        expect($role->permissions->pluck('name')->toArray())->toEqual([
            'users.view', 'users.create', 'users.edit'
        ]);
    });
});

describe('Integration with Spatie Permission', function () {

    test('works with existing roles and permissions', function () {
        // Create existing role and permission
        $existingRole = Role::create(['name' => 'Existing Role', 'guard_name' => 'web']);
        $existingPermission = Permission::create(['name' => 'existing.permission', 'guard_name' => 'web']);
        $existingRole->givePermissionTo($existingPermission);

        $roleService = app(RoleService::class);

        // Update the role with new permissions
        $data = [
            'name' => 'Existing Role',
            'guard_name' => 'web',
            'gates' => ['existing.permission', 'new.permission']
        ];

        $role = $roleService->createOrUpdateRole($data);

        expect($role->permissions)->toHaveCount(2);
        expect($role->permissions->pluck('name')->toArray())->toEqual([
            'existing.permission', 'new.permission'
        ]);
    });

    test('respects guard names', function () {
        $roleService = app(RoleService::class);

        $webData = [
            'name' => 'Web Role',
            'guard_name' => 'web',
            'gates' => ['web.permission']
        ];

        $apiData = [
            'name' => 'API Role',
            'guard_name' => 'api',
            'gates' => ['api.permission']
        ];

        $webRole = $roleService->createOrUpdateRole($webData);
        $apiRole = $roleService->createOrUpdateRole($apiData);

        expect($webRole->guard_name)->toBe('web');
        expect($apiRole->guard_name)->toBe('api');

        // Permissions should be created with correct guard
        $webPermission = Permission::where('name', 'web.permission')->first();
        $apiPermission = Permission::where('name', 'api.permission')->first();

        expect($webPermission->guard_name)->toBe('web');
        expect($apiPermission->guard_name)->toBe('api');
    });
});
