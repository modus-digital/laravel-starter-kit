<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $stats = [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_activities' => Activity::whereNotIn('log_name', config('modules.activity_logs.banlist'))->count(),
        ];

        // Recent activities
        $recentActivities = Activity::query()
            ->with(['causer:id,name,email'])
            ->latest()
            ->limit(10)
            ->get();

        // Role distribution
        $roleDistribution = Role::query()
            ->withCount('users')
            ->get()
            ->map(fn (Role $role) => [
                'name' => $role->name,
                'count' => $role->users_count,
            ]);

        return Inertia::render('admin/index', [
            'stats' => $stats,
            'recentActivities' => ['data' => ActivityResource::collection($recentActivities)->toArray(request())],
            'roleDistribution' => $roleDistribution,
        ]);
    }
}
