<?php

use App\Http\Controllers\RBAC\Roles\IndexRoleController;
use App\Http\Controllers\RBAC\Roles\ShowRoleController;
use App\Http\Controllers\Users\ChangePasswordUserController;
use App\Http\Controllers\Users\CreateUserController;
use App\Http\Controllers\Users\DestroyUserController;
use App\Http\Controllers\Users\EditUserController;
use App\Http\Controllers\Users\IndexUserController;
use App\Http\Controllers\Users\ShowUserController;
use App\Http\Controllers\Users\StoreUserController;
use App\Http\Controllers\Users\UpdatePasswordUserController;
use App\Http\Controllers\Users\UpdateUserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('users', IndexUserController::class)->name('users.index');
    Route::get('users/create', CreateUserController::class)->name('users.create');
    Route::post('users', StoreUserController::class)->name('users.store');
    Route::get('users/{user}', ShowUserController::class)->name('users.show');
    Route::get('users/{user}/edit', EditUserController::class)->name('users.edit');
    Route::patch('users/{user}', UpdateUserController::class)->name('users.update');
    Route::delete('users/{user}', DestroyUserController::class)->name('users.destroy');
    Route::get('users/{user}/change-password', ChangePasswordUserController::class)->name('users.change-password');
    Route::patch('users/{user}/change-password', UpdatePasswordUserController::class)->name('users.update-password');

    Route::get('roles', IndexRoleController::class)->name('roles.index');
    Route::get('roles/{role}', ShowRoleController::class)->name('roles.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/s3.php';
