<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

final class RedirectToApplicationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (! $user) {
            return to_route(route: 'login');
        }

        // Redirect to client portal if clients module is enabled
        if (config('modules.clients.enabled')) {
            return to_route(route: 'clients.show');
        }

        return to_route(route: 'dashboard');
    }
}
