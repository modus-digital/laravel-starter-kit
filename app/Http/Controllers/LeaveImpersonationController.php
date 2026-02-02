<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Spatie\Activitylog\Facades\Activity;
use Symfony\Component\HttpFoundation\Response;

final class LeaveImpersonationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        /** @var array<string, mixed>|null $impersonationData */
        $impersonationData = session()->get('impersonation');
        $impersonation = collect($impersonationData);

        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        /** @var User|null $originalUser */
        $originalUser = User::find($impersonation->get('original_user_id'));

        if (! $impersonation->has('is_impersonating')) {
            return redirect()->to(path: route('login'));
        }
        if (! $currentUser || ! $originalUser) {
            return redirect()->to(path: route('login'));
        }

        Auth::loginUsingId($originalUser->id);

        /**
         * ! ADMIN PANEL ONLY
         * ------------------------------------------------------------
         * Update the password hash in session for AuthenticateSession middleware
         * This prevents the middleware from logging out the impersonated user
         */
        if ($originalUser->hasPermissionTo(Permission::ACCESS_CONTROL_PANEL)) {
            session()->put('password_hash_'.Auth::getDefaultDriver(), $originalUser->getAuthPassword());
        }

        session()->forget('impersonation');

        Activity::inLog('impersonation')
            ->event('impersonate.leave')
            ->performedOn($currentUser)
            ->causedBy($originalUser)
            ->withProperties([
                'target' => $currentUser->name,
            ])
            ->log('activity.impersonate.leave');

        return Inertia::location(url: route('dashboard'));
    }
}
