<?php

use App\Http\Controllers\Users\IndexUserController;
use App\Http\Controllers\Users\ShowUserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('users', IndexUserController::class)->name('users.index');
    Route::get('users/{user}', ShowUserController::class)->name('users.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/s3.php';
