<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\ActivityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Add proper authorization check based on your permission system
        // return $this->user()->can('create users');
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[1-9]\d{1,14}$/'],
            'password' => ['required', 'string', Password::defaults()],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
            'provider' => ['nullable', 'string', 'max:255'],
            'email_verified_at' => ['nullable', 'date'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ];

        // Add client_ids validation if clients module is enabled
        if (config('modules.clients.enabled', false)) {
            $rules['client_ids'] = ['nullable', 'array'];
            $rules['client_ids.*'] = ['required', 'string', 'exists:clients,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'The phone number must be in E.164 format.',
            'status' => 'The selected status is invalid.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email_verified_at' => 'email verified at',
        ];
    }
}
