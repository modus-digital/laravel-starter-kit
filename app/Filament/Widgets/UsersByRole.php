<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\RBAC\Role as RBACRole;
use Filament\Widgets\ChartWidget;
use Spatie\Permission\Models\Role;

final class UsersByRole extends ChartWidget
{
    protected int|string|array $columnSpan = 1;

    protected static ?int $sort = 2;

    public function getHeading(): string
    {
        return __('admin.widgets.users_by_role.heading');
    }

    protected function getData(): array
    {
        $roles = Role::withCount('users')->get();

        return [
            'datasets' => [
                [
                    'label' => 'Users',
                    'data' => $roles->pluck('users_count')->toArray(),
                    'backgroundColor' => [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)',
                        'rgb(236, 72, 153)',
                    ],
                ],
            ],
            'labels' => $roles->pluck('name')->map(fn (string $name): string => RBACRole::from($name)->getLabel())->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'offsetY' => 20,
                ],
            ],
            'maintainAspectRatio' => false,
        ];
    }
}
