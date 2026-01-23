<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

final class MailgunAnalyticsController extends Controller
{
    public function index(Request $request): Response
    {
        // Get email statistics
        $stats = [
            'total_sent' => EmailMessage::count(),
            'total_delivered' => EmailEvent::where('event_type', 'delivered')->count(),
            'total_opened' => EmailEvent::where('event_type', 'opened')->count(),
            'total_clicked' => EmailEvent::where('event_type', 'clicked')->count(),
            'total_bounced' => EmailEvent::where('event_type', 'bounced')->count(),
            'total_failed' => EmailEvent::where('event_type', 'failed')->count(),
        ];

        // Get trend data for the last 7 days
        $trends = $this->getTrendData();

        // Get recent messages
        $recentMessages = EmailMessage::query()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (EmailMessage $message) => [
                'id' => $message->id,
                'recipient' => $message->to_address,
                'subject' => $message->subject,
                'status' => $message->status,
                'created_at' => $message->created_at->toISOString(),
            ]);

        // Get event breakdown
        $eventBreakdown = EmailEvent::query()
            ->selectRaw('event_type, COUNT(*) as count')
            ->groupBy('event_type')
            ->get()
            ->map(fn ($event) => [
                'event' => $event->event_type,
                'count' => $event->count,
            ]);

        return Inertia::render('admin/mailgun/index', [
            'stats' => $stats,
            'trends' => $trends,
            'recentMessages' => $recentMessages,
            'eventBreakdown' => $eventBreakdown,
        ]);
    }

    /**
     * Get trend data for the last 7 days.
     *
     * @return array<string, array<int, array{date: string, count: int}>>
     */
    private function getTrendData(): array
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Generate all dates in the range
        $dates = collect(CarbonPeriod::create($startDate, $endDate))
            ->mapWithKeys(fn (Carbon $date) => [$date->format('Y-m-d') => 0]);

        // Get sent counts by day
        $sentByDay = EmailMessage::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date');

        // Get event counts by day for each event type
        $eventTypes = ['delivered', 'opened', 'clicked', 'bounced', 'failed'];
        $eventsByDay = [];

        foreach ($eventTypes as $eventType) {
            $eventsByDay[$eventType] = EmailEvent::query()
                ->where('event_type', $eventType)
                ->where('occurred_at', '>=', $startDate)
                ->selectRaw('DATE(occurred_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->pluck('count', 'date');
        }

        // Format the data for charts
        $formatTrend = fn ($data) => $dates->merge($data)
            ->map(fn ($count, $date) => ['date' => $date, 'count' => (int) $count])
            ->values()
            ->toArray();

        return [
            'sent' => $formatTrend($sentByDay),
            'delivered' => $formatTrend($eventsByDay['delivered']),
            'opened' => $formatTrend($eventsByDay['opened']),
            'clicked' => $formatTrend($eventsByDay['clicked']),
            'bounced' => $formatTrend($eventsByDay['bounced']),
            'failed' => $formatTrend($eventsByDay['failed']),
        ];
    }
}
