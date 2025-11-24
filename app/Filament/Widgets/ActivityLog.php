<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

final class ActivityLog extends Widget implements HasSchemas
{
    use InteractsWithSchemas;

    public ?string $logName = null;

    public ?int $selectedActivityId = null;

    protected string $view = 'filament.widgets.activity-log';

    protected int|string|array $columnSpan = 1;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        $maxLength = $this->logNames->map(fn (string $name): int => mb_strlen($name))->max() ?? 0;
        $minWidth = max($maxLength, 3) + 4;

        return $schema
            ->components([
                Select::make('logName')
                    ->label('Filter by Log')
                    ->options(fn (): array => $this->logNames->mapWithKeys(fn (string $name): array => [$name => $name])->all())
                    ->placeholder('All')
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(fn () => $this->updatedLogName())
                    ->extraAttributes([
                        'style' => "min-width: {$minWidth}ch;",
                    ]),
            ]);
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
     * @return Collection<Activity>
     */
    public function getActivitiesProperty(): Collection
    {
        $query = Activity::query()
            ->with(['causer', 'subject'])
            ->latest('created_at');

        if ($this->logName !== null && $this->logName !== '') {
            $query->where('log_name', $this->logName);
        }

        return $query->limit(5)->get();
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

    public function getFullPageUrl(): string
    {
        // TODO: Update this URL when you create the activity log resource page
        // For example: return route('filament.control.resources.system.activity-logs.index');
        return '#';
    }
}
