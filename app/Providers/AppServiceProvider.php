<?php

namespace App\Providers;

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Override;
use Spatie\CpuLoadHealthCheck\CpuLoadCheck;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DatabaseSizeCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Facades\Health;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    #[Override]
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Dangerous commands are prohibited in production
        DB::prohibitDestructiveCommands(prohibit: app()->isProduction());

        // Models
        Model::unguard(); // Disable model guards
        Model::shouldBeStrict(); // Throw exceptions when accessing non-existent properties
        Model::preventLazyLoading(); // Throw exceptions when lazy loading is used
        Model::automaticallyEagerLoadRelationships(); // Resolve n+1 issues

        // Auto HTTPS Scheme + Vite prefetch strategy
        URL::forceScheme('https');
        Vite::usePrefetchStrategy('aggressive');
        Health::checks([

            // CPU load check
            CpuLoadCheck::new()
                ->failWhenLoadIsHigherInTheLastMinute(2.5)
                ->failWhenLoadIsHigherInTheLast5Minutes(1.5)
                ->failWhenLoadIsHigherInTheLast15Minutes(0.5),

            // Check cache health
            CacheCheck::new(),

            // Cached config, routes and events check
            OptimizedAppCheck::new(),

            // DB connection check
            DatabaseCheck::new(),

            // DB connection count check
            DatabaseConnectionCountCheck::new()
                ->warnWhenMoreConnectionsThan(50)
                ->failWhenMoreConnectionsThan(100),

            // DB size check
            DatabaseSizeCheck::new()
                ->failWhenSizeAboveGb(errorThresholdGb: 1.0),

            // Check if scheduled tasks are running
            ScheduleCheck::new(),

        ]);

        Gate::define('use-translation-manager', fn (?User $user): bool =>
            // Your authorization logic
            $user instanceof User && $user->hasPermissionTo(Permission::HAS_ACCESS_TO_ADMIN_PANEL));
    }
}
