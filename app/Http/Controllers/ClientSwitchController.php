<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Modules\Clients\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ClientSwitchController extends Controller
{
    /**
     * Handle switching to a different client.
     */
    public function __invoke(Request $request, Client $client): RedirectResponse
    {
        $user = $request->user();

        // Verify the user belongs to this client
        if (! $user->clients()->whereKey($client->id)->exists()) {
            abort(403, 'You do not have access to this client.');
        }

        // Store the new client ID in the session
        $request->session()->put('current_client_id', $client->id);

        return redirect()->back();
    }
}
