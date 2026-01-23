<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return Inertia::render(
            component: 'dashboard',
        );
    }
}
