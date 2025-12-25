<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Models\Modules\Tasks\Task;
use App\Services\TaskService;
use Illuminate\Foundation\Http\FormRequest;

final class DeleteTaskRequest extends FormRequest
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
        return [];
    }
}
