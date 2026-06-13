<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Http\Controllers\Controller;
use App\Models\RBAC\Permission;
use App\Models\RBAC\Role;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CreateRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        Gate::authorize('create', Role::class);

        $permissions = Permission::query()
            ->select(['id', 'name'])
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Permission $permission) => explode('.', $permission->name)[0])
            ->map(fn ($group) => $group->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])->values());

        return Inertia::render('roles/Create', [
            'permissions' => $permissions,
            'guards' => ['web'],
        ]);
    }
}
