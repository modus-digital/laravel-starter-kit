<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class StopImpersonating extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $userId = session()->pull('impersonating_user_id');

        if ($userId) {
            Auth::loginUsingId($userId);
        }

        session()->put('can_bypass_two_factor', false);

        return redirect()->to(session()->pull('impersonating_return_url'));
    }
}
