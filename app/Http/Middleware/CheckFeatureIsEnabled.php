<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureIsEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request):Response $next
     * @param  string  $featureKey  The dot-notation key for the feature flag (e.g., 'auth.register')
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        // Check if the feature is enabled
        if (! feature('app.features.' . $featureKey)->enabled()) {
            abort(
                code: 404,
                message: 'Feature is not enabled'
            );
        }

        return $next($request);
    }
}
