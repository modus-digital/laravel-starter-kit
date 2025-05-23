<?php

namespace App\Providers;

use App\Enums\RBAC\Permission;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
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
        URL::forceScheme(scheme: 'https');
        Vite::usePrefetchStrategy(strategy: 'aggressive');

        // Use immutable dates by default
        Date::use(handler: CarbonImmutable::class);

        // Health checks
        Health::checks([
            CpuLoadCheck::new()->failWhenLoadIsHigherInTheLastMinute(2.5)->failWhenLoadIsHigherInTheLast5Minutes(1.5)->failWhenLoadIsHigherInTheLast15Minutes(0.5),
            CacheCheck::new(),
            OptimizedAppCheck::new(),
            DatabaseCheck::new(),
            DatabaseConnectionCountCheck::new()->warnWhenMoreConnectionsThan(50)->failWhenMoreConnectionsThan(100),
            DatabaseSizeCheck::new()->failWhenSizeAboveGb(errorThresholdGb: 1.0),
            ScheduleCheck::new(),

        ]);

        // Define the gate for the translation manager
        Gate::define('use-translation-manager', fn(?User $user): bool => $user instanceof User && $user->hasPermissionTo(permission:Permission::HAS_ACCESS_TO_ADMIN_PANEL));
    }
}
