<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Role as RoleEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

final class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $query = User::query()->with(['roles']);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Include soft deleted if requested
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        // Only trashed records
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['name', 'email', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $users = $query->get()->map(fn (User $user) => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'status' => $user->status->value,
            'role' => $user->roles->first()?->name,
            'created_at' => $user->created_at->toISOString(),
            'deleted_at' => $user->deleted_at?->toISOString(),
        ]);

        // Get roles for form
        $roles = Role::query()
            ->where('name', '!=', RoleEnum::SUPER_ADMIN->value)
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->getLabel() ?? str($role->name)->headline()->toString(),
            ]);

        return Inertia::render('admin/users/index', [
            'users' => $users,
            'filters' => $request->only(['search', 'status', 'with_trashed', 'only_trashed', 'sort_by', 'sort_direction']),
            'roles' => $roles,
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function create(): Response
    {
        $roles = Role::query()
            ->where('name', '!=', RoleEnum::SUPER_ADMIN->value)
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->getLabel() ?? str($role->name)->headline()->toString(),
            ]);

        return Inertia::render('admin/users/create', [
            'roles' => $roles,
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'provider' => $request->provider ?? 'local',
            'email_verified_at' => $request->email_verified_at,
        ]);

        // Assign roles if provided
        if ($request->has('roles') && is_array($request->roles) && count($request->roles) > 0) {
            $user->syncRoles($request->roles);
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', __('admin.users.created_successfully'));
    }

    public function show(User $user): Response
    {
        $user->load(['roles', 'permissions']);

        // Load activities (frontend handles pagination/filtering)
        $activities = $user->activities()
            ->with('causer')
            ->latest()
            ->get();

        return Inertia::render('admin/users/show', [
            'user' => $user,
            'activities' => ActivityResource::collection($activities)->toArray(request()),
        ]);
    }

    public function edit(User $user): Response
    {
        $user->load(['roles']);

        $roles = Role::query()
            ->where('name', '!=', RoleEnum::SUPER_ADMIN->value)
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'label' => RoleEnum::tryFrom($role->name)?->getLabel() ?? str($role->name)->headline()->toString(),
            ]);

        return Inertia::render('admin/users/edit', [
            'user' => $user,
            'roles' => $roles,
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        if ($request->has('provider')) {
            $updateData['provider'] = $request->provider;
        }

        if ($request->has('email_verified_at')) {
            $updateData['email_verified_at'] = $request->email_verified_at;
        }

        $user->update($updateData);

        // Update roles if provided
        if ($request->has('roles')) {
            if (is_array($request->roles) && count($request->roles) > 0) {
                $user->syncRoles($request->roles);
            } else {
                $user->roles()->detach();
            }
        }

        return redirect()->route('admin.users.show', $user)
            ->with('success', __('admin.users.updated_successfully'));
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        Activity::inLog('administration')
            ->event('user.deleted')
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status->getLabel(),
                    'roles' => $user->roles->first()?->name
                        ? (RoleEnum::tryFrom($user->roles->first()->name)?->getLabel() ?? str($user->roles->first()->name)->headline()->toString())
                        : null,
                ],
            ])
            ->log('');

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.users.deleted_successfully'));
    }

    public function restore(string $userId): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->restore();

        Activity::inLog('administration')
            ->event('user.restored')
            ->causedBy(Auth::user())
            ->performedOn($user)
            ->withProperties([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status->getLabel(),
                    'roles' => $user->roles->first()?->name
                        ? (RoleEnum::tryFrom($user->roles->first()->name)?->getLabel() ?? str($user->roles->first()->name)->headline()->toString())
                        : null,
                ],
            ])
            ->log('');

        return redirect()->route('admin.users.show', $user)
            ->with('success', __('admin.users.restored_successfully'));
    }

    public function forceDelete(string $userId): RedirectResponse
    {
        $user = User::withTrashed()->findOrFail($userId);
        $user->forceDelete();

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.users.permanently_deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:users,id'],
        ]);

        $count = User::whereIn('id', $request->ids)->delete();

        Activity::inLog('administration')
            ->event('users.bulk_deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'user_ids' => $request->ids,
            ])
            ->log('');

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.users.bulk_deleted', ['count' => $count]));
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ]);

        $count = User::onlyTrashed()->whereIn('id', $request->ids)->restore();

        Activity::inLog('administration')
            ->event('users.bulk_restored')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'user_ids' => $request->ids,
            ])
            ->log('');

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.users.bulk_restored', ['count' => $count]));
    }
}
