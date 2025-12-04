<?php

declare(strict_types=1);

namespace App\Traits;

use Knuckles\Scribe\Scribe;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Trait for configuring Scribe API documentation generation.
 *
 * This trait provides methods to configure Scribe hooks and file processing
 * for generating API documentation with dynamic URL injection.
 */
trait ConfiguresScribeDocumentation
{
    /**
     * Configure Scribe documentation generation.
     */
    private function configureScribe(): void
    {
        if (class_exists(Scribe::class)) {
            Scribe::afterGenerating(function (array $paths): void {
                // Replace [[APP_URL]] placeholders in generated documentation files
                $this->replaceAppUrlInGeneratedFiles($paths);

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
     * Replace [[APP_URL]] placeholders in generated documentation files.
     */
    private function replaceAppUrlInGeneratedFiles(array $paths): void
    {
        $appUrl = config('app.url');

        // Process each generated file path
        foreach ($paths as $path) {
            if (is_string($path) && file_exists($path)) {
                if (is_dir($path)) {
                    // Handle directories (like js, css, images)
                    $this->processDirectory($path, $appUrl);
                } else {
                    // Handle files
                    $this->processFile($path, $appUrl);
                }
            }
        }
    }

    /**
     * Process a directory recursively to replace placeholders in all files.
     */
    private function processDirectory(string $dir, string $appUrl): void
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $this->processFile($file->getRealPath(), $appUrl);
            }
        }
    }

    /**
     * Process a single file to replace [[APP_URL]] placeholders.
     */
    private function processFile(string $filePath, string $appUrl): void
    {
        $content = file_get_contents($filePath);

        if ($content !== false) {
            $updatedContent = str_replace('[[APP_URL]]', $appUrl, $content);

            if ($updatedContent !== $content) {
                file_put_contents($filePath, $updatedContent);
            }
        }
    }

    /**
     * Recursively remove a directory and all its contents.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        // Use a more robust approach with error handling
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
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
