<?php

use App\Models\RBAC\Permission;
use App\Models\RBAC\Role;
use App\Models\User;

beforeEach(function () {
    $this->withoutVite();
});

function createUserWithRbacPermission(string $permission): User
{
    $user = User::factory()->create();
    $perm = Permission::findOrCreate($permission, 'web');
    $user->givePermissionTo($perm);

    return $user;
}

test('guests are redirected to the login page', function () {
    $this->get(route('roles.index'))
        ->assertRedirect(route('login'));
});

test('unauthorized users receive a forbidden response', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertForbidden();
});

test('authorized users can view the index page', function () {
    $user = createUserWithRbacPermission('rbac.index');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/Index')
        );
});

test('index page returns paginated roles', function () {
    $user = createUserWithRbacPermission('rbac.index');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/Index')
            ->has('roles.data')
            ->has('roles.total')
            ->has('filters.search')
            ->has('can.create')
        );
});

test('index page supports search', function () {
    $user = createUserWithRbacPermission('rbac.index');
    Role::findOrCreate('findable-role', 'web');
    Role::findOrCreate('another-role', 'web');

    $this->actingAs($user)
        ->get(route('roles.index', ['search' => 'findable']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/Index')
            ->where('filters.search', 'findable')
            ->has('roles.data', fn ($data) => $data
                ->has(1)
                ->first(fn ($role) => $role
                    ->where('name', 'findable-role')
                    ->etc()
                )
            )
        );
});

test('index page returns can.create based on user permissions', function () {
    $user = createUserWithRbacPermission('rbac.index');

    $this->actingAs($user)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('can.create', false)
        );

    $createPerm = Permission::findOrCreate('rbac.create', 'web');
    $user->givePermissionTo($createPerm);

    $this->actingAs($user->fresh())
        ->get(route('roles.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('can.create', true)
        );
});

test('index page includes permissions count per role', function () {
    $user = createUserWithRbacPermission('rbac.index');
    $role = Role::findOrCreate('test-role', 'web');
    $perm = Permission::findOrCreate('test.permission', 'web');
    $role->syncPermissions([$perm]);

    $this->actingAs($user)
        ->get(route('roles.index', ['search' => 'test-role']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/Index')
            ->has('roles.data', fn ($data) => $data
                ->has(1)
                ->first(fn ($r) => $r
                    ->where('name', 'test-role')
                    ->where('permissions_count', 1)
                    ->etc()
                )
            )
        );
});
