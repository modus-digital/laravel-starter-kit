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
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('User registered successfully');

        if ($user && $user->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL)) {
            return Inertia::location(url: route('filament.control.pages.dashboard'));
        }

        return Inertia::location(url: route('dashboard'));
    }
}
