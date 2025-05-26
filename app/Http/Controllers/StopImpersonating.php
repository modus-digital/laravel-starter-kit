<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StopImpersonating extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $userId = session()->pull('impersonating_user_id');

        if ($userId) {
            Auth::loginUsingId($userId);
        }

        session()->put('can_bypass_two_factor', false);

        return redirect()->to(session()->pull('impersonating_return_url'));
    }
}
