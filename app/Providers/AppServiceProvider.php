<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use App\Http\Responses\FortifyLoginResponse;
use App\Http\Responses\FortifyLogoutResponse;
use App\Http\Responses\FortifyRegisterResponse;
use App\Translation\NestedJsonLoader;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as FilamentLogoutResponseContract;
use Illuminate\Support\ServiceProvider;
use Knuckles\Scribe\Scribe;
use Laravel\Fortify\Http\Responses\LoginResponse as FortifyLoginResponseContract;
use Laravel\Fortify\Http\Responses\LogoutResponse as FortifyLogoutResponseContract;
use Laravel\Fortify\Http\Responses\RegisterResponse as FortifyRegisterResponseContract;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->configureCustomLoaders();
        $this->configureResponses();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Disable generating .scribe folder
        $this->configureScribe();
    }

    /**
     * Configure the responses of Filament and Fortify.
     */
    private function configureResponses(): void
    {
        // Filament
        $this->app->singleton(abstract: FilamentLogoutResponseContract::class, concrete: FilamentLogoutResponse::class);

        // Fortify
        $this->app->singleton(abstract: FortifyLoginResponseContract::class, concrete: FortifyLoginResponse::class);
        $this->app->singleton(abstract: FortifyRegisterResponseContract::class, concrete: FortifyRegisterResponse::class);
        $this->app->singleton(abstract: FortifyLogoutResponseContract::class, concrete: FortifyLogoutResponse::class);
    }

    /**
     * Configure the custom loaders for the application.
     */
    private function configureCustomLoaders(): void
    {
        $this->app->extend(
            abstract: 'translation.loader',
            closure: fn ($loader, $app): NestedJsonLoader => new NestedJsonLoader(
                files: $app['files'],
                path: $app['path.lang']
            )
        );
    }

    /**
     * Configure Scribe documentation generation.
     */
    private function configureScribe(): void
    {
        if (class_exists(Scribe::class)) {
            Scribe::afterGenerating(function (array $paths) {
                $scribeDir = base_path('.scribe');
                if (is_dir($scribeDir)) {
                    // Use PHP's recursive directory removal
                    $this->removeDirectory($scribeDir);
                }

                $docsDir = base_path('resources/views/scribe');
                if (is_dir($docsDir)) {
                    $this->removeDirectory($docsDir);
                }

                $publicDir = base_path('public/vendor/scribe');
                if (is_dir($publicDir)) {
                    $this->removeDirectory($publicDir);
                }
            });
        }
    }

    /**
     * Recursively remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        // Use a more robust approach with error handling
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }

        @rmdir($dir);
    }
}
