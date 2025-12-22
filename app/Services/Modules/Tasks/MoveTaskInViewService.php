<?php

declare(strict_types=1);

namespace App\Services\Modules\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Models\Modules\Tasks\TaskStatus;
use App\Models\Modules\Tasks\TaskView;
use App\Models\Modules\Tasks\TaskViewTaskPosition;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class MoveTaskInViewService
{
    public function move(TaskView $view, Task $task, TaskStatus $toStatus, int $toPosition): void
    {
        $clampedPosition = max(0, $toPosition);

        DB::transaction(function () use ($view, $task, $toStatus, $clampedPosition): void {
            $this->assertTaskBelongsToViewTaskable($view, $task);
            $this->assertStatusEnabledOnView($view, $toStatus);

            $fromStatusId = $task->status_id;

            $targetTaskIds = $this->orderedTaskIds($view, $toStatus->id);
            $targetTaskIds = $this->removeTaskId($targetTaskIds, $task->id);

            $insertIndex = min($clampedPosition, count($targetTaskIds));
            array_splice($targetTaskIds, $insertIndex, 0, [$task->id]);

            $this->persistColumn($view, $toStatus->id, $targetTaskIds);

            if ($fromStatusId !== $toStatus->id) {
                $sourceTaskIds = $this->orderedTaskIds($view, $fromStatusId);
                $sourceTaskIds = $this->removeTaskId($sourceTaskIds, $task->id);
                $this->persistColumn($view, $fromStatusId, $sourceTaskIds);
            }

            $task->status()->associate($toStatus);
            $task->save();
        });
    }

    /**
     * @return array<int, string>
     */
    private function orderedTaskIds(TaskView $view, string $statusId): array
    {
        $positioned = TaskViewTaskPosition::query()
            ->where('task_view_id', $view->id)
            ->where('task_status_id', $statusId)
            ->orderBy('position')
            ->pluck('task_id')
            ->all();

        $unpositioned = Task::query()
            ->where('taskable_type', $view->taskable_type)
            ->where('taskable_id', $view->taskable_id)
            ->where('status_id', $statusId)
            ->whereNotIn('id', $positioned)
            ->pluck('id')
            ->all();

        return array_merge($positioned, $unpositioned);
    }

    /**
     * @param  array<int, string>  $orderedTaskIds
     */
    private function persistColumn(TaskView $view, string $statusId, array $orderedTaskIds): void
    {
        TaskViewTaskPosition::query()
            ->where('task_view_id', $view->id)
            ->where('task_status_id', $statusId)
            ->when($orderedTaskIds !== [], static function ($query) use ($orderedTaskIds): void {
                $query->whereNotIn('task_id', $orderedTaskIds);
            })
            ->delete();

        foreach ($orderedTaskIds as $index => $taskId) {
            TaskViewTaskPosition::query()->updateOrCreate(
                [
                    'task_view_id' => $view->id,
                    'task_id' => $taskId,
                ],
                [
                    'task_status_id' => $statusId,
                    'position' => $index,
                ],
            );
        }
    }

    /**
     * @param  array<int, string>  $taskIds
     * @return array<int, string>
     */
    private function removeTaskId(array $taskIds, string $taskId): array
    {
        return array_values(array_filter($taskIds, static fn (string $id): bool => $id !== $taskId));
    }

    private function assertTaskBelongsToViewTaskable(TaskView $view, Task $task): void
    {
        if ($task->taskable_type !== $view->taskable_type || $task->taskable_id !== $view->taskable_id) {
            throw new InvalidArgumentException('Task does not belong to the view taskable.');
        }
    }

    private function assertStatusEnabledOnView(TaskView $view, TaskStatus $status): void
    {
        $exists = $view->statuses()
            ->whereKey($status->getKey())
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException('Status is not enabled on this view.');
        }
    }
}
