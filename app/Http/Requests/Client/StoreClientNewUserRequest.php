<?php

declare(strict_types=1);

namespace App\Http\Requests\Client;

use App\Enums\ActivityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class StoreClientNewUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(\App\Enums\RBAC\Permission::UpdateClients->value);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'status' => ['required', Rule::enum(ActivityStatus::class)],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->role_id === '' || $this->role_id === null) {
            $this->merge(['role_id' => null]);
        }

        if (! $this->has('status') || $this->status === '') {
            $this->merge(['status' => ActivityStatus::ACTIVE->value]);
        }
    }
}
