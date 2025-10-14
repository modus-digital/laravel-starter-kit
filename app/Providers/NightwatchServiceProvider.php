<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;
use Laravel\Nightwatch\Records\CacheEvent;
use Laravel\Nightwatch\Records\OutgoingRequest;
use Laravel\Nightwatch\Records\Query;
use Laravel\Nightwatch\Records\QueuedJob;

final class NightwatchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureSampling();
        $this->rejectCacheEvents();
        $this->rejectQueries();
        $this->rejectQueuedJobs();
        $this->rejectOutgoingRequests();
    }

    /**
     * Configure sampling rates based on environment.
     * Production: 10% - Minimal overhead, captures representative sample
     * Staging: 50% - Balanced for testing and performance analysis
     * Development: 100% - Full visibility for debugging
     *
     * Note: You can also set these via environment variables:
     * - NIGHTWATCH_REQUEST_SAMPLE_RATE
     * - NIGHTWATCH_COMMAND_SAMPLE_RATE
     * - NIGHTWATCH_EXCEPTION_SAMPLE_RATE
     */
    protected function configureSampling(): void
    {
        $samplingRates = [
            'production' => 0.1,
            'prod' => 0.1,
            'staging' => 0.5,
            'acceptance' => 0.5,
            'development' => 1.0,
            'local' => 1.0,
            'dev' => 1.0,
        ];

        $environment = app()->environment();
        $rate = $samplingRates[$environment] ?? 0.1;

        Nightwatch::sample(rate: $rate);
    }

    /**
     * Reject noisy cache events that don't provide actionable insights.
     *
     * Alternatively, you can disable all cache event tracking via:
     * NIGHTWATCH_IGNORE_CACHE_EVENTS=true
     */
    protected function rejectCacheEvents(): void
    {
        Nightwatch::rejectCacheEvents(
            callback: fn (CacheEvent $event): bool => in_array(
                needle: $event->key,
                haystack: [
                    // Permission caching (high frequency, low value)
                    'spatie.permission.cache',

                    // Framework internal cache keys
                    'illuminate:foundation:down',
                    'illuminate:queue:restart',
                    'illuminate:schedule:interrupt',

                    // Livewire component cache
                    'livewire',

                    // Session cache
                    'session',
                ],
                strict: false
            ) || str_starts_with(haystack: $event->key, needle: 'laravel_cache')
                || str_starts_with(haystack: $event->key, needle: 'filament:')
                || str_starts_with(haystack: $event->key, needle: 'livewire:')
        );
    }

    /**
     * Reject queries to infrastructure tables and high-frequency operations.
     *
     * Alternatively, you can disable all query tracking via:
     * NIGHTWATCH_IGNORE_QUERIES=true
     */
    protected function rejectQueries(): void
    {
        Nightwatch::rejectQueries(function (Query $query): bool {
            $sql = $query->sql;

            // Queue table operations (very high frequency)
            if (str_contains(haystack: $sql, needle: 'from "jobs"')
                || str_contains(haystack: $sql, needle: 'into "jobs"')
                || str_contains(haystack: $sql, needle: 'from "failed_jobs"')) {
                return true;
            }

            // Cache table operations (high frequency, low value)
            if (str_contains(haystack: $sql, needle: 'from "cache"')
                || str_contains(haystack: $sql, needle: 'into "cache"')
                || str_contains(haystack: $sql, needle: 'from "cache_locks"')) {
                return true;
            }

            // Session table operations
            if (str_contains(haystack: $sql, needle: 'from "sessions"')
                || str_contains(haystack: $sql, needle: 'into "sessions"')) {
                return true;
            }

            // Migration and system queries
            if (str_contains(haystack: $sql, needle: 'from "migrations"')
                || str_contains(haystack: $sql, needle: 'information_schema')) {
                return true;
            }

            // Telescope or monitoring table queries
            if (str_contains(haystack: $sql, needle: 'telescope_')
                || str_contains(haystack: $sql, needle: 'pulse_')) {
                return true;
            }

            // Very fast queries (< 10ms) in production
            if (app()->isProduction() && $query->duration < 10) {
                return true;
            }

            return false;
        });
    }

    /**
     * Reject noisy queued jobs that don't need monitoring.
     * Most queue jobs are background tasks that don't need detailed tracking.
     */
    protected function rejectQueuedJobs(): void
    {
        Nightwatch::rejectQueuedJobs(function (QueuedJob $job): bool {
            // In production, only track failed jobs or jobs that take > 30 seconds
            if (app()->isProduction()) {
                return $job->duration < 30000; // duration is in milliseconds
            }

            // In other environments, track jobs that take > 10 seconds
            return $job->duration < 10000;
        });
    }

    /**
     * Reject outgoing HTTP requests that are not critical to monitor.
     *
     * Alternatively, you can disable all outgoing request tracking via:
     * NIGHTWATCH_IGNORE_OUTGOING_REQUESTS=true
     */
    protected function rejectOutgoingRequests(): void
    {
        Nightwatch::rejectOutgoingRequests(function (OutgoingRequest $request): bool {
            // Reject health check pings to external services
            if (str_contains(haystack: $request->url, needle: '/health')
                || str_contains(haystack: $request->url, needle: '/ping')
                || str_contains(haystack: $request->url, needle: '/status')) {
                return true;
            }

            // Reject successful requests that completed quickly (< 100ms) in production
            if (app()->isProduction()
                && $request->duration < 100
                && $request->statusCode >= 200
                && $request->statusCode < 300) {
                return true;
            }

            return false;
        });
    }
}
