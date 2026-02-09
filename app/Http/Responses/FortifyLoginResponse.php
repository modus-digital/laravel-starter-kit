<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Enums\RBAC\Permission;
use App\Events\Security\NewDeviceLogin;
use Illuminate\Support\Facades\Event;
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
                ->log('activity.auth.login');

            // Check if this is a new device/login
            $sessionKey = 'last_login_device_'.$user->id;
            $lastDevice = $request->session()->get($sessionKey);
            $currentDevice = md5($request->ip().$request->userAgent());

            if ($lastDevice !== null && $lastDevice !== $currentDevice) {
                // New device detected
                Event::dispatch(new NewDeviceLogin(
                    user: $user,
                    ipAddress: $request->ip(),
                    userAgent: $request->userAgent(), // Could be enhanced with geolocation service
                ));
            }

            // Store current device fingerprint
            $request->session()->put($sessionKey, $currentDevice);
        }

        if ($user && $user->hasPermissionTo(Permission::AccessControlPanel)) {
            return Inertia::location(url: route('admin.index'));
        }

        return Inertia::location(url: route('dashboard'));
    }
}
