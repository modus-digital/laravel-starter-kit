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
        $url = $fileStorage->upload(
            file: $request->file('image'),
            storagePath: 'images',
            public: true
        );

        return response()->json([
            'url' => $url,
        ], 200);
    }
}
