<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateBrandingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can(\App\Enums\RBAC\Permission::AccessControlPanel->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'logo_light' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'logo_dark' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'emblem_light' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:1024'],
            'emblem_dark' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:1024'],
            'app_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'font' => ['required', 'string', 'max:100'],
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
            'logo_light.image' => 'The light logo must be an image file.',
            'logo_light.mimes' => 'The light logo must be a file of type: jpeg, jpg, png, svg, webp.',
            'logo_light.max' => 'The light logo must not be greater than 2MB.',
            'logo_dark.image' => 'The dark logo must be an image file.',
            'logo_dark.mimes' => 'The dark logo must be a file of type: jpeg, jpg, png, svg, webp.',
            'logo_dark.max' => 'The dark logo must not be greater than 2MB.',
            'emblem_light.image' => 'The light emblem must be an image file.',
            'emblem_light.mimes' => 'The light emblem must be a file of type: jpeg, jpg, png, svg, webp.',
            'emblem_light.max' => 'The light emblem must not be greater than 1MB.',
            'emblem_dark.image' => 'The dark emblem must be an image file.',
            'emblem_dark.mimes' => 'The dark emblem must be a file of type: jpeg, jpg, png, svg, webp.',
            'emblem_dark.max' => 'The dark emblem must not be greater than 1MB.',
            'primary_color.regex' => 'The primary color must be a valid hex color code.',
            'secondary_color.regex' => 'The secondary color must be a valid hex color code.',
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
            'logo_light' => 'light logo',
            'logo_dark' => 'dark logo',
            'app_name' => 'application name',
            'primary_color' => 'primary color',
            'secondary_color' => 'secondary color',
        ];
    }
}
