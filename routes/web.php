<?php

use App\Http\Controllers\Users\CreateUserController;
use App\Http\Controllers\Users\IndexUserController;
use App\Http\Controllers\Users\ShowUserController;
use App\Http\Controllers\Users\StoreUserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('users', IndexUserController::class)->name('users.index');
    Route::get('users/create', CreateUserController::class)->name('users.create');
    Route::post('users', StoreUserController::class)->name('users.store');
    Route::get('users/{user}', ShowUserController::class)->name('users.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/s3.php';
