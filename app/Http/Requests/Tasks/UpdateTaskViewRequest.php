<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Models\Modules\Tasks\TaskView;
use App\Services\TaskService;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateTaskViewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if ($user === null) {
            return false;
        }

        /** @var TaskView|null $taskView */
        $taskView = $this->route('taskView');
        if ($taskView === null) {
            return false;
        }

        // Check if the user has access to this view
        $taskService = app(TaskService::class);
        $accessibleViewIds = $taskService->queryAccessibleTaskViewsForUser(
            user: $user,
            currentClientId: session()->get('current_client_id'),
        )->pluck('id')->toArray();

        return in_array($taskView->id, $accessibleViewIds, true);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status_ids' => ['sometimes', 'required', 'array', 'min:1'],
            'status_ids.*' => ['required', 'string', 'uuid', 'exists:task_statuses,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status_ids.min' => 'Select at least one status.',
            'status_ids.required' => 'Select at least one status.',
        ];
    }
}
