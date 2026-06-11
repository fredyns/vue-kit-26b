---
title: Role Management CRUD Plan
date: 2026-06-11
status: planned
---

# Role Management CRUD Plan

## Goal

Create a Role management feature using Laravel single-action invocable controllers, Inertia Vue pages, Wayfinder route helpers, RBAC permissions, and Pest feature tests.

## Current Project Context

- The application is Laravel + Inertia Vue.
- Pages live in `resources/js/pages`.
- The `Role` model (`App\Models\RBAC\Role`) extends Spatie's Permission Role with UUID support.
  - Uses `rbac_roles` table
  - Has `isProtected()` method that prevents deletion of system roles
- The `Permission` model (`App\Models\RBAC\Permission`) also extends Spatie with UUIDs.
  - Uses `rbac_permissions` table
- Protected roles (defined in `App\Enums\UserRole`) cannot be deleted:
  - `super-admin`
  - `internal`
  - `external`
- RBAC permissions already exist:
  - `rbac.index`
  - `rbac.show`
  - `rbac.create`
  - `rbac.update`
  - `rbac.delete`
- User CRUD already exists as a reference pattern in `App\Http\Controllers\Users`.

## Proposed Scope

### Included

- Role list page with search, pagination, and permission count.
- Role detail page with associated permissions.
- Role create page with permission checklist.
- Role edit page with permission checklist.
- Role store, update, and delete actions.
- RBAC authorization for each action.
- Form request validation.
- Pest feature tests for routes, authorization, validation, and persistence.
- Wayfinder generation after routes are added.

### Deferred

- Bulk role actions.
- Role hierarchy/nesting.
- Advanced audit logs.
- Soft deletes (roles use hard delete).

## Architecture

Use one invocable controller per route/action, following the User CRUD pattern.

Recommended namespace:

```text
App\Http\Controllers\RBAC\Roles
```

Recommended controllers:

```text
IndexRoleController
ShowRoleController
CreateRoleController
StoreRoleController
EditRoleController
UpdateRoleController
DestroyRoleController
```

Each controller should define only `__invoke(...)`.

## Routes

Add to `routes/web.php` inside the existing `auth` and `verified` middleware group, or create `routes/rbac.php` if preferred.

Recommended routes:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    // ... existing user routes ...

    Route::get('roles', IndexRoleController::class)->name('roles.index');
    Route::get('roles/create', CreateRoleController::class)->name('roles.create');
    Route::post('roles', StoreRoleController::class)->name('roles.store');
    Route::get('roles/{role}', ShowRoleController::class)->name('roles.show');
    Route::get('roles/{role}/edit', EditRoleController::class)->name('roles.edit');
    Route::patch('roles/{role}', UpdateRoleController::class)->name('roles.update');
    Route::delete('roles/{role}', DestroyRoleController::class)->name('roles.destroy');
});
```

Use implicit route model binding for `Role` (bind by UUID).

## Authorization

Create `App\Policies\RolePolicy` and map actions to the existing `rbac.*` permissions:

| Policy method | Permission |
| --- | --- |
| `viewAny` | `rbac.index` |
| `view` | `rbac.show` |
| `create` | `rbac.create` |
| `update` | `rbac.update` |
| `delete` | `rbac.delete` |

Additional rules:

- Prevent deletion of protected roles (those in `UserRole::values()`).
- Prevent modification of protected role names.
- Super-admin role should always retain all permissions (enforced at policy or controller level).

## Backend Implementation Plan

### 1. Index

Controller: `IndexRoleController`

Responsibilities:

- Authorize `viewAny`.
- Query roles with selected columns.
- Support `search` query parameter against `name`.
- Eager load `permissions` count without N+1 queries.
- Return paginated results to `roles/Index`.

Suggested props:

```text
roles
filters.search
can.create
can.view
can.update
can.delete
```

### 2. Show

Controller: `ShowRoleController`

Responsibilities:

- Authorize `view`.
- Load role with all associated permissions.
- Return `roles/Show`.

Suggested props:

```text
role (id, name, guard_name, created_at, updated_at)
role.permissions (id, name)
can.update
can.delete
```

### 3. Create

Controller: `CreateRoleController`

Responsibilities:

- Authorize `create`.
- Load all available permissions grouped by resource (optional grouping strategy).
- Return `roles/Create`.

Suggested props:

```text
permissions (id, name, grouped by resource prefix)
guards (typically ['web'])
```

### 4. Store

Controller: `StoreRoleController`

Responsibilities:

- Validate with `StoreRoleRequest`.
- Create role using validated fields (name, guard_name).
- Sync permissions via Spatie's `syncPermissions()` method.
- Flash a success toast with `Inertia::flash(...)`.
- Redirect to the role detail page or role index.

### 5. Edit

Controller: `EditRoleController`

Responsibilities:

- Authorize `update`.
- Load role with current permissions (as array of IDs or names).
- Load all available permissions for the checklist.
- Return `roles/Edit`.

Suggested props:

```text
role (id, name, guard_name, permission_ids)
permissions (id, name, grouped by resource prefix)
can.delete
is_protected (bool)
```

### 6. Update

Controller: `UpdateRoleController`

Responsibilities:

- Validate with `UpdateRoleRequest`.
- Update `name` only if role is not protected.
- Update `guard_name` if provided.
- Sync permissions via `syncPermissions()`.
- Prevent removing all permissions from `super-admin` role (optional safety check).
- Flash a success toast.
- Redirect back to edit or show.

### 7. Destroy

Controller: `DestroyRoleController`

Responsibilities:

- Authorize `delete`.
- Prevent deletion if `role->isProtected()` returns true.
- Prevent deletion if role has assigned users (optional, or reassign users).
- Delete the role (Spatie will handle pivot table cleanup).
- Flash a success toast.
- Redirect to index.

## Validation Plan

Create Form Requests:

```text
App\Http\Requests\RBAC\Roles\StoreRoleRequest
App\Http\Requests\RBAC\Roles\UpdateRoleRequest
```

Suggested store rules:

- `name`: required string, max 255, unique in `rbac_roles` table
- `guard_name`: required string, typically `web`
- `permissions`: nullable array
- `permissions.*`: exists in `rbac_permissions` table (validate by name or ID)

Suggested update rules:

- `name`: required string, max 255, unique ignoring current role
- `guard_name`: required string
- `permissions`: nullable array
- `permissions.*`: exists in `rbac_permissions` table

## Frontend Plan

Recommended pages:

```text
resources/js/pages/roles/Index.vue
resources/js/pages/roles/Show.vue
resources/js/pages/roles/Create.vue
resources/js/pages/roles/Edit.vue
```

Recommended shared components:

```text
resources/js/components/roles/RoleForm.vue
resources/js/components/roles/PermissionChecklist.vue
resources/js/components/roles/PermissionGroup.vue
```

### Permission Checklist UX

The permission checklist should:

- Group permissions by resource prefix (e.g., `users.create`, `users.update` grouped under "Users").
- Display permission names in human-readable format (`users.create` → "Create Users").
- Support "Select All" per resource group.
- Show indeterminate state for partially selected groups.
- Display permission count per group.

Frontend conventions:

- Use Inertia `<Link>` for navigation.
- Use Inertia `<Form>` or `useForm` style.
- Use Wayfinder imports from `@/actions/...` or `@/routes/...`.
- Keep Vue pages single-root.
- Reuse existing layout and UI components.

## Wayfinder Plan

After routes/controllers are added, run:

```bash
php artisan wayfinder:generate --with-form --no-interaction
```

Then use generated helpers in Vue pages for links and forms.

## Testing Plan

Create Pest feature tests under:

```text
tests/Feature/RBAC/Roles/RoleManagementTest.php
```

Recommended tests:

- Guest users are redirected to login.
- Unverified authenticated users cannot access verified-only routes.
- Unauthorized users receive forbidden responses for each action.
- Authorized users can view the index page.
- Index page returns paginated roles and supports search.
- Authorized users can view the create page.
- Role can be created with valid data and permissions.
- Store validation rejects duplicate role names.
- Authorized users can view the edit page.
- Role can be updated with valid data.
- Update validation ignores the current role's name.
- Protected roles cannot be deleted (super-admin, internal, external).
- Non-protected roles can be deleted.
- Role with assigned users cannot be deleted (if implementing this restriction).
- Permission sync works correctly (add, remove, replace).

## Implementation Order

1. Create policy and register/verify authorization mapping.
2. Create Form Requests.
3. Create invocable controllers.
4. Add routes.
5. Generate Wayfinder actions/routes.
6. Create Inertia pages and shared role components (PermissionChecklist).
7. Add Pest feature tests.
8. Run targeted tests.
9. Run Pint for PHP changes.

## Verification Commands

Run the minimum relevant checks after implementation:

```bash
php artisan test --compact tests/Feature/RBAC/Roles/RoleManagementTest.php
vendor/bin/pint --dirty --format agent
```

If frontend route helpers or compiled assets are stale, also run:

```bash
npm run build
```

## Open Questions

- Should `super-admin` role permissions be immutable (always have all permissions)?
- Should we allow custom guard names, or enforce `web` only?
- Should deleting a role with assigned users be blocked, or should users be reassigned?
- Should we display which users have each role in the role detail view?
