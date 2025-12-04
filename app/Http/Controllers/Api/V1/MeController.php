<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final class MeController extends Controller
{
    /**
     * Get current authenticated user information
     *
     * Returns detailed information about the currently authenticated user including their profile,
     * roles, permissions, and current access token details.
     *
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "phone": "+1234567890",
     *     "status": "active",
     *     "email_verified_at": "2024-01-01T00:00:00Z",
     *     "provider": "local",
     *     "role": "admin",
     *     "created_at": "2024-01-01T00:00:00Z",
     *     "updated_at": "2024-01-01T00:00:00Z",
     *     "deleted_at": null
     *   },
     *   "token": {
     *     "id": 1,
     *     "name": "API Token",
     *     "abilities": ["*"],
     *     "created_at": "2024-01-01T00:00:00Z",
     *     "last_used_at": "2024-01-01T00:00:00Z",
     *     "expires_at": null,
     *     "is_expired": false
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $token = $request->bearerToken();

        // Get the current token details
        $accessToken = PersonalAccessToken::findToken($token);

        $tokenInfo = null;
        if ($accessToken) {
            $tokenInfo = [
                'id' => $accessToken->id,
                'name' => $accessToken->name,
                'abilities' => $accessToken->abilities,
                'created_at' => $accessToken->created_at,
                'last_used_at' => $accessToken->last_used_at,
                'expires_at' => $accessToken->expires_at,
                'is_expired' => $accessToken->cant('*'),
            ];
        }

        return response()->json([
            'user' => new UserResource($user->load(['roles'])),
            'token' => $tokenInfo,
        ]);
    }
}
