<?php

declare(strict_types=1);

use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskView;
use App\Models\User;
use Database\Seeders\DemoTaskBoardsSeeder;

it('seeds demo task boards and tasks for the target user', function (): void {
    $this->markTestSkipped('DemoTaskBoardsSeeder not yet implemented');
    $this->seed(DemoTaskBoardsSeeder::class);

    $user = User::query()->find(DemoTaskBoardsSeeder::USER_ID);

    expect($user)->not->toBeNull();

    expect(
        TaskView::query()
            ->where('taskable_type', User::class)
            ->where('taskable_id', DemoTaskBoardsSeeder::USER_ID)
            ->count(),
    )->toBeGreaterThanOrEqual(4);

    expect(
        Task::query()
            ->where('taskable_type', User::class)
            ->where('taskable_id', DemoTaskBoardsSeeder::USER_ID)
            ->count(),
    )->toBeGreaterThanOrEqual(10);
});
