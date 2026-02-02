<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RBAC\Permission as PermissionEnum;
use App\Enums\RBAC\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\ActivityCollection;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

final class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Role::query()->withCount('permissions', 'users');

        // Apply search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['name', 'created_at', 'permissions_count', 'users_count'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $roles = $query->get();

        return Inertia::render('core/admin/roles/index', [
            'roles' => $roles,
            'filters' => $request->only(['search', 'sort_by', 'sort_direction']),
        ]);
    }

    public function create(): Response
    {
        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'label' => PermissionEnum::tryFrom($permission->name)?->getLabel() ?? str($permission->name)->headline()->toString(),
                'description' => PermissionEnum::tryFrom($permission->name)?->getDescription() ?? '',
                'category' => $this->getPermissionCategory($permission->name),
            ])
            ->groupBy('category');

        return Inertia::render('core/admin/roles/create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web',
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        // Assign permissions if provided
        if ($request->has('permissions') && is_array($request->permissions) && count($request->permissions) > 0) {
            $role->syncPermissions($request->permissions);
        }

        Activity::inLog('administration')
            ->event('rbac.role.created')
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ])
            ->log('activity.rbac.role.created');

        return redirect()->route('admin.roles.show', $role)
            ->with('success', __('admin.roles.created_successfully'));
    }

    public function show(Role $role): Response
    {
        $role->load(['permissions']);

        // Load activities
        $activities = $role->activities()
            ->latest()
            ->paginate(10);

        return Inertia::render('core/admin/roles/show', [
            'role' => $role,
            'activities' => new ActivityCollection($activities),
        ]);
    }

    public function edit(Role $role): Response
    {
        $role->load(['permissions']);

        $permissions = Permission::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'label' => PermissionEnum::tryFrom($permission->name)?->getLabel() ?? str($permission->name)->headline()->toString(),
                'description' => PermissionEnum::tryFrom($permission->name)?->getDescription() ?? '',
                'category' => $this->getPermissionCategory($permission->name),
            ])
            ->groupBy('category');

        // Check if this is a system role
        $isSystemRole = in_array($role->name, [RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN->value], true);

        return Inertia::render('core/admin/roles/edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'guard_name' => $role->guard_name,
                'icon' => $role->icon,
                'color' => $role->color,
                'permissions' => $role->permissions->pluck('name')->toArray(),
            ],
            'permissions' => $permissions,
            'isSystemRole' => $isSystemRole,
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        // Prevent updating system roles
        if (in_array($role->name, [RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN->value], true)) {
            return redirect()->route('admin.roles.show', $role)
                ->with('error', __('admin.roles.cannot_update_system_role'));
        }

        $role->update([
            'name' => $request->name,
            'icon' => $request->icon,
            'color' => $request->color,
        ]);

        // Update permissions
        if ($request->has('permissions')) {
            if (is_array($request->permissions) && count($request->permissions) > 0) {
                $role->syncPermissions($request->permissions);
            } else {
                $role->permissions()->detach();
            }
        }

        Activity::inLog('administration')
            ->event('rbac.role.updated')
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ])
            ->log('activity.rbac.role.updated');

        return redirect()->route('admin.roles.show', $role)
            ->with('success', __('admin.roles.updated_successfully'));
    }

    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deleting system roles
        if (in_array($role->name, [RoleEnum::SUPER_ADMIN->value, RoleEnum::ADMIN->value], true)) {
            return redirect()->route('admin.roles.index')
                ->with('error', __('admin.roles.cannot_delete_system_role'));
        }

        Activity::inLog('administration')
            ->event('rbac.role.deleted')
            ->causedBy(Auth::user())
            ->performedOn($role)
            ->withProperties([
                'role' => [
                    'id' => $role->id,
                    'name' => $role->name,
                ],
            ])
            ->log('activity.rbac.role.deleted');

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', __('admin.roles.deleted_successfully'));
    }

    /**
     * Get the category for a permission based on its name
     */
    private function getPermissionCategory(string $permissionName): string
    {
        if (str_contains($permissionName, 'users')) {
            return 'Users';
        }

        if (str_contains($permissionName, 'roles')) {
            return 'Roles';
        }

        if (str_contains($permissionName, 'api') || str_contains($permissionName, 'tokens')) {
            return 'API Tokens';
        }

        if (str_contains($permissionName, 'clients')) {
            return 'Clients';
        }

        if (str_contains($permissionName, 'socialite') || str_contains($permissionName, 'providers')) {
            return 'Socialite';
        }

        return 'General';
    }
}
