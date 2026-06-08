<?php

use App\Models\RBAC\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutVite();
});

function createUserWithPermission(string $permission): User
{
    $user = User::factory()->create();
    $perm = Permission::findOrCreate($permission, 'web');
    $user->givePermissionTo($perm);

    return $user;
}

test('guests are redirected to the login page', function () {
    $this->get(route('users.index'))
        ->assertRedirect(route('login'));
});

test('unauthorized users receive a forbidden response', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('authorized users can view the index page', function () {
    $user = createUserWithPermission('users.index');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/Index')
        );
});

test('index page returns paginated users', function () {
    $user = createUserWithPermission('users.index');
    User::factory()->count(15)->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/Index')
            ->has('users.data', 10)
            ->has('users.total')
            ->has('filters.search')
            ->has('can.create')
        );
});

test('index page supports search', function () {
    $user = createUserWithPermission('users.index');
    User::factory()->create(['name' => 'Findable User']);
    User::factory()->create(['name' => 'Another Person']);

    $this->actingAs($user)
        ->get(route('users.index', ['search' => 'Findable']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/Index')
            ->where('filters.search', 'Findable')
            ->has('users.data', fn ($data) => $data
                ->has(1)
                ->first(fn ($user) => $user
                    ->where('name', 'Findable User')
                    ->etc()
                )
            )
        );
});

test('index page eager loads web roles without N+1', function () {
    $user = createUserWithPermission('users.index');
    User::factory()->count(5)->create();

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/Index')
            ->has('users.data.0.web_roles')
        );
});

test('index page returns can.create based on user permissions', function () {
    $user = createUserWithPermission('users.index');

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('can.create', false)
        );

    $createPerm = Permission::findOrCreate('users.create', 'web');
    $user->givePermissionTo($createPerm);

    $this->actingAs($user->fresh())
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('can.create', true)
        );
});
