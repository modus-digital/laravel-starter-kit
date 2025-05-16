<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Cjmellor\BrowserSessions\Facades\BrowserSessions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ClearBrowserSessionsController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        BrowserSessions::logoutOtherBrowserSessions();

        return redirect()->back()->with('bs-cleared', 'Browser sessions cleared.');
    }
}
