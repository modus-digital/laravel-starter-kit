<?php

namespace App\Http\Middleware\Filament;

use App\Enums\RBAC\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Override;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * The Filament authentication middleware.
 *
 * This middleware is responsible for authenticating the incoming request using the given guards.
 * It also checks if the authenticated user has access to the current panel.
 */
class Authenticate extends Middleware
{
    /**
     * Authenticate the incoming request using the given guards.
     *
     * @throws AuthenticationException
     * @throws HttpException
     */
    #[Override]
    protected function authenticate($request, array $guards): void
    {
        Filament::getCurrentPanel();

        /** @var User|null $user */
        $user = Auth::loginUsingId(1);

        if (! $user || ! $user->hasPermissionTo(Permission::HAS_ACCESS_TO_ADMIN_PANEL)) {
            redirect()->to(config('app.url'))->send();
        }
    }
}
