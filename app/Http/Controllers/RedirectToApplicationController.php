<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RBAC\Role;
use Illuminate\Http\Request;

final class RedirectToApplicationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return to_route(route: 'login');
        }
        if ($user->hasAnyRole(roles: [Role::SUPER_ADMIN, Role::ADMIN])) {
            return to_route(route: 'filament.control.pages.dashboard');
        }

        return to_route(route: 'dashboard');
    }
}
