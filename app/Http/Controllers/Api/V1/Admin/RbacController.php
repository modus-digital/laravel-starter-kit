<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class RbacController extends Controller
{
    /**
     * List all roles
     *
     * Get a paginated list of roles with optional filtering and search capabilities.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Default: 15. Example: 10
     * @queryParam search Search in name field. Example: admin
     * @queryParam with_permissions Include permissions in the response. Example: true
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "admin",
     *       "guard_name": "web",
     *       "icon": "heroicon-o-user",
     *       "color": "yellow",
     *       "is_internal": true,
     *       "permissions": ["access:control-panel", "create:users"],
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 1,
     *     "per_page": 15,
     *     "total": 3,
     *     "from": 1,
     *     "to": 3
     *   },
     *   "links": {
     *     "first": "[[APP_URL]]/api/v1/admin/rbac/roles?page=1",
     *     "last": "[[APP_URL]]/api/v1/admin/rbac/roles?page=1",
     *     "prev": null,
     *     "next": null
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function index(Request $request): JsonResponse|RoleCollection
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $query = Role::query();

        // Include permissions if requested
        if ($request->boolean('with_permissions')) {
            $query->with('permissions');
        }

        // Apply search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->paginate($request->integer('per_page', 15));

        return new RoleCollection($roles);
    }

    /**
     * Create a new role
     *
     * Create a new role with optional permissions assignment.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @bodyParam name string required The role name. Must be unique, lowercase with underscores/hyphens only. Example: content_manager
     * @bodyParam guard_name string required The guard name. Must be 'web'. Example: web
     * @bodyParam icon string The role icon (Heroicon name). Example: heroicon-o-user
     * @bodyParam color string The role color. Example: blue
     * @bodyParam permissions array Optional array of permission names to assign. Example: ["read:users", "update:users"]
     *
     * @response 201 {
     *   "id": 4,
     *   "name": "content_manager",
     *   "guard_name": "web",
     *   "icon": "heroicon-o-user",
     *   "color": "blue",
     *   "is_internal": false,
     *   "permissions": ["read:users", "update:users"],
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name has already been taken."]
     *   }
     * }
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name,
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        // Assign permissions if provided
        if ($request->has('permissions') && is_array($request->permissions)) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(new RoleResource($role->load('permissions')), 201);
    }

    /**
     * Get a specific role
     *
     * Retrieve detailed information about a specific role including its permissions.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam role integer required The role ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "admin",
     *   "guard_name": "web",
     *   "icon": "heroicon-o-user",
     *   "color": "yellow",
     *   "is_internal": true,
     *   "permissions": ["access:control-panel", "create:users", "read:users", "update:users", "delete:users"],
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     */
    public function show(Request $request, Role $role): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return response()->json(new RoleResource($role->load('permissions')));
    }

    /**
     * Update a role
     *
     * Update role information and permissions. Internal roles cannot be modified.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam role integer required The role ID. Example: 4
     *
     * @bodyParam name string The role name. Must be unique, lowercase with underscores/hyphens only. Example: content_editor
     * @bodyParam guard_name string The guard name. Must be 'web'. Example: web
     * @bodyParam icon string The role icon (Heroicon name). Example: heroicon-o-pencil
     * @bodyParam color string The role color. Example: green
     * @bodyParam permissions array Optional array of permission names to assign. Example: ["read:users", "update:users", "create:posts"]
     *
     * @response 200 {
     *   "id": 4,
     *   "name": "content_editor",
     *   "guard_name": "web",
     *   "icon": "heroicon-o-pencil",
     *   "color": "green",
     *   "is_internal": false,
     *   "permissions": ["read:users", "update:users", "create:posts"],
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["Internal roles cannot be modified."]
     *   }
     * }
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        // Check if this is an internal role that cannot be modified
        if ($this->isInternalRole($role)) {
            return response()->json([
                'message' => 'Internal roles cannot be modified',
            ], 422);
        }

        $role->update([
            'name' => $request->name ?? $role->name,
            'guard_name' => $request->guard_name ?? $role->guard_name,
            'icon' => $request->icon ?? $role->icon,
            'color' => $request->color ?? $role->color,
        ]);

        // Update permissions if provided
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions ?: []);
        }

        return response()->json(new RoleResource($role->load('permissions')));
    }

    /**
     * Delete a role
     *
     * Delete a role. Internal roles cannot be deleted.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam role integer required The role ID. Example: 4
     *
     * @response 204
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     * @response 422 {
     *   "message": "Internal roles cannot be deleted"
     * }
     */
    public function destroy(Request $request, Role $role): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if this is an internal role that cannot be deleted
        if ($this->isInternalRole($role)) {
            return response()->json([
                'message' => 'Internal roles cannot be deleted',
            ], 422);
        }

        $role->delete();

        return response()->json(null, 204);
    }

    /**
     * Get all available permissions
     *
     * Retrieve a list of all available permissions in the system.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @queryParam grouped boolean Group permissions by category. Default: false. Example: true
     *
     * @response 200 {
     *   "permissions": [
     *     {
     *       "name": "access:control-panel",
     *       "label": "Access Control Panel",
     *       "description": "Allows access to the admin control panel"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function permissions(Request $request): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $permissions = collect(Permission::cases())->map(fn (Permission $permission): array => [
            'name' => $permission->value,
            'label' => $permission->getLabel(),
            'description' => $permission->getDescription(),
            'is_super_admin_only' => $permission->isInternalOnly(),
        ]);

        if ($request->boolean('grouped')) {
            $grouped = $permissions->groupBy(fn (array $permission): string => explode(':', $permission['name'])[0]);

            return response()->json([
                'permissions' => $grouped,
            ]);
        }

        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    /**
     * Attach permissions to a role
     *
     * Attach one or more permissions to an existing role. Internal roles cannot be modified.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam role integer required The role ID. Example: 4
     *
     * @bodyParam permissions array required Array of permission names to attach. Example: ["create:posts", "read:posts"]
     *
     * @response 200 {
     *   "id": 4,
     *   "name": "content_editor",
     *   "guard_name": "web",
     *   "icon": "heroicon-o-pencil",
     *   "color": "green",
     *   "is_internal": false,
     *   "permissions": ["read:users", "update:users", "create:posts", "read:posts"],
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "permissions": ["Internal roles cannot be modified."]
     *   }
     * }
     */
    public function attachPermissions(Request $request, Role $role): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if this is an internal role that cannot be modified
        if ($this->isInternalRole($role)) {
            return response()->json([
                'message' => 'Internal roles cannot be modified',
            ], 422);
        }

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->givePermissionTo($request->permissions);

        return response()->json(new RoleResource($role->load('permissions')));
    }

    /**
     * Detach permissions from a role
     *
     * Remove one or more permissions from an existing role. Internal roles cannot be modified.
     *
     * @group Admin | RBAC
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam role integer required The role ID. Example: 4
     *
     * @bodyParam permissions array required Array of permission names to detach. Example: ["create:posts"]
     *
     * @response 200 {
     *   "id": 4,
     *   "name": "content_editor",
     *   "guard_name": "web",
     *   "icon": "heroicon-o-pencil",
     *   "color": "green",
     *   "is_internal": false,
     *   "permissions": ["read:users", "update:users", "read:posts"],
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "permissions": ["Internal roles cannot be modified."]
     *   }
     * }
     */
    public function detachPermissions(Request $request, Role $role): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if this is an internal role that cannot be modified
        if ($this->isInternalRole($role)) {
            return response()->json([
                'message' => 'Internal roles cannot be modified',
            ], 422);
        }

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role->revokePermissionTo($request->permissions);

        return response()->json(new RoleResource($role->load('permissions')));
    }

    /**
     * Check if a role is internal and cannot be modified
     */
    private function isInternalRole(Role $role): bool
    {
        return in_array($role->name, [
            RoleEnum::SUPER_ADMIN->value,
            RoleEnum::ADMIN->value,
        ]);
    }
}
