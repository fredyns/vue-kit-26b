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

test('guests are redirected from the create page', function () {
    $this->get(route('roles.create'))
        ->assertRedirect(route('login'));
});

test('unauthorized users cannot view the create page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertForbidden();
});

test('authorized users can view the create page', function () {
    $user = createUserWithRbacPermission('rbac.create');

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/Create')
            ->has('permissions')
            ->has('guards')
        );
});

test('create page groups permissions by resource prefix', function () {
    $user = createUserWithRbacPermission('rbac.create');
    Permission::findOrCreate('posts.create', 'web');
    Permission::findOrCreate('posts.delete', 'web');

    $this->actingAs($user)
        ->get(route('roles.create'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('roles/Create')
            ->has('permissions.posts')
        );
});

test('a role can be created with valid data', function () {
    $user = createUserWithRbacPermission('rbac.create');

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'editor',
            'guard_name' => 'web',
        ])
        ->assertRedirect();

    expect(Role::where('name', 'editor')->exists())->toBeTrue();
});

test('a role can be created with permissions', function () {
    $user = createUserWithRbacPermission('rbac.create');
    Permission::findOrCreate('posts.create', 'web');
    Permission::findOrCreate('posts.delete', 'web');

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'editor',
            'guard_name' => 'web',
            'permissions' => ['posts.create', 'posts.delete'],
        ])
        ->assertRedirect();

    $role = Role::where('name', 'editor')->first();
    expect($role)->not->toBeNull();
    expect($role->permissions->pluck('name')->toArray())
        ->toContain('posts.create')
        ->toContain('posts.delete');
});

test('store validation rejects a duplicate role name', function () {
    $user = createUserWithRbacPermission('rbac.create');
    Role::findOrCreate('editor', 'web');

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'editor',
            'guard_name' => 'web',
        ])
        ->assertSessionHasErrors('name');
});

test('store validation rejects missing required fields', function () {
    $user = createUserWithRbacPermission('rbac.create');

    $this->actingAs($user)
        ->post(route('roles.store'), [])
        ->assertSessionHasErrors(['name', 'guard_name']);
});

test('store validation rejects invalid permission names', function () {
    $user = createUserWithRbacPermission('rbac.create');

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'editor',
            'guard_name' => 'web',
            'permissions' => ['nonexistent.permission'],
        ])
        ->assertSessionHasErrors('permissions.0');
});

test('unauthorized users cannot store a role', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('roles.store'), [
            'name' => 'editor',
            'guard_name' => 'web',
        ])
        ->assertForbidden();

    expect(Role::where('name', 'editor')->exists())->toBeFalse();
});
