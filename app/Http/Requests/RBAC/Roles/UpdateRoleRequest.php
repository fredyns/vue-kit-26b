<?php

namespace App\Http\Requests\RBAC\Roles;

use App\Models\RBAC\Permission;
use App\Models\RBAC\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $this->user()?->can('update', $role) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $role = $this->route('role');
        $roleId = $role instanceof Role ? $role->id : $role;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('rbac_roles', 'name')->ignore($roleId)],
            'guard_name' => ['required', 'string', 'max:255'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::exists(Permission::class, 'name')],
        ];
    }
}
