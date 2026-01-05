<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Enums\Modules\Tasks\TaskPriority;
use App\Enums\Modules\Tasks\TaskType;
use App\Models\Modules\Tasks\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        /** @var Task|null $task */
        $task = $this->route('task');
        if ($task === null) {
            return false;
        }

        $taskService = app(TaskService::class);

        return $taskService->queryAccessibleTasksForUser(
            user: $user,
            currentClientId: session()->get('current_client_id'),
        )->whereKey($task->getKey())->exists();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'json'],
            'type' => ['sometimes', 'nullable', 'string', Rule::enum(TaskType::class)],
            'priority' => ['sometimes', 'nullable', 'string', Rule::enum(TaskPriority::class)],
            'status_id' => ['sometimes', 'nullable', 'string', 'uuid', 'exists:task_statuses,id'],
            'order' => ['sometimes', 'nullable', 'integer'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'assigned_to_id' => ['sometimes', 'nullable', 'string', 'uuid', 'exists:users,id'],
        ];
    }
}
