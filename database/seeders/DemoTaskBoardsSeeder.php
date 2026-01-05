<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\Modules\Tasks\TaskView;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DemoTaskBoardsSeeder extends Seeder
{
    public const string USER_ID = '019b4a87-0ec0-71c9-ba7b-4de2fbbbef8b';

    public function run(): void
    {
        if (config('modules.tasks.enabled') !== true) {
            return;
        }

        $user = User::query()->updateOrCreate(
            ['id' => self::USER_ID],
            [
                'name' => 'Demo Tasks User',
                'email' => 'demo.tasks.user@local.test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );

        $statuses = TaskStatus::query()
            ->whereIn('name', ['Todo', 'In Progress', 'Done'])
            ->get()
            ->keyBy('name');

        $todo = $statuses->get('Todo') ?? TaskStatus::findOrCreateByName(name: 'Todo', color: '#3498db');
        $inProgress = $statuses->get('In Progress') ?? TaskStatus::findOrCreateByName(name: 'In Progress', color: '#f1c40f');
        $done = $statuses->get('Done') ?? TaskStatus::findOrCreateByName(name: 'Done', color: '#2ecc71');

        $defaultStatuses = [
            ['name' => $todo->name, 'color' => $todo->color],
            ['name' => $inProgress->name, 'color' => $inProgress->color],
            ['name' => $done->name, 'color' => $done->color],
        ];

        $views = [
            [
                'name' => 'My Tasks',
                'type' => 'list',
                'is_default' => true,
            ],
            [
                'name' => 'Sprint Board',
                'type' => 'kanban',
                'is_default' => false,
            ],
            [
                'name' => 'Release Calendar',
                'type' => 'calendar',
                'is_default' => false,
            ],
            [
                'name' => 'Roadmap',
                'type' => 'gantt',
                'is_default' => false,
            ],
        ];

        foreach ($views as $view) {
            $taskView = TaskView::query()->updateOrCreate(
                [
                    'taskable_type' => User::class,
                    'taskable_id' => $user->id,
                    'slug' => "demo-{$view['type']}-{$user->id}",
                ],
                [
                    'name' => $view['name'],
                    'type' => $view['type'],
                    'is_default' => $view['is_default'],
                    'metadata' => null,
                ],
            );

            $taskView->syncStatusesByNames($defaultStatuses);
        }

        $now = CarbonImmutable::now();

        $seedTasks = [
            // Todo
            [
                'title' => 'Write onboarding checklist',
                'status_id' => $todo->id,
                'order' => 1,
                'priority' => 'normal',
                'type' => 'documentation',
                'due_date' => $now->addDays(3),
            ],
            [
                'title' => 'Triage incoming bug reports',
                'status_id' => $todo->id,
                'order' => 2,
                'priority' => 'high',
                'type' => 'task',
                'due_date' => $now->addDays(1),
            ],
            [
                'title' => 'Design “Create View” modal UX polish',
                'status_id' => $todo->id,
                'order' => 3,
                'priority' => 'normal',
                'type' => 'feature',
                'due_date' => $now->addDays(5),
            ],

            // In Progress
            [
                'title' => 'Implement drag-and-drop ordering',
                'status_id' => $inProgress->id,
                'order' => 1,
                'priority' => 'high',
                'type' => 'feature',
                'due_date' => $now->addDays(2),
            ],
            [
                'title' => 'Fix task status color mismatch',
                'status_id' => $inProgress->id,
                'order' => 2,
                'priority' => 'critical',
                'type' => 'bug',
                'due_date' => $now->addDay(),
            ],

            // Done
            [
                'title' => 'Set up default task statuses',
                'status_id' => $done->id,
                'order' => 1,
                'priority' => 'low',
                'type' => 'task',
                'due_date' => $now->subDays(2),
                'completed_at' => $now->subDay(),
            ],
            [
                'title' => 'Add tasks index Inertia page',
                'status_id' => $done->id,
                'order' => 2,
                'priority' => 'normal',
                'type' => 'task',
                'due_date' => $now->subDays(6),
                'completed_at' => $now->subDays(4),
            ],
        ];

        foreach ($seedTasks as $data) {
            Task::query()->updateOrCreate(
                [
                    'taskable_type' => User::class,
                    'taskable_id' => $user->id,
                    'title' => $data['title'],
                ],
                [
                    'type' => $data['type'],
                    'priority' => $data['priority'],
                    'description' => $data['description'] ?? null,
                    'order' => $data['order'] ?? null,
                    'status_id' => $data['status_id'],
                    'created_by_id' => $user->id,
                    'assigned_to_id' => $user->id,
                    'due_date' => $data['due_date'] ?? null,
                    'completed_at' => $data['completed_at'] ?? null,
                ],
            );
        }

        // A few extra tasks for variety (idempotent via stable titles).
        foreach (range(1, 6) as $i) {
            $title = "Demo task {$i}";

            Task::query()->updateOrCreate(
                [
                    'taskable_type' => User::class,
                    'taskable_id' => $user->id,
                    'title' => $title,
                ],
                [
                    'description' => null,
                    'type' => 'task',
                    'priority' => 'normal',
                    'order' => null,
                    'status_id' => [$todo->id, $inProgress->id, $done->id][$i % 3],
                    'created_by_id' => $user->id,
                    'assigned_to_id' => $user->id,
                    'due_date' => $now->addDays($i),
                    'completed_at' => null,
                ],
            );
        }
    }
}
