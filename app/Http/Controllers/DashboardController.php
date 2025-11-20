<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\{Inertia, Response};

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        return Inertia::render(
            component: 'dashboard',
        );
    }
}
