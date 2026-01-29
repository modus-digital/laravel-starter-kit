<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class ActivityController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Activity::query()->with(['causer:id,name,email', 'subject']);

        $inAuditableLogs = config('modules.activity_logs.banlist');

        // Apply filters
        if ($request->has('log_name') && $request->log_name !== '') {
            $query->where('log_name', $request->log_name);
        }

        $query->whereNotIn('log_name', $inAuditableLogs);

        if ($request->has('event') && $request->event !== '') {
            $query->where('event', 'like', "%{$request->event}%");
        }

        if ($request->has('causer_id') && $request->causer_id !== '') {
            $query->where('causer_id', $request->causer_id);
        }

        if ($request->has('date_from') && $request->date_from !== '') {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to !== '') {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Apply sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_direction', 'desc');
        $allowedSorts = ['log_name', 'event', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDirection === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $activities = $query->get();

        // Get unique log names for filter
        $logNames = Activity::query()
            ->whereNotIn('log_name', $inAuditableLogs)
            ->select('log_name')
            ->distinct()
            ->pluck('log_name');

        return Inertia::render('core/admin/activities/index', [
            'activities' => ActivityResource::collection($activities)->toArray(request()),
            'filters' => $request->only(['log_name', 'event', 'causer_id', 'date_from', 'date_to', 'sort_by', 'sort_direction']),
            'logNames' => $logNames,
        ]);
    }

    public function show(Activity $activity): Response
    {
        $activity->load(['causer:id,name,email', 'subject']);

        return Inertia::render('core/admin/activities/show', [
            'activity' => new ActivityResource($activity),
        ]);
    }
}
