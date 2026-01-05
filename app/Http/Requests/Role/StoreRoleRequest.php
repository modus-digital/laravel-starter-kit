<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Enums\RBAC\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // TODO: Add proper authorization check
        // return $this->user()->can(Permission::CREATE_ROLES->value);
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
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:roles,name',
                'regex:/^[a-z_-]+$/',
                'not_in:super_admin,admin', // Cannot create internal roles
            ],
            'guard_name' => ['required', 'string', 'max:255', 'in:web'],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:255', 'regex:/^#[a-fA-F0-9]{6}$|^[a-fA-F0-9]{3}$/'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_column(Permission::cases(), 'value'))],
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
            'name.regex' => 'The role name may only contain lowercase letters, underscores, and hyphens.',
            'name.not_in' => 'Internal roles (super_admin, admin) cannot be created.',
            'color.regex' => 'The color must be a valid hex color code.',
            'permissions.*.in' => 'One or more selected permissions are invalid.',
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
            'guard_name' => 'guard name',
        ];
    }
}
