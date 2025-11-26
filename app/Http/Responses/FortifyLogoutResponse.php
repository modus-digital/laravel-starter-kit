<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Inertia\Inertia;
use Laravel\Fortify\Contracts\LogoutResponse as Responsable;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

final class FortifyLogoutResponse implements Responsable
{
    public function toResponse($request): Response
    {
        Activity::inLog('authentication')
            ->event('auth.logout')
            ->causedBy(auth()->user())
            ->log('');

        return Inertia::location(url: route('login'));
    }
}
