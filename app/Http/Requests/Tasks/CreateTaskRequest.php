<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Enums\Modules\Tasks\TaskPriority;
use App\Enums\Modules\Tasks\TaskType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        $currentClientId = session()->get('current_client_id');
        if ($currentClientId === null) {
            return true;
        }

        return $user->clients()->whereKey($currentClientId)->exists();
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'json'],
            'type' => ['nullable', 'string', Rule::enum(TaskType::class)],
            'priority' => ['nullable', 'string', Rule::enum(TaskPriority::class)],
            'status_id' => ['nullable', 'string', 'uuid', 'exists:task_statuses,id'],
            'order' => ['nullable', 'integer'],
            'due_date' => ['nullable', 'date'],
            'assigned_to_id' => ['nullable', 'string', 'uuid', 'exists:users,id'],
        ];
    }
}
