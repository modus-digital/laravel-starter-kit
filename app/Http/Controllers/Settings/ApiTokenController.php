<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\RBAC\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class ApiTokenController extends Controller
{
    /**
     * Show the API tokens management page.
     */
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $tokens = $user->tokens()->get()->map(fn ($token): array => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at?->toDateTimeString(),
            'created_at' => $token->created_at->toDateTimeString(),
        ]);

        // Get all available permissions
        $allPermissions = collect(Permission::cases())->map(fn ($permission): array => [
            'value' => $permission->value,
            'label' => $permission->getLabel(),
            'description' => $permission->getDescription(),
        ]);

        // Get user's permissions
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        return Inertia::render('settings/api-tokens', [
            'tokens' => $tokens,
            'availablePermissions' => $allPermissions,
            'userPermissions' => $userPermissions,
        ]);
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'permissions' => ['required', 'array'],
            'permissions.*' => ['string'],
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();

        // Get user's permissions
        $userPermissions = $user->getAllPermissions()->pluck('name')->toArray();

        // Filter requested permissions to only include those the user has
        $validPermissions = array_intersect($validated['permissions'], $userPermissions);

        if ($validPermissions === []) {
            throw ValidationException::withMessages([
                'permissions' => 'You must select at least one permission that you have access to.',
            ]);
        }

        // Create the token
        $token = $user->createToken($validated['name'], $validPermissions);

        return back()
            ->with('data', [
                'token' => $token->plainTextToken,
                'tokenName' => $validated['name'],
            ]);
    }

    /**
     * Delete an API token.
     */
    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $token = $user->tokens()->where('id', $tokenId)->first();

        if (! $token) {
            abort(404);
        }

        $token->delete();

        return to_route('api-tokens.index')
            ->with('status', 'token-deleted');
    }
}
