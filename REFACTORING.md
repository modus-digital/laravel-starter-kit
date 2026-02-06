# Optimization & Refactoring Opportunities

This document outlines areas for improvement identified in the codebase.

---

## Critical (Security)

### 1. Missing Authorization in Form Requests

**16 files** with `return true;` in `authorize()` methods:

- `app/Http/Requests/Role/StoreRoleRequest.php:18-21`
- `app/Http/Requests/Role/UpdateRoleRequest.php:18-23`
- `app/Http/Requests/User/StoreUserRequest.php:17-21`
- All similar request classes

These bypass authorization entirely - implement proper permission checks.

**Fix Example:**

```php
public function authorize(): bool
{
    return $this->user()->can(Permission::CREATE_ROLES->value);
}
```

---

## High Priority (Performance)

### 2. Missing Pagination

Endpoints loading all records into memory:

- `app/Http/Controllers/Admin/ClientController.php:68` - `$query->get()`
- `app/Http/Controllers/Admin/RoleController.php:45` - `$query->get()`
- `app/Http/Controllers/Admin/ActivityController.php:56` - `$query->get()`

**Fix:** Replace `->get()` with `->paginate(15)` or similar.

### 3. N+1 Query Opportunities

- `app/Http/Controllers/Admin/ClientController.php:104-107` - User relationships
- `app/Http/Controllers/Admin/UserController.php:63-72` - Manual transformation instead of eager loading

### 4. Inefficient Queries

- `app/Http/Controllers/Admin/ClientController.php:122-131` - Two queries where one subquery could work

**Current:**

```php
$existingUserIds = $client->users()->pluck('users.id');
$availableUsers = User::query()
    ->whereNotIn('id', $existingUserIds)
    ->get();
```

**Better:** Use a subquery to combine into one query.

---

## Medium Priority (Code Quality)

### 5. Duplicate Code Patterns

**Role mapping logic** repeated 3+ times:

- `app/Http/Controllers/Admin/UserController.php:75-81, 93-99`
- `app/Http/Controllers/Admin/ClientController.php:113-120`

```php
$roles = Role::query()
    ->where('name', '!=', RoleEnum::SUPER_ADMIN->value)
    ->get()
    ->map(fn (Role $role) => [
        'name' => $role->name,
        'label' => RoleEnum::tryFrom($role->name)?->getLabel() ?? str($role->name)->headline()->toString(),
    ]);
```

**Fix:** Extract to a `RoleService` or trait method.

**Conditional field mapping** (`if ($request->has())` repeated for every field):

- `app/Http/Controllers/Admin/ClientController.php:153-190`
- `app/Http/Controllers/Api/V1/Admin/UserController.php:321-349`

**Fix:** Create a helper method or use `$request->only()` with array filtering.

### 6. Inline Validation in Controllers

**18 occurrences** - should use Form Requests:

- `app/Http/Controllers/Admin/ClientController.php:275-279`
- `app/Http/Controllers/Admin/TranslationController.php` (4 occurrences)

**Current:**

```php
public function updateUserRole(Request $request, Client $client, User $user): RedirectResponse
{
    $request->validate([
        'role_id' => ['required', 'integer', 'exists:roles,id'],
    ]);
}
```

**Fix:** Create `UpdateUserRoleRequest` Form Request class.

### 7. Fat Controllers

`ClientController` has 11 methods handling:

- CRUD operations
- User management
- Role assignment
- Activity logging
- Soft delete operations
- Bulk operations

**Fix:** Split into:

- `ClientController` - CRUD only
- `ClientUserController` - User management
- Or create `ClientUserManager` action class

---

## Architecture Improvements

### 8. Missing Service Layer

Business logic in controllers that should be extracted:

- `app/Http/Controllers/Admin/ClientController.php:240-252` - Client user management
- Role/user assignment operations mixed with Activity logging

**Fix:** Create `ClientUserService` to handle client user management.

### 9. Manual Transformations Instead of Resources

- `app/Http/Controllers/Admin/UserController.php:63-72` - Manual `->map()` instead of using existing `UserResource`/`UserCollection`

**Current:**

```php
$users = $query->get()->map(fn (User $user) => [
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    // ...
]);
```

**Fix:** Use existing `UserResource` or `UserCollection`.

### 10. Duplicate Activity Query

Same query in two places:

- `app/Http/Controllers/TaskController.php:70-98` (show method)
- `app/Http/Controllers/TaskController.php:241-247` (activities method)

**Fix:** Extract to a query scope or service method.

---

## Frontend (React/TypeScript)

### 11. Type Duplication

Same types defined in multiple components:

- `resources/js/pages/modules/admin/clients/show.tsx:19-71`
- `resources/js/pages/modules/admin/clients/create.tsx`
- `resources/js/pages/modules/admin/clients/edit.tsx`
- `resources/js/pages/modules/admin/clients/index.tsx`

**Fix:** Create centralized types:

```typescript
// resources/js/types/admin/clients.ts
export type Client = {
    id: number;
    name: string;
    // ...
};

export type ClientUser = {
    id: number;
    name: string;
    email: string;
    // ...
};
```

### 12. Complex Rendering Logic

- `resources/js/pages/modules/tasks/show.tsx:39-80` - `renderActivityBadge` function

**Fix:** Extract to a reusable `<ActivityBadge />` component.

---

## Summary

| Category                 | Severity     | Count          |
| ------------------------ | ------------ | -------------- |
| Missing Authorization    | **CRITICAL** | 16 files       |
| Missing Pagination       | HIGH         | 3 endpoints    |
| Duplicate Code           | HIGH         | 3+ patterns    |
| N+1 Queries              | MEDIUM       | 2+ locations   |
| Inline Validation        | MEDIUM       | 18 occurrences |
| Fat Controllers          | MEDIUM       | 2 controllers  |
| Type Duplication (React) | MEDIUM       | 4+ pages       |

---

## Recommended Refactoring Priority

### Phase 1 (Critical - Security)

1. Implement authorization checks in all Form Requests
2. Move inline validations to Form Requests
3. Add permission enforcement

### Phase 2 (Performance)

1. Add pagination to list endpoints
2. Fix N+1 queries with eager loading
3. Extract duplicate query patterns to scopes/services

### Phase 3 (Code Quality)

1. Extract role formatting to service
2. Create `ClientUserService`
3. Consolidate React types

### Phase 4 (Architecture)

1. Refactor fat controllers
2. Create domain service classes
3. Componentize React UI logic
