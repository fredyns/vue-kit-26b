<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Http\Controllers\Controller;
use App\Models\RBAC\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DestroyRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Role $role): RedirectResponse
    {
        Gate::authorize('delete', $role);

        if ($role->isProtected()) {
            return redirect()->route('roles.index')->with('error', 'Protected roles cannot be deleted.');
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.show', $role)->with('error', 'Cannot delete role with assigned users.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
