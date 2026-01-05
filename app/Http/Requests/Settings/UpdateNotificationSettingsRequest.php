<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enums\NotificationDeliveryMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateNotificationSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notifications' => ['required', 'array'],
            'notifications.*' => ['required', 'string', Rule::enum(NotificationDeliveryMethod::class)],
        ];
    }
}
