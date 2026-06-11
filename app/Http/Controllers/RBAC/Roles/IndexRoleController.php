<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Http\Controllers\Controller;
use App\Models\RBAC\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class IndexRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        Gate::authorize('viewAny', Role::class);

        $roles = Role::query()
            ->select(['id', 'name', 'guard_name', 'created_at'])
            ->withCount('permissions')
            ->when($request->string('search')->trim()->toString(), fn ($query, $search) => $query->search($search))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('roles/Index', [
            'roles' => $roles->through(fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'created_at' => $role->created_at,
                'permissions_count' => $role->permissions_count,
                'is_protected' => $role->isProtected(),
            ]),
            'filters' => [
                'search' => $request->string('search')->toString(),
            ],
            'can' => [
                'create' => $request->user()->can('create', Role::class),
            ],
        ]);
    }
}
