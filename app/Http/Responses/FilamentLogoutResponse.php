<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Responsable;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

final class FilamentLogoutResponse implements Responsable
{
    public function toResponse($request): Response
    {
        Activity::inLog('authentication')
            ->event('auth.logout')
            ->causedBy($request->user())
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'guard' => $request->guard,
                'remember' => $request->remember,
            ])
            ->log('User logged out successfully');

        return Inertia::location(url: route('login'));
    }
}
