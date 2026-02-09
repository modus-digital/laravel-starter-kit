<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use App\Services\FileStorageService;
use Illuminate\Http\JsonResponse;

final class ImageUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ImageUploadRequest $request, FileStorageService $fileStorage): JsonResponse
    {
        $file = $request->file('image');
        $url = $fileStorage->upload(
            file: $file,
            storagePath: 'images',
            public: true
        );

        // Extract path and filename from URL
        $path = parse_url($url, PHP_URL_PATH);
        if ($path && str_starts_with($path, '/')) {
            $path = mb_ltrim($path, '/');
        }
        if ($path && str_starts_with($path, 'storage/')) {
            $path = mb_substr($path, mb_strlen('storage/'));
        }

        // Extract the actual stored filename from path
        $filename = basename(is_string($path) ? $path : '');

        return response()->json([
            'url' => $url,
            'path' => $path ?? 'images/'.$filename,
            'filename' => $filename,
        ], 200);
    }
}
