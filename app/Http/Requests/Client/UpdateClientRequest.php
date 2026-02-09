<?php

declare(strict_types=1);

namespace App\Http\Requests\Client;

use App\Enums\ActivityStatus;
use App\Models\Modules\Clients\Client;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(\App\Enums\RBAC\Permission::UpdateClients->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clientRouteParam = $this->route('client');
        $clientId = $clientRouteParam instanceof Client ? $clientRouteParam->id : $clientRouteParam;

        return [
            'name' => ['sometimes', 'string', 'max:255', Rule::unique('clients')->ignore($clientId)],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(ActivityStatus::class)],
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
            'contact_phone.regex' => 'The contact phone number must be in E.164 format.',
            'status' => 'The selected status is invalid.',
            'name.unique' => 'A client with this name already exists.',
        ];
    }
}
