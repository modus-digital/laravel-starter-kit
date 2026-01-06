<?php

declare(strict_types=1);

namespace App\Filament\Pages\MailgunAnalytics;

use App\Enums\Modules\Mailgun\EmailStatus;
use App\Models\Modules\Mailgun\EmailMessage;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

final class MailgunStatsWidget extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $now = Carbon::now();
        $last7Days = $now->copy()->subDays(7);
        $last30Days = $now->copy()->subDays(30);

        return [
            Stat::make(__('admin.mailgun.stats.delivered'), $this->getDeliveredCount($last7Days))
                ->description(__('admin.mailgun.stats.last_7_days'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart($this->getDeliveredChartData($last7Days)),

            Stat::make(__('admin.mailgun.stats.bounced'), $this->getBouncedCount($last7Days))
                ->description(__('admin.mailgun.stats.last_7_days'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning')
                ->chart($this->getBouncedChartData($last7Days)),

            Stat::make(__('admin.mailgun.stats.failed'), $this->getFailedCount($last7Days))
                ->description(__('admin.mailgun.stats.last_7_days'))
                ->descriptionIcon('heroicon-m-x-circle')
                ->color('danger')
                ->chart($this->getFailedChartData($last7Days)),

            Stat::make(__('admin.mailgun.stats.dropped'), $this->getDroppedCount($last7Days))
                ->description(__('admin.mailgun.stats.last_7_days'))
                ->descriptionIcon('heroicon-m-minus-circle')
                ->color('gray')
                ->chart($this->getDroppedChartData($last7Days)),
        ];
    }

    private function getDeliveredCount(Carbon $since): int
    {
        return EmailMessage::where('status', EmailStatus::DELIVERED)
            ->where('sent_at', '>=', $since)
            ->count();
    }

    private function getBouncedCount(Carbon $since): int
    {
        return EmailMessage::where('status', EmailStatus::BOUNCED)
            ->where('sent_at', '>=', $since)
            ->count();
    }

    private function getFailedCount(Carbon $since): int
    {
        return EmailMessage::where('status', EmailStatus::FAILED)
            ->where('sent_at', '>=', $since)
            ->count();
    }

    private function getDroppedCount(Carbon $since): int
    {
        return EmailMessage::where('status', EmailStatus::DROPPED)
            ->where('sent_at', '>=', $since)
            ->count();
    }

    /**
     * @return array<int, int>
     */
    private function getDeliveredChartData(Carbon $since): array
    {
        return $this->getChartData($since, EmailStatus::DELIVERED);
    }

    /**
     * @return array<int, int>
     */
    private function getBouncedChartData(Carbon $since): array
    {
        return $this->getChartData($since, EmailStatus::BOUNCED);
    }

    /**
     * @return array<int, int>
     */
    private function getFailedChartData(Carbon $since): array
    {
        return $this->getChartData($since, EmailStatus::FAILED);
    }

    /**
     * @return array<int, int>
     */
    private function getDroppedChartData(Carbon $since): array
    {
        return $this->getChartData($since, EmailStatus::DROPPED);
    }

    /**
     * Get chart data for the last 7 days.
     *
     * @return array<int, int>
     */
    private function getChartData(Carbon $since, EmailStatus $status): array
    {
        $data = [];
        $now = Carbon::now();

        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->endOfDay();

            $count = EmailMessage::where('status', $status)
                ->whereBetween('sent_at', [$date, $nextDate])
                ->count();

            $data[] = $count;
        }

        return $data;
    }
}
