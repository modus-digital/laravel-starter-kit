<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\TestS3ConnectionRequest;
use App\Http\Requests\Admin\UpdateIntegrationSettingsRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Outerweb\Settings\Facades\Setting;
use Spatie\Activitylog\Facades\Activity;

final class IntegrationController extends Controller
{
    public function edit(): Response
    {
        $enabledProviders = config('modules.socialite.providers', []);

        $integrations = [
            'mailgun' => [
                'webhook_signing_key' => $this->decryptSetting('integrations.mailgun.webhook_signing_key'),
            ],
            'oauth' => [
                'google' => [
                    'enabled' => Setting::get('integrations.oauth.google.enabled', false),
                    'client_id' => Setting::get('integrations.oauth.google.client_id'),
                    'client_secret' => $this->decryptSetting('integrations.oauth.google.client_secret'),
                    'available' => $enabledProviders['google'] ?? false,
                ],
                'github' => [
                    'enabled' => Setting::get('integrations.oauth.github.enabled', false),
                    'client_id' => Setting::get('integrations.oauth.github.client_id'),
                    'client_secret' => $this->decryptSetting('integrations.oauth.github.client_secret'),
                    'available' => $enabledProviders['github'] ?? false,
                ],
                'microsoft' => [
                    'enabled' => Setting::get('integrations.oauth.microsoft.enabled', false),
                    'client_id' => Setting::get('integrations.oauth.microsoft.client_id'),
                    'client_secret' => $this->decryptSetting('integrations.oauth.microsoft.client_secret'),
                    'available' => $enabledProviders['microsoft'] ?? false,
                ],
            ],
            's3' => [
                'enabled' => Setting::get('integrations.s3.enabled', false),
                'key' => Setting::get('integrations.s3.key'),
                'secret' => $this->decryptSetting('integrations.s3.secret'),
                'region' => Setting::get('integrations.s3.region'),
                'bucket' => Setting::get('integrations.s3.bucket'),
                'endpoint' => Setting::get('integrations.s3.endpoint'),
                'url' => Setting::get('integrations.s3.url'),
                'use_path_style_endpoint' => Setting::get('integrations.s3.use_path_style_endpoint', false),
            ],
        ];

        return Inertia::render('core/admin/integrations/edit', [
            'integrations' => $integrations,
        ]);
    }

    /**
     * Test S3 connection with provided credentials.
     */
    public function testS3Connection(TestS3ConnectionRequest $request): JsonResponse
    {
        try {
            $config = [
                'key' => $request->input('s3_key'),
                'secret' => $request->input('s3_secret'),
                'region' => $request->input('s3_region'),
                'bucket' => $request->input('s3_bucket'),
                'endpoint' => $request->input('s3_endpoint'),
                'use_path_style_endpoint' => $request->boolean('s3_use_path_style_endpoint'),
            ];

            $s3 = new \Aws\S3\S3Client([
                'version' => 'latest',
                'region' => $config['region'],
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'endpoint' => $config['endpoint'],
                'use_path_style_endpoint' => $config['use_path_style_endpoint'],
            ]);

            // Test connection by listing buckets or checking if bucket exists
            $s3->headBucket(['Bucket' => $config['bucket']]);

            return response()->json([
                'success' => true,
                'message' => 'S3 connection successful',
            ]);
        } catch (\Aws\Exception\AwsException $e) {
            return response()->json([
                'success' => false,
                'message' => 'S3 connection failed: '.$e->getMessage(),
            ], 500);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error testing S3 connection: '.$e->getMessage(),
            ], 500);
        }
    }

    public function update(UpdateIntegrationSettingsRequest $request): RedirectResponse
    {
        // Update Mailgun settings (encrypt sensitive data)
        if ($request->filled('mailgun_webhook_signing_key')) {
            Setting::set('integrations.mailgun.webhook_signing_key', encrypt($request->mailgun_webhook_signing_key));
        }

        // Update OAuth settings (encrypt secrets, store IDs and enabled state in plain text)
        Setting::set('integrations.oauth.google.enabled', $request->boolean('google_enabled'));
        Setting::set('integrations.oauth.google.client_id', $request->google_client_id);
        if ($request->filled('google_client_secret')) {
            Setting::set('integrations.oauth.google.client_secret', encrypt($request->google_client_secret));
        }

        Setting::set('integrations.oauth.github.enabled', $request->boolean('github_enabled'));
        Setting::set('integrations.oauth.github.client_id', $request->github_client_id);
        if ($request->filled('github_client_secret')) {
            Setting::set('integrations.oauth.github.client_secret', encrypt($request->github_client_secret));
        }

        Setting::set('integrations.oauth.microsoft.enabled', $request->boolean('microsoft_enabled'));
        Setting::set('integrations.oauth.microsoft.client_id', $request->microsoft_client_id);
        if ($request->filled('microsoft_client_secret')) {
            Setting::set('integrations.oauth.microsoft.client_secret', encrypt($request->microsoft_client_secret));
        }

        // Update S3 settings (encrypt secret key)
        Setting::set('integrations.s3.enabled', $request->boolean('s3_enabled'));
        Setting::set('integrations.s3.key', $request->s3_key);
        if ($request->filled('s3_secret')) {
            Setting::set('integrations.s3.secret', encrypt($request->s3_secret));
        }
        Setting::set('integrations.s3.region', $request->s3_region);
        Setting::set('integrations.s3.bucket', $request->s3_bucket);
        Setting::set('integrations.s3.endpoint', $request->s3_endpoint);
        Setting::set('integrations.s3.url', $request->s3_url);
        Setting::set('integrations.s3.use_path_style_endpoint', $request->boolean('s3_use_path_style_endpoint'));

        Activity::inLog('administration')
            ->event('integrations.updated')
            ->causedBy(Auth::user())
            ->log('activity.integrations.updated');

        return redirect()->route('admin.integrations.edit')
            ->with('success', __('admin.integrations.updated_successfully'));
    }

    /**
     * Safely decrypt a setting value, returning null if decryption fails or value doesn't exist.
     */
    private function decryptSetting(string $key): ?string
    {
        $value = Setting::get($key);

        if (! $value) {
            return null;
        }

        try {
            return decrypt($value);
        } catch (Exception) {
            // If decryption fails, the value might be stored in plain text (legacy data)
            // Return the plain value for backward compatibility
            return $value;
        }
    }
}
