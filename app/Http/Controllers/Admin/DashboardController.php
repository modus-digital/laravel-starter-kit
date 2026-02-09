<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ActivityStatus;
use App\Enums\Modules\Mailgun\EmailStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\Modules\Clients\Client;
use App\Models\Modules\Mailgun\EmailMessage;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        return Inertia::render('core/admin/dashboard/index', [
            'layout' => $user->getPreference('dashboard.layout', $this->defaultLayout()),
            'availableWidgets' => $this->getAvailableWidgets(),
            'widgetData' => Inertia::defer(fn (): array => [
                'stats' => $this->getStats(),
                'recentActivities' => $this->getRecentActivities(),
                'clientStats' => $this->getClientStats(),
                'emailStats' => $this->getEmailStats(),
                'activityTrends' => $this->getActivityTrends(),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'total_activities' => Activity::whereNotIn('log_name', config('modules.activity_logs.banlist'))->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRecentActivities(): array
    {
        $activities = Activity::query()
            ->with(['causer:id,name,email'])
            ->whereNotIn('log_name', config('modules.activity_logs.banlist'))
            ->latest()
            ->limit(10)
            ->get();

        /** @var array<int, array<string, mixed>> $result */
        $result = ActivityResource::collection($activities)->toArray(request());

        return $result;
    }

    /**
     * @return array<string, int>
     */
    private function getClientStats(): array
    {
        if (! config('modules.clients.enabled', false)) {
            return [
                'total' => 0,
                'active' => 0,
                'new_this_month' => 0,
            ];
        }

        return [
            'total' => Client::count(),
            'active' => Client::where('status', ActivityStatus::ACTIVE)->count(),
            'new_this_month' => Client::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function getEmailStats(): array
    {
        if (! config('modules.mailgun.enabled', false)) {
            return [
                'total_sent' => 0,
                'delivered' => 0,
                'failed' => 0,
            ];
        }

        return [
            'total_sent' => EmailMessage::count(),
            'delivered' => EmailMessage::where('status', EmailStatus::DELIVERED)->count(),
            'failed' => EmailMessage::whereIn('status', [EmailStatus::FAILED, EmailStatus::BOUNCED, EmailStatus::DROPPED])->count(),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getActivityTrends(): array
    {
        return Activity::query()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotIn('log_name', config('modules.activity_logs.banlist'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function defaultLayout(): array
    {
        return []; // Empty by default - users add widgets manually
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getAvailableWidgets(): array
    {
        $widgets = [
            [
                'id' => 'stats',
                'name' => 'Statistics Overview',
                'description' => 'Total users, roles, permissions, and activities',
                'defaultSize' => ['w' => 12, 'h' => 2],
            ],
            [
                'id' => 'activities',
                'name' => 'Recent Activities',
                'description' => 'Latest activity log entries',
                'defaultSize' => ['w' => 6, 'h' => 4],
            ],
            [
                'id' => 'activity-chart',
                'name' => 'Activity Trends',
                'description' => 'Activity trends over the last 30 days',
                'defaultSize' => ['w' => 6, 'h' => 3],
            ],
        ];

        if (config('modules.clients.enabled', false)) {
            $widgets[] = [
                'id' => 'clients',
                'name' => 'Client Overview',
                'description' => 'Client statistics and metrics',
                'defaultSize' => ['w' => 6, 'h' => 3],
            ];
        }

        if (config('modules.mailgun.enabled', false)) {
            $widgets[] = [
                'id' => 'email',
                'name' => 'Email Analytics',
                'description' => 'Email delivery and engagement stats',
                'defaultSize' => ['w' => 6, 'h' => 3],
            ];
        }

        return $widgets;
    }
}
