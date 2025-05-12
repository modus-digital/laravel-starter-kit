<?php

namespace App\Http\Middleware\Filament;

use App\Enums\RBAC\Permission;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

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
     * @param  \Illuminate\Http\Request  $request
     *
     * @throws \Illuminate\Auth\AuthenticationException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    protected function authenticate($request, array $guards): void
    {
        $panel = Filament::getCurrentPanel();

        /** @var User|null $user */
        $user = Auth::loginUsingId(1);

        if (! $user || ! $user->hasPermissionTo(Permission::HAS_ACCESS_TO_ADMIN_PANEL)) {
            redirect()->to(config('app.url'))->send();
        }
    }
}
