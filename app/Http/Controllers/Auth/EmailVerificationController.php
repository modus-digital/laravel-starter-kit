<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class EmailVerificationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if (! $request->user()) {
            return redirect()->intended('/');
        }

        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended('/');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return to_route('application.dashboard');
    }
}
