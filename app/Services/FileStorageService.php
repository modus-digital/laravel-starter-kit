<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class FileStorageService
{
    public function upload(UploadedFile $file, string $storagePath, bool $public = false, ?string $fileName = null): string
    {
        $disk = $this->getDisk();

        $path = $disk->putFileAs(
            path: $storagePath,
            file: $file,
            name: $fileName ?? $this->generateFileName($file)
        );

        if ($public) {
            $disk->setVisibility(path: $path, visibility: 'public');
        }

        // Return the full URL - this is what gets stored in DB
        return $disk->url($path);
    }

    public function exists(string $storedValue): bool
    {
        $path = $this->extractPathFromUrl($storedValue);

        if (! $path) {
            return false;
        }

        return $this->getDisk()->exists($path);
    }

    public function delete(string $url): bool
    {
        $path = $this->extractPathFromUrl($url);

        if (! $path) {
            return false;
        }

        $disk = $this->getDisk();

        // Attempt to delete - returns true if deleted, false if file didn't exist
        return $disk->delete($path);
    }

    public function getDisk(): \Illuminate\Contracts\Filesystem\Filesystem
    {
        $s3Enabled = setting('integrations.s3.enabled', false);

        if (! $s3Enabled) {
            return Storage::disk('local');
        }

        // Build S3 disk on-demand using settings from database
        $config = [
            'driver' => 's3',
            'key' => setting('integrations.s3.key'),
            'secret' => $this->decryptSetting('integrations.s3.secret'),
            'region' => setting('integrations.s3.region'),
            'bucket' => setting('integrations.s3.bucket'),
            'url' => setting('integrations.s3.url'),
            'endpoint' => setting('integrations.s3.endpoint'),
            'use_path_style_endpoint' => setting('integrations.s3.use_path_style_endpoint', false),
        ];

        return Storage::build($config);
    }

    private function generateFileName(UploadedFile $file): string
    {
        return Str::uuid7().'.'.$file->getClientOriginalExtension();
    }

    private function decryptSetting(string $key): ?string
    {
        $value = setting($key);

        if (! $value) {
            return null;
        }

        return rescue(
            callback: fn (): mixed => decrypt(value: $value),
            rescue: $value,
        );
    }

    private function extractPathFromUrl(string $url): ?string
    {
        // Handle relative paths (like /storage/uploads/file.jpg)
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            // It's a relative path, remove leading slash and storage prefix
            $path = mb_ltrim($url, '/');
            $path = preg_replace('#^storage/#', '', $path);

            return $path ?: null;
        }

        // Handle full URLs
        $parsedUrl = parse_url($url);
        $path = mb_ltrim($parsedUrl['path'] ?? '', '/');

        // Remove /storage prefix if present (Laravel adds this to local disk URLs)
        $path = preg_replace('#^storage/#', '', $path);

        return $path ?: null;
    }
}
