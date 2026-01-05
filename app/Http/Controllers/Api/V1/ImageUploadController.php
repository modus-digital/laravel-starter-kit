<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImageUploadRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

final class ImageUploadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ImageUploadRequest $request): JsonResponse
    {
        $image = $request->file('image');

        // Generate a unique filename
        $filename = Str::uuid().'.'.$image->getClientOriginalExtension();

        // Store the image in the public disk
        $path = $image->storeAs('images', $filename, 'public');

        // Generate the absolute URL for the uploaded image
        $url = url('storage/'.$path);

        return response()->json([
            'url' => $url,
            'path' => $path,
            'filename' => $filename,
        ], 200);
    }
}
