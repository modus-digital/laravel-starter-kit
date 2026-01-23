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
            'logo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,svg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:ico,png,svg', 'max:1024'],
            'app_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:500'],
            'primary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'secondary_color' => ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'font' => ['required', 'string', 'max:100'],
            'logo_aspect_ratio' => ['nullable', 'string', 'in:1:1,16:9'],
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
            'logo.image' => 'The logo must be an image file.',
            'logo.mimes' => 'The logo must be a file of type: jpeg, jpg, png, svg, webp.',
            'logo.max' => 'The logo must not be greater than 2MB.',
            'favicon.image' => 'The favicon must be an image file.',
            'favicon.mimes' => 'The favicon must be a file of type: ico, png, svg.',
            'favicon.max' => 'The favicon must not be greater than 1MB.',
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
            'app_name' => 'application name',
            'primary_color' => 'primary color',
            'secondary_color' => 'secondary color',
        ];
    }
}
