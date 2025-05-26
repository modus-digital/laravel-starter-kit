<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SentryController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $environment = app()->environment();
        $sentry = [
            'dsn' => config('sentry.dsn'),
            'traces_sample_rate' => config('sentry.traces_sample_rate'),
            'profiles_sample_rate' => config('sentry.profiles_sample_rate'),
        ];

        return response()->json([
            'environment' => $environment,
            'sentry' => $sentry,
            'enabled' => ! app()->isLocal(),
        ]);
    }
}
