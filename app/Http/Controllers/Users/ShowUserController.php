<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): Response
    {
        Gate::authorize('view', $user);

        return Inertia::render('users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $user->webRoles->map(fn($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ]),
            ],
            'can' => [
                'update' => (bool)auth()->user()?->can('update', $user),
                'delete' => (bool)auth()->user()?->can('delete', $user),
            ],
        ]);
    }
}
