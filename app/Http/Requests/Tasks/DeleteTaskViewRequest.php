<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Models\Modules\Tasks\TaskView;
use App\Services\TaskService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

final class DeleteTaskViewRequest extends FormRequest
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
        return [];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var TaskView|null $taskView */
            $taskView = $this->route('taskView');

            if ($taskView !== null && $taskView->is_default) {
                $validator->errors()->add(
                    'taskView',
                    'Cannot delete the default view. Please set another view as default first.'
                );
            }
        });
    }
}
