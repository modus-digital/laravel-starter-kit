<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\RBAC\Permission;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityCollection;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ActivityLogController extends Controller
{
    /**
     * List all activities with role-based filtering and advanced search capabilities.
     *
     * Get a paginated list of activities with optional filtering by date range, user, event type, and subject.
     * Access is restricted based on user roles and permissions.
     *
     * @group Admin | Activity Logs
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @queryParam page integer The page number. Example: 1
     * @queryParam per_page integer Number of items per page. Default: 15. Example: 10
     * @queryParam search Search in description, causer name, and subject. Example: john
     * @queryParam event Filter by specific event types. Example: created
     * @queryParam causer_id Filter by causer ID. Example: 019ae998-0d67-7f61-a080-0921c763e695
     * @queryParam subject_type Filter by subject type (User, Client, Role, etc.). Example: App\Models\User
     * @queryParam subject_id Filter by subject ID. Example: 1
     * @queryParam date_from Filter activities from this date (YYYY-MM-DD). Example: 2024-01-01
     * @queryParam date_to Filter activities to this date (YYYY-MM-DD). Example: 2024-12-31
     * @queryParam log_name Filter by log name. Example: default
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "log_name": "default",
     *       "description": "User John Doe created a new account",
     *       "event": "created",
     *       "subject_type": "App\\Models\\User",
     *       "subject_id": "019ae998-0d67-7f61-a080-0921c763e695",
     *       "causer_type": "App\\Models\\User",
     *       "causer_id": "019ae998-0d67-7f61-a080-0921c763e695",
     *       "causer": {
     *         "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *         "name": "John Doe",
     *         "email": "john@example.com"
     *       },
     *       "properties": {
     *         "issuer": {
     *           "name": "John Doe",
     *           "email": "john@example.com",
     *           "ip_address": "127.0.0.1",
     *           "user_agent": "Mozilla/5.0..."
     *         }
     *       },
     *       "created_at": "2024-01-01T00:00:00Z",
     *       "updated_at": "2024-01-01T00:00:00Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "last_page": 1,
     *     "per_page": 15,
     *     "total": 1,
     *     "from": 1,
     *     "to": 1
     *   },
     *   "links": {
     *     "first": "[[APP_URL]]/api/v1/admin/activities?page=1",
     *     "last": "[[APP_URL]]/api/v1/admin/activities?page=1",
     *     "prev": null,
     *     "next": null
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     */
    public function index(Request $request): JsonResponse|ActivityCollection
    {
        $user = $request->user();
        assert($user instanceof User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $query = Activity::query()->with(['causer', 'subject']);

        // Apply role-based filtering
        $this->applyRoleBasedFilters($query, $user);

        // Apply search filter
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search): void {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })
                    ->orWhereHasMorph('subject', '*', function ($q, $type) use ($search): void {
                        // Create an instance to check attributes safely
                        $modelInstance = new $type;

                        if ($modelInstance instanceof Model) {
                            if (method_exists($modelInstance, 'getNameAttribute') || $modelInstance->hasAttribute('name')) {
                                $q->where('name', 'like', "%{$search}%");
                            }
                            if ($modelInstance->hasAttribute('email')) {
                                $q->orWhere('email', 'like', "%{$search}%");
                            }
                        }
                    });
            });
        }

        // Filter by event type
        if ($request->has('event') && $request->event !== '') {
            $query->where('event', $request->event);
        }

        // Filter by causer
        if ($request->has('causer_id') && $request->causer_id !== '') {
            $query->where('causer_id', $request->causer_id);
        }

        // Filter by subject type
        if ($request->has('subject_type') && $request->subject_type !== '') {
            $query->where('subject_type', $request->subject_type);
        }

        // Filter by subject ID
        if ($request->has('subject_id') && $request->subject_id !== '') {
            $query->where('subject_id', $request->subject_id);
        }

        // Filter by log name
        if ($request->has('log_name') && $request->log_name !== '') {
            $query->where('log_name', $request->log_name);
        }

        // Date range filtering
        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activities = $query->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 15));

        return new ActivityCollection($activities);
    }

    /**
     * Get a specific activity by ID.
     *
     * Retrieve detailed information about a specific activity including all properties.
     *
     * @group Admin | Activity Logs
     *
     * @authenticated
     *
     * @header Authorization Bearer {token}
     *
     * @urlParam activity integer required The activity ID. Example: 1
     *
     * @response 200 {
     *   "id": 1,
     *   "log_name": "default",
     *   "description": "User John Doe created a new account",
     *   "event": "created",
     *   "subject_type": "App\\Models\\User",
     *   "subject_id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "causer_type": "App\\Models\\User",
     *   "causer_id": "019ae998-0d67-7f61-a080-0921c763e695",
     *   "causer": {
     *     "id": "019ae998-0d67-7f61-a080-0921c763e695",
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   },
     *   "properties": {
     *     "issuer": {
     *       "name": "John Doe",
     *       "email": "john@example.com",
     *       "ip_address": "127.0.0.1",
     *       "user_agent": "Mozilla/5.0..."
     *     }
     *   },
     *   "created_at": "2024-01-01T00:00:00Z",
     *   "updated_at": "2024-01-01T00:00:00Z"
     * }
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * @response 403 {
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "message": "Activity not found"
     * }
     */
    public function show(Request $request, Activity $activity): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof User);

        if ($user->tokenCant(Permission::AccessControlPanel->value)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Apply role-based filtering to ensure user can only see activities they're allowed to
        if (! $this->canUserAccessActivity($user, $activity)) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $activity->load(['causer', 'subject']);

        return response()->json(new ActivityResource($activity));
    }

    /**
     * Apply role-based filtering to the activity query.
     * Different roles may have different access levels to activities.
     */
    /**
     * @param  Builder<Activity>  $query
     */
    private function applyRoleBasedFilters(Builder $query, User $user): void
    {
        // Super admin and admin can see all activities
        if ($user->hasRole(['super-admin', 'admin'])) {
            return;
        }

        // Regular users with activity log access can only see their own activities
        // and activities related to subjects they have access to
        $query->where(function ($q) use ($user): void {
            $q->where('causer_id', $user->id)
                ->orWhere('causer_type', '!=', User::class); // System activities
        });
    }

    /**
     * Check if a user can access a specific activity based on their role.
     */
    private function canUserAccessActivity(User $user, Activity $activity): bool
    {
        // Super admin and admin can access everything
        if ($user->hasRole(['super-admin', 'admin'])) {
            return true;
        }

        // Regular users can only access their own activities
        return $activity->causer_id !== null && (string) $activity->causer_id === $user->id;
    }
}
