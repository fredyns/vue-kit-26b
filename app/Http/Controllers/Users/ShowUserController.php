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

        $user->load('webRoles');

        return Inertia::render('users/Show', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'web_roles' => $user->webRoles->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                ]),
                'can' => [
                    'view' => request()->user()?->can('view', $user) ?? false,
                    'update' => request()->user()?->can('update', $user) ?? false,
                    'changePassword' => request()->user()?->can('changePassword', $user) ?? false,
                    'delete' => request()->user()?->can('delete', $user) ?? false,
                ],
            ],
        ]);
    }
}
