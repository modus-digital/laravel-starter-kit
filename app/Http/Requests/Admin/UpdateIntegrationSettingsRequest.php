<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateIntegrationSettingsRequest extends FormRequest
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
            'mailgun_webhook_signing_key' => ['nullable', 'string', 'max:255'],
            'google_enabled' => ['boolean'],
            'google_client_id' => ['nullable', 'string', 'max:255'],
            'google_client_secret' => ['nullable', 'string', 'max:255'],
            'github_enabled' => ['boolean'],
            'github_client_id' => ['nullable', 'string', 'max:255'],
            'github_client_secret' => ['nullable', 'string', 'max:255'],
            'microsoft_enabled' => ['boolean'],
            'microsoft_client_id' => ['nullable', 'string', 'max:255'],
            'microsoft_client_secret' => ['nullable', 'string', 'max:255'],
            's3_enabled' => ['boolean'],
            's3_key' => ['nullable', 'string', 'max:255'],
            's3_secret' => ['nullable', 'string', 'max:255'],
            's3_region' => ['nullable', 'string', 'max:255'],
            's3_bucket' => ['nullable', 'string', 'max:255'],
            's3_endpoint' => ['nullable', 'string', 'max:255', 'url'],
            's3_url' => ['nullable', 'string', 'max:255', 'url'],
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
            'mailgun_webhook_signing_key.max' => 'The Mailgun webhook signing key must not exceed 255 characters.',
            'google_client_id.max' => 'The Google client ID must not exceed 255 characters.',
            'google_client_secret.max' => 'The Google client secret must not exceed 255 characters.',
            'github_client_id.max' => 'The GitHub client ID must not exceed 255 characters.',
            'github_client_secret.max' => 'The GitHub client secret must not exceed 255 characters.',
            'microsoft_client_id.max' => 'The Microsoft client ID must not exceed 255 characters.',
            'microsoft_client_secret.max' => 'The Microsoft client secret must not exceed 255 characters.',
            's3_key.max' => 'The S3 access key must not exceed 255 characters.',
            's3_secret.max' => 'The S3 secret must not exceed 255 characters.',
            's3_region.max' => 'The S3 region must not exceed 255 characters.',
            's3_bucket.max' => 'The S3 bucket name must not exceed 255 characters.',
            's3_endpoint.max' => 'The S3 endpoint must not exceed 255 characters.',
            's3_endpoint.url' => 'The S3 endpoint must be a valid URL.',
            's3_url.max' => 'The S3 URL must not exceed 255 characters.',
            's3_url.url' => 'The S3 URL must be a valid URL.',
        ];
    }
}
