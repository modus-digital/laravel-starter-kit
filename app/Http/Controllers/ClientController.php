<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Client\UpdateClientSettingsRequest;
use App\Models\Modules\Clients\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

final class ClientController extends Controller
{
    use AuthorizesRequests;

    public function show(): InertiaResponse
    {
        $client = $this->getCurrentClient();

        abort_if(! $client, 404, 'No client selected');

        $this->authorize('view', $client);

        return Inertia::render('modules/app/clients/show', [
            'client' => $client,
        ]);
    }

    public function users(): InertiaResponse
    {
        $client = $this->getCurrentClient();

        abort_if(! $client, 404, 'No client selected');

        $this->authorize('view', $client);

        $users = $client->users()
            ->with(['roles'])
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => $user->status->value,
                'role' => $user->roles->first()?->name,
            ]);

        return Inertia::render('modules/app/clients/users', [
            'client' => $client,
            'users' => $users,
        ]);
    }

    public function settings(): InertiaResponse
    {
        $client = $this->getCurrentClient();

        abort_if(! $client, 404, 'No client selected');

        $this->authorize('view', $client);

        return Inertia::render('modules/app/clients/settings', [
            'client' => $client,
        ]);
    }

    public function updateSettings(UpdateClientSettingsRequest $request): RedirectResponse
    {
        $client = $this->getCurrentClient();

        abort_if(! $client, 404, 'No client selected');

        $client->update([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'country' => $request->country,
        ]);

        return redirect()->route('clients.manage.settings')
            ->with('success', __('clients.settings_updated'));
    }

    public function activities(): InertiaResponse
    {
        $client = $this->getCurrentClient();

        abort_if(! $client, 404, 'No client selected');

        $this->authorize('view', $client);

        $activities = $client->activities()
            ->latest()
            ->paginate(15);

        return Inertia::render('modules/app/clients/activities', [
            'client' => $client,
            'activities' => $activities,
        ]);
    }

    protected function getCurrentClient(): ?Client
    {
        $clientId = session('current_client_id');

        if (! $clientId) {
            return null;
        }

        return Client::find($clientId);
    }
}
