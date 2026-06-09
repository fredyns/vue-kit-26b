<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
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
            'user' => (new UserResource($user))->resolve(request()),
        ]);
    }
}
