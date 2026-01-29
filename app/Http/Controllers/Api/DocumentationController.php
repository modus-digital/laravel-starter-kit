<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\Yaml\Yaml;

final class DocumentationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render(component: 'core/api-docs');
    }

    public function specFile(): JsonResponse
    {
        $file = Storage::disk('local')->path(config('modules.api.documentation_path'));

        if (! file_exists($file)) {
            abort(404);
        }

        // Convert YAML to JSON if needed
        $yaml = file_get_contents($file);
        if ($yaml === false) {
            abort(500, 'Unable to read documentation file');
        }
        $data = Yaml::parse($yaml);

        return response()->json($data);
    }
}
