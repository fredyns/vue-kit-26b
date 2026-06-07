---
title: User Management CRUD Plan
date: 2026-06-07
status: planned
---

# User Management CRUD Plan

## Goal

Create an authenticated User management feature using Laravel single-action invocable controllers, Inertia Vue pages, Wayfinder route helpers, RBAC permissions, and Pest feature tests.

## Current Project Context

- The application is Laravel + Inertia Vue.
- Pages live in `resources/js/pages`.
- Existing authenticated routes are grouped in `routes/web.php` and `routes/settings.php`.
- The `User` model uses UUIDs and has searchable `name` and `email` fields.
- User roles already exist through `App\Enums\UserRole`:
  - `super-admin`
  - `internal`
  - `external`
- User permissions already exist:
  - `users.index`
  - `users.show`
  - `users.create`
  - `users.update`
  - `users.delete`
- The codebase does not currently have User CRUD pages, User policies, or an established CRUD table component pattern.

## Proposed Scope

### Included

- User list page with search, pagination, and role display.
- User detail page.
- User create page.
- User edit page.
- User store, update, and delete actions.
- RBAC authorization for each action.
- Form request validation.
- Pest feature tests for routes, authorization, validation, and persistence.
- Wayfinder generation after routes are added.

### Deferred

- Bulk user actions.
- User impersonation.
- Avatar upload.
- Password reset email from admin panel.
- Advanced audit logs.
- Soft deletes, unless explicitly required.

## Architecture

Use one invocable controller per route/action instead of a multi-method resource controller.

Recommended namespace:

```text
App\Http\Controllers\Users
```

Recommended controllers:

```text
IndexUserController
ShowUserController
CreateUserController
StoreUserController
EditUserController
UpdateUserController
DestroyUserController
```

Each controller should define only `__invoke(...)`.

## Routes

Add a dedicated route file only if the feature grows beyond a few routes. Otherwise, add the routes to `routes/web.php` inside the existing `auth` and `verified` middleware group.

Recommended routes:

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('users', IndexUserController::class)->name('users.index');
    Route::get('users/create', CreateUserController::class)->name('users.create');
    Route::post('users', StoreUserController::class)->name('users.store');
    Route::get('users/{user}', ShowUserController::class)->name('users.show');
    Route::get('users/{user}/edit', EditUserController::class)->name('users.edit');
    Route::patch('users/{user}', UpdateUserController::class)->name('users.update');
    Route::delete('users/{user}', DestroyUserController::class)->name('users.destroy');
});
```

Use implicit route model binding for `User`.

## Authorization

Create `App\Policies\UserPolicy` and map actions to the existing permissions:

| Policy method | Permission |
| --- | --- |
| `viewAny` | `users.index` |
| `view` | `users.show` |
| `create` | `users.create` |
| `update` | `users.update` |
| `delete` | `users.delete` |

Additional rules:

- Prevent users from deleting their own account from the admin User management screen.
- Consider preventing non-super-admin users from managing `super-admin` accounts.
- Keep profile self-service changes in existing settings pages, not this admin CRUD.

## Backend Implementation Plan

### 1. Index

Controller: `IndexUserController`

Responsibilities:

- Authorize `viewAny`.
- Query users with selected columns only.
- Support `search` query parameter against `name` and `email`.
- Eager load `webRoles` or include role data without N+1 queries.
- Return paginated results to `users/Index`.

Suggested props:

```text
users
filters.search
can.create
can.view
can.update
can.delete
```

### 2. Show

Controller: `ShowUserController`

Responsibilities:

- Authorize `view`.
- Load selected user fields and role data.
- Return `users/Show`.

### 3. Create

Controller: `CreateUserController`

Responsibilities:

- Authorize `create`.
- Load assignable roles.
- Return `users/Create`.

### 4. Store

Controller: `StoreUserController`

Responsibilities:

- Validate with `StoreUserRequest`.
- Create user using validated fields only.
- Hashing can rely on the `User` model password cast.
- Assign selected role IDs or role names through existing `User` role helper methods.
- Flash a success toast with `Inertia::flash(...)`.
- Redirect to the user detail page or user index.

### 5. Edit

Controller: `EditUserController`

Responsibilities:

- Authorize `update`.
- Load user and assignable roles.
- Return `users/Edit`.

### 6. Update

Controller: `UpdateUserController`

Responsibilities:

- Validate with `UpdateUserRequest`.
- Update `name`, `email`, and optionally `password` only when provided.
- Reset `email_verified_at` when email changes, matching profile behavior.
- Sync role assignments safely.
- Flash a success toast.
- Redirect back to edit or show.

### 7. Destroy

Controller: `DestroyUserController`

Responsibilities:

- Authorize `delete`.
- Prevent self-delete.
- Delete the user.
- Flash a success toast.
- Redirect to index.

## Validation Plan

Create Form Requests:

```text
App\Http\Requests\Users\StoreUserRequest
App\Http\Requests\Users\UpdateUserRequest
```

Suggested store rules:

- `name`: required string max 255
- `email`: required email max 255 unique users email
- `password`: required confirmed using `Password::defaults()`
- `roles`: nullable array
- `roles.*`: exists in RBAC roles table or valid role name, depending on selected implementation

Suggested update rules:

- `name`: required string max 255
- `email`: required email max 255 unique users email ignoring current user
- `password`: nullable confirmed using `Password::defaults()`
- `roles`: nullable array
- `roles.*`: exists in RBAC roles table or valid role name

## Frontend Plan

Recommended pages:

```text
resources/js/pages/users/Index.vue
resources/js/pages/users/Show.vue
resources/js/pages/users/Create.vue
resources/js/pages/users/Edit.vue
```

Recommended shared components if duplication appears:

```text
resources/js/components/users/UserForm.vue
resources/js/components/users/UserRoleBadges.vue
```

Frontend conventions:

- Use Inertia `<Link>` for navigation.
- Use Inertia `<Form>` or existing `useForm` style after checking nearby pages.
- Use Wayfinder imports from `@/actions/...` or `@/routes/...`; do not hardcode URLs in forms and links.
- Keep Vue pages single-root.
- Reuse existing layout and UI components from `resources/js/components` before creating new ones.

## Wayfinder Plan

After routes/controllers are added, run:

```bash
php artisan wayfinder:generate --with-form --no-interaction
```

Then use generated helpers in Vue pages for links and forms.

## Testing Plan

Create Pest feature tests under:

```text
tests/Feature/Users/UserManagementTest.php
```

Recommended tests:

- Guest users are redirected to login.
- Unverified authenticated users cannot access verified-only routes.
- Unauthorized users receive forbidden responses for each action.
- Authorized users can view the index page.
- Index page returns paginated users and supports search.
- Authorized users can view the create page.
- User can be created with valid data.
- Store validation rejects duplicate email and weak/missing password.
- Authorized users can view the edit page.
- User can be updated with valid data.
- Update validation ignores the current user's email.
- User password is only changed when a new password is provided.
- User cannot delete their own account through User management.
- Authorized users can delete another user.

Use factories and existing role/permission helpers instead of manual database setup where possible.

## Implementation Order

1. Create policy and register/verify authorization mapping.
2. Create Form Requests.
3. Create invocable controllers.
4. Add routes.
5. Generate Wayfinder actions/routes.
6. Create Inertia pages and any small shared user components.
7. Add Pest feature tests.
8. Run targeted tests.
9. Run Pint for PHP changes.

## Verification Commands

Run the minimum relevant checks after implementation:

```bash
php artisan test --compact tests/Feature/Users/UserManagementTest.php
vendor/bin/pint --dirty --format agent
```

If frontend route helpers or compiled assets are stale, also run the project frontend build/dev command as appropriate.

## Open Questions

- Should role assignment be editable by all users with `users.update`, or only by `super-admin`?
- Should admins be able to set passwords directly, or should creation send password reset/setup emails?
- Should deleting users be hard delete or soft delete?
- Should `external` users only see the index, as suggested by the existing `users.index` permission assignment?
