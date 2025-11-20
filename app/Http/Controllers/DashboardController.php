<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RBAC\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        if (Auth::user()->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL)) {
            return redirect()->to(path: route('filament.control.pages.dashboard', absolute: false));
        }

        return Inertia::render(
            component: 'dashboard',
        );
    }
}
