<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\Roles\UpdateRoleRequest;
use App\Models\RBAC\Role;
use Illuminate\Http\RedirectResponse;

class UpdateRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $validated = $request->validated();

        // Prevent updating name for protected roles
        if (! $role->isProtected()) {
            $role->name = $validated['name'];
        }

        $role->guard_name = $validated['guard_name'];
        $role->save();

        $permissions = $validated['permissions'] ?? [];

        // Prevent removing all permissions from super-admin role
        if ($role->name === 'super-admin' && empty($permissions)) {
            return redirect()->route('roles.edit', $role)->with('error', 'Super-admin role must have at least one permission.');
        }

        $role->syncPermissions($permissions);

        return redirect()->route('roles.show', $role)->with('success', 'Role updated successfully.');
    }
}
