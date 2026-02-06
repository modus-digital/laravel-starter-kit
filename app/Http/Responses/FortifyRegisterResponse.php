<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\RBAC\Permission;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\RegisterResponse as Responsable;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

final class FortifyRegisterResponse implements Responsable
{
    public function toResponse($request): Response
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        Activity::inLog('authentication')
            ->event('auth.register')
            ->causedBy($user)
            ->log('activity.auth.register');

        if ($user && $user->hasPermissionTo(Permission::AccessControlPanel)) {
            return Inertia::location(url: route('filament.control.pages.dashboard'));
        }

        return Inertia::location(url: route('dashboard'));
    }
}
