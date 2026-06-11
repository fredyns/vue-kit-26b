<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ChangePasswordUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(User $user): Response
    {
        Gate::authorize('changePassword', $user);

        return Inertia::render('users/ChangePassword', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
            ],
        ]);
    }
}
