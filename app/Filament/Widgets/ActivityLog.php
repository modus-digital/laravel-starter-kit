<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

final class ActivityLog extends Widget
{
    public ?string $logName = null;

    public ?int $selectedActivityId = null;

    protected string $view = 'filament.widgets.activity-log';

    protected int|string|array $columnSpan = 1;

    public function mount(): void
    {
        $this->logName = null;
    }

    /**
     * @return Collection<int, string>
     */
    public function getLogNamesProperty(): Collection
    {
        return Activity::query()
            ->distinct()
            ->whereNotNull('log_name')
            ->orderBy('log_name')
            ->pluck('log_name');
    }

    /**
     * @return Paginator<Activity>
     */
    public function getActivitiesProperty(): Paginator
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest('created_at');

        if ($this->logName !== null && $this->logName !== '') {
            $query->where('log_name', $this->logName);
        }

        return $query->paginate(10);
    }

    public function updatedLogName(): void
    {
        // Reset pagination when filter changes
    }

    public function openActivityModal(int $activityId): void
    {
        $this->selectedActivityId = $activityId;
    }

    public function closeActivityModal(): void
    {
        $this->selectedActivityId = null;
    }

    public function getSelectedActivityProperty(): ?Activity
    {
        if ($this->selectedActivityId === null) {
            return null;
        }

        return Activity::with(['causer', 'subject'])
            ->find($this->selectedActivityId);
    }
}
