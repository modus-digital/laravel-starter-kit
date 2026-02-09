<?php

declare(strict_types=1);

namespace App\Http\Requests\Role;

use App\Enums\RBAC\Permission;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $role = $this->route('role');

        return $role instanceof Role && $this->user()?->can('update', $role);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $roleRouteParam = $this->route('role');
        $roleId = $roleRouteParam instanceof Role ? $roleRouteParam->id : $roleRouteParam;

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('roles')->ignore($roleId),
                'regex:/^[a-z_-]+$/',
                function ($attribute, $value, $fail): void {
                    $role = $this->route('role');
                    if ($role instanceof Role && $role->isInternal()) {
                        $fail('Internal roles cannot be modified.');
                    }
                },
            ],
            'guard_name' => ['sometimes', 'string', 'max:255', 'in:web'],
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
