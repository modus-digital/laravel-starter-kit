<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\BulkDeleteUsersRequest;
use App\Http\Requests\User\BulkRestoreUsersRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\ActivityResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

final class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly RoleService $roleService) {}

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

        $users = $query->paginate(15);

        return Inertia::render('core/admin/users/index', [
            'users' => new UserCollection($users),
            'filters' => $request->only(['search', 'status', 'with_trashed', 'only_trashed', 'sort_by', 'sort_direction']),
            'roles' => $this->roleService->getFormattedRoles(),
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('core/admin/users/create', [
            'roles' => $this->roleService->getFormattedRoles(),
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

        return Inertia::render('core/admin/users/show', [
            'user' => $user,
            'activities' => ActivityResource::collection($activities)->toArray(request()),
        ]);
    }

    public function edit(User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['roles']);

        return Inertia::render('core/admin/users/edit', [
            'user' => $user,
            'roles' => $this->roleService->getFormattedRoles(),
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        // Hash password if provided
        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        // Extract roles before updating user
        $roles = $validated['roles'] ?? null;
        unset($validated['roles']);

        $user->update($validated);

        // Update roles if provided
        if ($roles !== null) {
            if (is_array($roles) && count($roles) > 0) {
                $user->syncRoles($roles);
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
                        ? __('enums.rbac.role.'.$user->roles->first()->name)
                        : null,
                ],
            ])
            ->log('activity.user.deleted');

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
                        ? __('enums.rbac.role.'.$user->roles->first()->name)
                        : null,
                ],
            ])
            ->log('activity.user.restored');

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

    public function bulkDelete(BulkDeleteUsersRequest $request): RedirectResponse
    {
        $count = User::whereIn('id', $request->ids)->delete();

        Activity::inLog('administration')
            ->event('users.bulk_deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'user_ids' => $request->ids,
            ])
            ->log('activity.user.bulk_deleted');

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.users.bulk_deleted', ['count' => $count]));
    }

    public function bulkRestore(BulkRestoreUsersRequest $request): RedirectResponse
    {
        $count = User::onlyTrashed()->whereIn('id', $request->ids)->restore();

        Activity::inLog('administration')
            ->event('users.bulk_restored')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'user_ids' => $request->ids,
            ])
            ->log('activity.user.bulk_restored');

        return redirect()->route('admin.users.index')
            ->with('success', __('admin.users.bulk_restored', ['count' => $count]));
    }
}
