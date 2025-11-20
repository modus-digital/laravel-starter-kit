<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Activity;

class LeaveImpersonationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $impersonation = collect(session()->get('impersonation'));
        $currentUser = Auth::user();
        $originalUser = User::find($impersonation->get('original_user_id'));

        if (! $impersonation->has('is_impersonating')) return redirect()->to(path: route('login'));
        if (! $currentUser || ! $originalUser) return redirect()->to(path: route('login'));

        Auth::loginUsingId($originalUser->id);

        session()->forget('impersonation');

        Activity::inLog('impersonation')
            ->event('impersonate.leave')
            ->performedOn($currentUser)
            ->causedBy($originalUser)
            ->withProperties([
                'issuer' => $originalUser->name,
                'target' => $currentUser->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log(description: 'User impersonation ended');
            
        return redirect(
            to: $impersonation->get('return_url') ?? route('dashboard')
        );
    }
}
