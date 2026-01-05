<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\RBAC\Permission;
use App\Http\Controllers\Controller;
use App\Http\Requests\Client\StoreClientRequest;
use App\Http\Requests\Client\UpdateClientRequest;
use App\Http\Resources\ClientCollection;
use App\Http\Resources\ClientResource;
use App\Models\Modules\Clients\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ClientController extends Controller
{
    /**
     * List all clients
     *
     * Get a paginated list of clients with optional filtering and search capabilities.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Default: 15. Example: 10
     * @queryParam status Filter by client status. Example: active
     * @queryParam search Search in name and contact_email fields. Example: john
     * @queryParam with_trashed Include soft deleted clients. Example: true
     * @queryParam only_trashed Only show soft deleted clients. Example: false
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *       "name": "Acme Corporation",
     *       "contact_name": "John Doe",
     *       "contact_email": "john@acme.com",
     *       "contact_phone": "+1234567890",
     *       "address": "123 Main St",
     *       "postal_code": "12345",
     *       "city": "New York",
     *       "country": "USA",
     *       "status": "active",
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
     *     "first": "[[APP_URL]]/api/v1/admin/clients?page=1",
     *     "last": "[[APP_URL]]/api/v1/admin/clients?page=5",
     *     "prev": null,
     *     "next": "[[APP_URL]]/api/v1/admin/clients?page=2"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function index(Request $request): JsonResponse|ClientCollection
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::READ_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $query = Client::query();

        // Apply filters
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_email', 'like', "%{$search}%");
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

        $clients = $query->paginate($request->integer('per_page', 15));

        return new ClientCollection($clients);
    }

    /**
     * Create a new client
     *
     * Create a new client account with the specified information.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @bodyParam name string required The client's name. Must be unique. Example: Acme Corporation
     * @bodyParam contact_name string The client's contact person name. Example: John Doe
     * @bodyParam contact_email string The client's contact email address. Example: john@acme.com
     * @bodyParam contact_phone string The client's contact phone number in E.164 format. Example: +1234567890
     * @bodyParam address string The client's address. Example: 123 Main St
     * @bodyParam postal_code string The client's postal code. Example: 12345
     * @bodyParam city string The client's city. Example: New York
     * @bodyParam country string The client's country. Example: USA
     * @bodyParam status string required The client's status. Must be one of: active, inactive, suspended. Example: active
     *
     * @response 201 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "Acme Corporation",
     *   "contact_name": "John Doe",
     *   "contact_email": "john@acme.com",
     *   "contact_phone": "+1234567890",
     *   "address": "123 Main St",
     *   "postal_code": "12345",
     *   "city": "New York",
     *   "country": "USA",
     *   "status": "active",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name has already been taken."]
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function store(StoreClientRequest $request): JsonResponse|ClientResource
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::CREATE_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }
        $client = Client::create([
            'name' => $request->name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'address' => $request->address,
            'postal_code' => $request->postal_code,
            'city' => $request->city,
            'country' => $request->country,
            'status' => $request->status,
        ]);

        return new ClientResource($client);
    }

    /**
     * Get a specific client
     *
     * Retrieve detailed information about a specific client by their ID.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam client string required The ID of the client. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "Acme Corporation",
     *   "contact_name": "John Doe",
     *   "contact_email": "john@acme.com",
     *   "contact_phone": "+1234567890",
     *   "address": "123 Main St",
     *   "postal_code": "12345",
     *   "city": "New York",
     *   "country": "USA",
     *   "status": "active",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 404 {
     *   "message": "Client not found"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function show(Request $request, Client $client): JsonResponse|ClientResource
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::READ_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        return new ClientResource($client);
    }

    /**
     * Update a client
     *
     * Update an existing client's information. Only provided fields will be updated.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam client string required The ID of the client to update. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @bodyParam name string The client's name. Must be unique. Example: Acme Corp
     * @bodyParam contact_name string The client's contact person name. Example: Jane Smith
     * @bodyParam contact_email string The client's contact email address. Example: jane@acme.com
     * @bodyParam contact_phone string The client's contact phone number in E.164 format. Example: +1987654321
     * @bodyParam address string The client's address. Example: 456 Oak St
     * @bodyParam postal_code string The client's postal code. Example: 67890
     * @bodyParam city string The client's city. Example: Los Angeles
     * @bodyParam country string The client's country. Example: USA
     * @bodyParam status string The client's status. Example: inactive
     *
     * @response 200 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "Acme Corp",
     *   "contact_name": "Jane Smith",
     *   "contact_email": "jane@acme.com",
     *   "contact_phone": "+1987654321",
     *   "address": "456 Oak St",
     *   "postal_code": "67890",
     *   "city": "Los Angeles",
     *   "country": "USA",
     *   "status": "inactive",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-02T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name has already been taken."]
     *   }
     * }
     * @response 404 {
     *   "message": "Client not found"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function update(UpdateClientRequest $request, Client $client): JsonResponse|ClientResource
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::UPDATE_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $updateData = [];

        if ($request->has('name')) {
            $updateData['name'] = $request->name;
        }

        if ($request->has('contact_name')) {
            $updateData['contact_name'] = $request->contact_name;
        }

        if ($request->has('contact_email')) {
            $updateData['contact_email'] = $request->contact_email;
        }

        if ($request->has('contact_phone')) {
            $updateData['contact_phone'] = $request->contact_phone;
        }

        if ($request->has('address')) {
            $updateData['address'] = $request->address;
        }

        if ($request->has('postal_code')) {
            $updateData['postal_code'] = $request->postal_code;
        }

        if ($request->has('city')) {
            $updateData['city'] = $request->city;
        }

        if ($request->has('country')) {
            $updateData['country'] = $request->country;
        }

        if ($request->has('status')) {
            $updateData['status'] = $request->status;
        }

        $client->update($updateData);

        return new ClientResource($client);
    }

    /**
     * Soft delete a client
     *
     * Soft delete a client account. The client can be restored later.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam client string required The ID of the client to delete. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "message": "Client deleted successfully."
     * }
     * @response 404 {
     *   "message": "Client not found"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function destroy(Request $request, Client $client): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::DELETE_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $client->delete();

        return response()->json([
            'message' => 'Client deleted successfully.',
        ]);
    }

    /**
     * Restore a soft deleted client
     *
     * Restore a previously soft deleted client account.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam clientId string required The ID of the client to restore. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "name": "Acme Corporation",
     *   "contact_name": "John Doe",
     *   "contact_email": "john@acme.com",
     *   "contact_phone": "+1234567890",
     *   "address": "123 Main St",
     *   "postal_code": "12345",
     *   "city": "New York",
     *   "country": "USA",
     *   "status": "active",
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z",
     *   "deleted_at": null
     * }
     * @response 404 {
     *   "message": "Client not found"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function restore(Request $request, string $clientId): JsonResponse|ClientResource
    {
        $client = Client::withTrashed()->findOrFail($clientId);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::RESTORE_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $client->restore();

        return new ClientResource($client);
    }

    /**
     * Permanently delete a client
     *
     * Permanently delete a client account. This action cannot be undone.
     *
     * @group Admin | Clients
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam clientId string required The ID of the client to permanently delete. Example: 019ae998-0d67-7f61-a080-0921c763e695
     *
     * @response 200 {
     *   "message": "Client permanently deleted."
     * }
     * @response 404 {
     *   "message": "Client not found"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function forceDelete(Request $request, string $clientId): JsonResponse
    {
        $client = Client::withTrashed()->findOrFail($clientId);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        if ($user->tokenCant(Permission::DELETE_CLIENTS->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $client->forceDelete();

        return response()->json([
            'message' => 'Client permanently deleted.',
        ]);
    }
}
