<?php

declare(strict_types=1);

namespace App\Http\Requests\Tasks;

use App\Enums\Modules\Tasks\TaskViewType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateTaskViewRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return request()->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::enum(TaskViewType::class)],
            'status_ids' => ['nullable', 'array'],
            'status_ids.*' => ['required', 'string', 'uuid', 'exists:task_statuses,id'],
        ];
    }
}
