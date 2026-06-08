<?php

use App\Http\Controllers\Users\IndexUserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::get('users', IndexUserController::class)->name('users.index');
});

require __DIR__.'/settings.php';
require __DIR__.'/s3.php';
