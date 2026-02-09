<?php

declare(strict_types=1);

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

final class StoreClientUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can(\App\Enums\RBAC\Permission::UpdateClients->value);
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'string', 'exists:users,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->role_id === '' || $this->role_id === null) {
            $this->merge(['role_id' => null]);
        }
    }
}
