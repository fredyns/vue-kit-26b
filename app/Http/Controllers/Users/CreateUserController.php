<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\RBAC\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CreateUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        Gate::authorize('create', User::class);

        return Inertia::render('users/Create', [
            'roles' => Role::query()
                ->select(['id', 'name'])
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
