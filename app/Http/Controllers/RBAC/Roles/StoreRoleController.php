<?php

namespace App\Http\Controllers\RBAC\Roles;

use App\Http\Controllers\Controller;
use App\Http\Requests\RBAC\Roles\StoreRoleRequest;
use App\Models\RBAC\Role;
use Illuminate\Http\RedirectResponse;

class StoreRoleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->validated('name'),
            'guard_name' => $request->validated('guard_name'),
        ]);

        $permissions = $request->validated('permissions', []);

        if (! empty($permissions)) {
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.show', $role)->with('success', 'Role created successfully.');
    }
}
