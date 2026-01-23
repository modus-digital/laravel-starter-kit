<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ActivityCollection;
use App\Http\Resources\UserCollection;
use App\Models\Modules\Clients\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Facades\Activity;

final class ClientController extends Controller
{
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

        $clients = $query->get();

        return Inertia::render('admin/clients/index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status', 'with_trashed', 'only_trashed', 'sort_by', 'sort_direction']),
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/clients/create', [
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
        // Load users relation
        $users = $client->users()
            ->with(['roles'])
            ->latest()
            ->paginate(10);

        // Load activities
        $activities = $client->activities()
            ->latest()
            ->paginate(10);

        return Inertia::render('admin/clients/show', [
            'client' => $client,
            'users' => new UserCollection($users),
            'activities' => new ActivityCollection($activities),
        ]);
    }

    public function edit(Client $client): Response
    {
        return Inertia::render('admin/clients/edit', [
            'client' => $client,
            'statuses' => ActivityStatus::options(),
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('contact_name')) {
            $updateData['contact_name'] = $request->contact_name;
        }

        if ($request->has('contact_email')) {
            $updateData['contact_email'] = $request->contact_email;
        }

        if ($request->has('contact_phone')) {
            $updateData['contact_phone'] = $request->contact_phone;
        }

        if ($request->has('address')) {
            $updateData['address'] = $request->address;
        }

        if ($request->has('postal_code')) {
            $updateData['postal_code'] = $request->postal_code;
        }

        if ($request->has('city')) {
            $updateData['city'] = $request->city;
        }

        if ($request->has('country')) {
            $updateData['country'] = $request->country;
        }

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        $client->update($updateData);

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
            ->log('');

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
            ->log('');

        return redirect()->route('admin.clients.show', $client)
            ->with('success', __('admin.clients.restored_successfully'));
    }

    public function forceDelete(string $clientId): RedirectResponse
    {
        $client = Client::withTrashed()->findOrFail($clientId);
        $client->forceDelete();

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.permanently_deleted'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string', 'exists:clients,id'],
        ]);

        $count = Client::whereIn('id', $request->ids)->delete();

        Activity::inLog('administration')
            ->event('clients.bulk_deleted')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'client_ids' => $request->ids,
            ])
            ->log('');

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.bulk_deleted', ['count' => $count]));
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'string'],
        ]);

        $count = Client::onlyTrashed()->whereIn('id', $request->ids)->restore();

        Activity::inLog('administration')
            ->event('clients.bulk_restored')
            ->causedBy(Auth::user())
            ->withProperties([
                'count' => $count,
                'client_ids' => $request->ids,
            ])
            ->log('');

        return redirect()->route('admin.clients.index')
            ->with('success', __('admin.clients.bulk_restored', ['count' => $count]));
    }
}
