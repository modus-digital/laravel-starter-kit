<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RBAC\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL)) {
            return redirect()->route('filament.control.pages.dashboard');
        }

        return redirect()->route('app.dashboard');
    }
}
