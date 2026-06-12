<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Http\Controllers\Controller;
use App\Models\RBAC\Role;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ShowRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Role $role): Response
    {
        Gate::authorize('view', $role);

        $role->load('permissions');

        return Inertia::render('roles/Show', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'created_at' => $role->created_at,
                'updated_at' => $role->updated_at,
                'permissions' => $role->permissions->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                ]),
                'is_protected' => $role->isProtected(),
            ],
            'can' => [
                'update' => request()->user()?->can('update', $role) ?? false,
                'delete' => request()->user()?->can('delete', $role) ?? false,
            ],
        ]);
    }
}
