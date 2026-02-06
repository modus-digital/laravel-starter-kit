<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SearchController extends Controller
{
    public function __construct(
        private readonly SearchService $searchService
    ) {}

    /**
     * Handle search requests.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }

        $query = $request->string('q', '')->trim()->toString();
        $limit = $request->integer('limit', 10);

        if (empty($query)) {
            return response()->json([
                'data' => [],
            ]);
        }

        $results = $this->searchService->search($query, $user, $limit);

        return response()->json([
            'data' => $results,
        ]);
    }
}
