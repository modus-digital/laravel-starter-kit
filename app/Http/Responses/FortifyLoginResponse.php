<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\RBAC\Permission;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as Responsable;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

final class FortifyLoginResponse implements Responsable
{
    public function toResponse($request): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if ($user) {
            $clientId = $user->clients()
                ->orderBy('name')
                ->value('clients.id');

            if ($clientId) {
                $request->session()->put('current_client_id', $clientId);
            }
        }

        if ($user) {
            Activity::inLog('authentication')
                ->event('auth.login')
                ->causedBy($user)
                ->withProperties([
                    'guard' => $request->guard,
                    'remember' => $request->remember,
                ])
                ->log('');
        }

        if ($user && $user->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL)) {
            return Inertia::location(url: route('filament.control.pages.dashboard'));
        }

        return Inertia::location(url: route('dashboard'));
    }
}
