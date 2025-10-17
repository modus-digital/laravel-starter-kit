<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\HookManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HookManager::class, static fn (): HookManager => new HookManager());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Blade directive to render a hook location
        Blade::directive('renderHook', static function (string $expression): string {
            return "<?php echo app(\\App\\Services\\HookManager::class)->renderHook($expression); ?>";
        });

        // Blade directive to register content at a hook location
        Blade::directive('hook', static function (string $expression): string {
            return "<?php app(\\App\\Services\\HookManager::class)->register($expression, function() { ob_start(); ?>";
        });

        Blade::directive('endhook', static function (): string {
            return '<?php return new \\Illuminate\\Support\\HtmlString(ob_get_clean()); }); ?>';
        });
    }
}
