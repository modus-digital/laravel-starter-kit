<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class TestS3ConnectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->can(\App\Enums\RBAC\Permission::AccessControlPanel->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            's3_key' => ['required', 'string', 'max:255'],
            's3_secret' => ['required', 'string', 'max:255'],
            's3_region' => ['required', 'string', 'max:255'],
            's3_bucket' => ['required', 'string', 'max:255'],
            's3_url' => ['nullable', 'string', 'max:255', 'url'],
            's3_endpoint' => ['nullable', 'string', 'max:255', 'url'],
            's3_use_path_style_endpoint' => ['boolean'],
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
            's3_key.required' => 'The S3 access key is required to test the connection.',
            's3_secret.required' => 'The S3 secret key is required to test the connection.',
            's3_region.required' => 'The S3 region is required to test the connection.',
            's3_bucket.required' => 'The S3 bucket name is required to test the connection.',
            's3_endpoint.url' => 'The S3 endpoint must be a valid URL.',
            's3_url.url' => 'The S3 URL must be a valid URL.',
        ];
    }
}
