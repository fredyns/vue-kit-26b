<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Enums\AuthGuard;
use App\Http\Controllers\Controller;
use App\Models\RBAC\Permission;
use App\Models\RBAC\Role;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EditRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Role $role): Response
    {
        Gate::authorize('update', $role);

        $role->load(['permissions:id,name']);

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

        return Inertia::render('roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'permission_ids' => $role->permissions->pluck('name')->values(),
            ],
            'permissions' => $permissions,
            'guards' => collect(AuthGuard::cases())->map(fn ($guard) => [
                'value' => $guard->value,
                'label' => $guard->label(),
            ])->values(),
            'can' => [
                'delete' => Gate::allows('delete', $role),
            ],
            'is_protected' => $role->isProtected(),
        ]);
    }
}
