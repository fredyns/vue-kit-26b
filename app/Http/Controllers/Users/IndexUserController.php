<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IndexUserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', User::class);

        $users = User::query()
            ->select(['id', 'name', 'email', 'email_verified_at', 'created_at'])
            ->with('webRoles:id,name')
            ->when($request->string('search')->trim()->toString(), fn ($query, $search) => $query->search($search))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
            'can' => [
                'create' => $request->user()->can('create', User::class),
                'update' => $request->user()->can('update', new User),
            ],
        ]);
    }
}
