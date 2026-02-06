<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\BulkDeleteClientsRequest;
use App\Http\Requests\Client\BulkRestoreClientsRequest;
use App\Http\Requests\Client\StoreClientNewUserRequest;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\StoreClientUserRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Requests\Client\UpdateUserRoleRequest;
use App\Http\Resources\ActivityCollection;
use App\Http\Resources\UserCollection;
use App\Models\Modules\Clients\Client;
use App\Models\User;
use App\Services\ClientUserService;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

final class ClientController extends Controller
{
    public function __construct(
        private readonly RoleService $roleService,
        private readonly ClientUserService $clientUserService
    ) {}

    public function index(Request $request): Response
    {
        $query = Client::query();

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
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
        $allowedSorts = ['name', 'status', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $clients = $query->paginate(15);

        return Inertia::render('modules/admin/clients/index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status', 'with_trashed', 'only_trashed', 'sort_by', 'sort_direction']),
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('modules/admin/clients/create', [
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = Client::create([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'country' => $request->country,
            'status' => $request->status,
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.created_successfully'));
    }

    public function show(Client $client): Response
    {
        $users = $client->users()
            ->with(['roles'])
            ->latest()
            ->paginate(10);

        $activities = $client->activities()
            ->latest()
            ->paginate(10);

        $availableUsers = User::query()
            ->whereDoesntHave('clients', fn ($query) => $query->where('clients.id', $client->id))
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]);

        return Inertia::render('modules/admin/clients/show', [
            'client' => $client,
            'users' => new UserCollection($users),
            'activities' => new ActivityCollection($activities),
            'roles' => $this->roleService->getFormattedRolesForClient(),
            'availableUsers' => $availableUsers,
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('modules/admin/clients/edit', [
            'client' => $client,
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.updated_successfully'));
    }

    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        Activity::inLog('administration')
            ->event('client.deleted')
            ->causedBy(Auth::user())
            ->performedOn($client)
            ->withProperties([
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'status' => $client->status->getLabel(),
                ],
            ])
            ->log('activity.client.deleted');

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.deleted_successfully'));
    }

    public function restore(string $clientId): RedirectResponse
    {
        $client = Client::withTrashed()->findOrFail($clientId);
        $client->restore();

        Activity::inLog('administration')
            ->event('client.restored')
            ->causedBy(Auth::user())
            ->performedOn($client)
            ->withProperties([
                'client' => [
                    'id' => $client->id,
                    'name' => $client->name,
                    'status' => $client->status->getLabel(),
                ],
            ])
            ->log('activity.client.restored');

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.restored_successfully'));
    }

    public function addUserToClient(StoreClientUserRequest $request, Client $client): RedirectResponse
    {
        $this->clientUserService->addUserToClient(
            $client,
            $request->user_id,
            $request->role_id
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.user_added_to_client'));
    }

    public function storeNewUserForClient(StoreClientNewUserRequest $request, Client $client): RedirectResponse
    {
        $this->clientUserService->createUserForClient(
            $client,
            $request->name,
            $request->email,
            $request->password,
            ActivityStatus::from($request->status),
            $request->role_id
        );

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.user_created_and_added'));
    }

    public function updateUserRole(UpdateUserRoleRequest $request, Client $client, User $user): RedirectResponse
    {
        if (! $client->users()->where('users.id', $user->id)->exists()) {
            abort(404);
        }

        $this->clientUserService->updateUserRole($user, $request->role_id);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.user_role_updated'));
    }

    public function forceDelete(string $clientId): RedirectResponse
    {
        $client = Client::withTrashed()->findOrFail($clientId);
        $client->forceDelete();

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.permanently_deleted'));
    }

    public function bulkDelete(BulkDeleteClientsRequest $request): RedirectResponse
    {
        $count = Client::whereIn('id', $request->ids)->delete();

        Activity::inLog('administration')
            ->event('clients.bulk_deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'client_ids' => $request->ids,
            ])
            ->log('activity.client.bulk_deleted');

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.bulk_deleted', ['count' => $count]));
    }

    public function bulkRestore(BulkRestoreClientsRequest $request): RedirectResponse
    {
        $count = Client::onlyTrashed()->whereIn('id', $request->ids)->restore();

        Activity::inLog('administration')
            ->event('clients.bulk_restored')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'client_ids' => $request->ids,
            ])
            ->log('activity.client.bulk_restored');

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.bulk_restored', ['count' => $count]));
    }

    public function removeUserFromClient(Client $client, User $user): RedirectResponse
    {
        if (! $client->users()->where('users.id', $user->id)->exists()) {
            abort(404);
        }

        $this->clientUserService->removeUserFromClient($client, $user);

        Activity::inLog('administration')
            ->event('client.user_removed')
            ->causedBy(Auth::user())
            ->performedOn($client)
            ->withProperties([
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
            ])
            ->log('activity.client.user_removed');

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.user_removed'));
    }
}
