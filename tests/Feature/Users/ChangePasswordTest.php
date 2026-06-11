<?php

use App\Models\RBAC\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutVite();
});

function userWithChangePasswordPermission(): User
{
    $user = User::factory()->create();
    $perm = Permission::findOrCreate('users.change-password', 'web');
    $user->givePermissionTo($perm);

    return $user;
}

test('guests are redirected to login for change password page', function () {
    $target = User::factory()->create();

    $this->get(route('users.change-password', $target))
        ->assertRedirect(route('login'));
});

test('guests are redirected to login for update password action', function () {
    $target = User::factory()->create();

    $this->patch(route('users.update-password', $target), [
        'password' => 'NewPassword1!',
        'password_confirmation' => 'NewPassword1!',
    ])->assertRedirect(route('login'));
});

test('unauthorized users receive a forbidden response on change password page', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('users.change-password', $target))
        ->assertForbidden();
});

test('unauthorized users receive a forbidden response on update password action', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->patch(route('users.update-password', $target), [
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])->assertForbidden();
});

test('authorized users can view the change password page', function () {
    $actor = userWithChangePasswordPermission();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->get(route('users.change-password', $target))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('users/ChangePassword')
            ->where('user.id', $target->id)
            ->where('user.name', $target->name)
        );
});

test('authorized users can change a user password', function () {
    $actor = userWithChangePasswordPermission();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->patch(route('users.update-password', $target), [
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ])
        ->assertRedirect(route('users.show', $target));

    expect(Hash::check('NewPassword1!', $target->fresh()->password))->toBeTrue();
});

test('update password validation rejects missing confirmation', function () {
    $actor = userWithChangePasswordPermission();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->patch(route('users.update-password', $target), [
            'password' => 'NewPassword1!',
        ])
        ->assertSessionHasErrors('password');
});

test('update password validation rejects mismatched confirmation', function () {
    $actor = userWithChangePasswordPermission();
    $target = User::factory()->create();

    $this->actingAs($actor)
        ->patch(route('users.update-password', $target), [
            'password' => 'NewPassword1!',
            'password_confirmation' => 'DifferentPassword1!',
        ])
        ->assertSessionHasErrors('password');
});

test('show page exposes can.changePassword based on permission', function () {
    $actor = User::factory()->create();
    $target = User::factory()->create();
    $showPerm = Permission::findOrCreate('users.show', 'web');
    $actor->givePermissionTo($showPerm);

    $this->actingAs($actor)
        ->get(route('users.show', $target))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('user.can.changePassword', false)
        );

    $changePerm = Permission::findOrCreate('users.change-password', 'web');
    $actor->givePermissionTo($changePerm);

    $this->actingAs($actor->fresh())
        ->get(route('users.show', $target))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('user.can.changePassword', true)
        );
});
