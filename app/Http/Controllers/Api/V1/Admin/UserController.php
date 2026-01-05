<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\RBAC\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class UserController extends Controller
{
    /**
     * List all users
     *
     * Get a paginated list of users with optional filtering and search capabilities.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Default: 15. Example: 10
     * @queryParam status Filter by user status. Example: active
     * @queryParam search Search in name and email fields. Example: john
     * @queryParam with_trashed Include soft deleted users. Example: true
     * @queryParam only_trashed Only show soft deleted users. Example: false
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "phone": "+1234567890",
     *       "status": "active",
     *       "email_verified_at": "2024-01-01T00:00:00Z",
     *       "provider": "local",
     *       "role": "admin",
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z",
     *       "deleted_at": null
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 5,
     *     "per_page": 15,
     *     "total": 67,
     *     "from": 1,
     *     "to": 15
     *   },
     *   "links": {
     *     "first": "[[APP_URL]]/api/v1/admin/users?page=1",
     *     "last": "[[APP_URL]]/api/v1/admin/users?page=5",
     *     "prev": null,
     *     "next": "[[APP_URL]]/api/v1/admin/users?page=2"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function index(Request $request): JsonResponse|UserCollection
    {
        $user = $request->user();
        assert($user instanceof User);

        if ($user->tokenCant(Permission::READ_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $query = User::query()->with(['roles']);

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Include soft deleted if requested
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        }

        // Only trashed records
        if ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }

        $users = $query->paginate($request->integer('per_page', 15));

        return new UserCollection($users);
    }

    /**
     * Create a new user
     *
     * Create a new user account with the specified information and optionally assign roles.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @bodyParam name string required The user's full name. Example: John Doe
     * @bodyParam email string required The user's email address. Must be unique. Example: john@example.com
     * @bodyParam phone string The user's phone number in E.164 format. Example: +1234567890
     * @bodyParam password string required The user's password. Must meet password requirements. Example: password123
     * @bodyParam status string required The user's status. Must be one of: active, inactive, suspended. Example: active
     * @bodyParam provider string The authentication provider. Example: local
     * @bodyParam email_verified_at date The email verification timestamp. Example: 2024-01-01T00:00:00Z
     * @bodyParam roles array Optional array of role names to assign. Example: ["admin", "user"]
     *
     * @response 201 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "John Doe",
     *   "email": "john@example.com",
     *   "phone": "+1234567890",
     *   "status": "active",
     *   "email_verified_at": "2024-01-01T00:00:00Z",
     *   "provider": "local",
     *   "role": "admin",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function store(StoreUserRequest $request): JsonResponse|UserResource
    {
        $actingUser = $request->user();

        if (! $actingUser instanceof User) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($actingUser->tokenCant(Permission::CREATE_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'status' => $request->status,
            'provider' => $request->provider,
            'email_verified_at' => $request->email_verified_at,
        ]);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return new UserResource($user->load(['roles']));
    }

    /**
     * Get a specific user
     *
     * Retrieve detailed information about a specific user by their ID.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam user string required The ID of the user. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "John Doe",
     *   "email": "john@example.com",
     *   "phone": "+1234567890",
     *   "status": "active",
     *   "email_verified_at": "2024-01-01T00:00:00Z",
     *   "provider": "local",
     *   "role": "admin",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function show(Request $request, User $user): JsonResponse|UserResource
    {
        $actingUser = $request->user();
        if (! $actingUser instanceof User) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($actingUser->tokenCant(Permission::READ_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // $this->authorize('view', $user);

        $user->load(['roles', 'permissions']);

        return new UserResource($user);
    }

    /**
     * Update a user
     *
     * Update an existing user's information. Only provided fields will be updated.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam user string required The ID of the user to update. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @bodyParam name string The user's full name. Example: John Smith
     * @bodyParam email string The user's email address. Must be unique. Example: johnsmith@example.com
     * @bodyParam phone string The user's phone number in E.164 format. Example: +1987654321
     * @bodyParam password string The user's new password. Example: newpassword123
     * @bodyParam status string The user's status. Example: inactive
     * @bodyParam provider string The authentication provider. Example: google
     * @bodyParam email_verified_at date The email verification timestamp. Example: 2024-01-02T00:00:00Z
     * @bodyParam roles array Array of role names to assign. Example: ["user"]
     *
     * @response 200 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "John Smith",
     *   "email": "johnsmith@example.com",
     *   "phone": "+1987654321",
     *   "status": "inactive",
     *   "email_verified_at": "2024-01-02T00:00:00Z",
     *   "provider": "google",
     *   "role": "user",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-02T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email has already been taken."]
     *   }
     * }
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse|UserResource
    {
        $actingUser = $request->user();
        if (! $actingUser instanceof User) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($actingUser->tokenCant(Permission::UPDATE_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('email')) {
            $updateData['email'] = $request->email;
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->phone;
        }

        if ($request->has('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        if ($request->has('provider')) {
            $updateData['provider'] = $request->provider;
        }

        if ($request->has('email_verified_at')) {
            $updateData['email_verified_at'] = $request->email_verified_at;
        }

        $user->update($updateData);

        // Update roles if provided
        if ($request->has('roles')) {
            $user->syncRoles($request->roles);
        }

        return new UserResource($user->load(['roles']));
    }

    /**
     * Soft delete a user
     *
     * Soft delete a user account. The user can be restored later.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam user string required The ID of the user to delete. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "message": "User deleted successfully."
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $actingUser = $request->user();
        if (! $actingUser instanceof User) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($actingUser->tokenCant(Permission::DELETE_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ]);
    }

    /**
     * Restore a soft deleted user
     *
     * Restore a previously soft deleted user account.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam userId string required The ID of the user to restore. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "John Doe",
     *   "email": "john@example.com",
     *   "phone": "+1234567890",
     *   "status": "active",
     *   "email_verified_at": "2024-01-01T00:00:00Z",
     *   "provider": "local",
     *   "role": "admin",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function restore(Request $request, string $userId): JsonResponse|UserResource
    {
        $targetUser = User::withTrashed()->findOrFail($userId);

        $actingUser = $request->user();
        if (! $actingUser instanceof User) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($actingUser->tokenCant(Permission::RESTORE_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $targetUser->restore();

        return new UserResource($targetUser->load(['roles']));
    }

    /**
     * Permanently delete a user
     *
     * Permanently delete a user account. This action cannot be undone.
     *
     * @group Admin | Users
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam userId string required The ID of the user to permanently delete. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "message": "User permanently deleted."
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function forceDelete(Request $request, string $userId): JsonResponse
    {
        $targetUser = User::withTrashed()->findOrFail($userId);

        $actingUser = $request->user();
        if (! $actingUser instanceof User) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        if ($actingUser->tokenCant(Permission::DELETE_USERS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $targetUser->forceDelete();

        return response()->json([
            'message' => 'User permanently deleted.',
        ]);
    }
}
