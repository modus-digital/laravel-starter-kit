<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\RBAC\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Facades\Activity;

final class ImpersonationController extends Controller
{
    public function start(Request $request, User $targetUser): RedirectResponse
    {
        /** @var User $currentUser */
        $currentUser = $request->user();

        session()->put('impersonation', [
            'is_impersonating' => true,
            'original_user_id' => $currentUser->id,
            'return_url' => url()->previous(),
            'can_bypass_2fa' => true,
        ]);

        Auth::loginUsingId($targetUser->id);

        Activity::inLog('impersonation')
            ->event('impersonate.start')
            ->performedOn($targetUser)
            ->causedBy($currentUser)
            ->withProperties([
                'target' => $targetUser->name,
                'user' => [
                    'id' => $targetUser->id,
                    'name' => $targetUser->name,
                    'email' => $targetUser->email,
                    'status' => $targetUser->status->getLabel(),
                    'roles' => $targetUser->roles->first()?->name
                        ? (Role::tryFrom($targetUser->roles->first()->name)?->getLabel() ?? str($targetUser->roles->first()->name)->headline()->toString())
                        : null,
                ],
            ])
            ->log('activity.impersonate.start');

        return redirect()->route('dashboard');
    }

    public function leave(Request $request): RedirectResponse
    {
        /** @var array<string, mixed>|null $impersonationData */
        $impersonationData = session()->get('impersonation');
        $impersonation = collect($impersonationData);

        /** @var User|null $currentUser */
        $currentUser = $request->user();

        /** @var User|null $originalUser */
        $originalUser = User::find($impersonation->get('original_user_id'));

        if (! $impersonation->has('is_impersonating')) {
            return redirect()->to(path: route('login'));
        }
        if (! $currentUser || ! $originalUser) {
            return redirect()->to(path: route('login'));
        }

        Auth::loginUsingId($originalUser->id);

        session()->forget('impersonation');

        Activity::inLog('impersonation')
            ->event('impersonate.leave')
            ->performedOn($currentUser)
            ->causedBy($originalUser)
            ->withProperties([
                'target' => $currentUser->name,
            ])
            ->log('activity.impersonate.leave');

        return redirect()->route('admin.users.index');
    }
}
