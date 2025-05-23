<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureIsEnabled
{
    /**
     * This middleware checks if a feature is enabled and aborts the request if it is not.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @param  string  $featureKey  The dot-notation key for the feature flag (e.g., 'auth.register')
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $featureKey): Response
    {
        // Check if the feature is enabled
        if (! feature('features.' . $featureKey)->enabled()) {
            abort(
                code: 404,
                message: 'Feature is not enabled'
            );
        }

        return $next($request);
    }
}
