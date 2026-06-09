<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\RBAC\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EditUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): Response
    {
        Gate::authorize('update', $user);

        return Inertia::render('users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->webRoles->pluck('name')->toArray(),
            ],
            'roles' => Role::query()
                ->select(['id', 'name'])
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
