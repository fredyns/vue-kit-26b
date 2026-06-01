<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return [
    [
        'id' => Str::uuid(),
        'name' => 'Sys-Admin',
        'email' => 'admin@admin.com',
        'email_verified_at' => now(),
        'password' => Hash::make('admin'),
        'remember_token' => Str::random(10),
    ],
    [
        'id' => Str::uuid(),
        'name' => 'Sys-User',
        'email' => 'user@testing.com',
        'email_verified_at' => now(),
        'password' => Hash::make('password'),
        'remember_token' => Str::random(10),
    ],
];
