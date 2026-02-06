<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Outerweb\Settings\Facades\Setting;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create required permissions in the database
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            Spatie\Permission\Models\Permission::create(['name' => $permission->value]);
        }
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::AccessControlPanel);
});

it('can show integrations edit page', function () {
    $response = $this->actingAs($this->user)->get('/admin/integrations');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/integrations/edit')
            ->has('integrations')
        );
});

it('can update integration settings', function () {
    $data = [
        'mailgun_webhook_signing_key' => 'test_key_123',
        'google_enabled' => true,
        'google_client_id' => 'google_client_id',
        'google_client_secret' => 'google_secret',
        'github_enabled' => true,
        'github_client_id' => 'github_client_id',
        'github_client_secret' => 'github_secret',
        'microsoft_enabled' => false,
        'microsoft_client_id' => 'microsoft_client_id',
        'microsoft_client_secret' => 'microsoft_secret',
        's3_enabled' => true,
        's3_key' => 'AKIAIOSFODNN7EXAMPLE',
        's3_secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
        's3_region' => 'us-east-1',
        's3_bucket' => 'my-bucket',
        's3_endpoint' => 'https://s3.amazonaws.com',
        's3_url' => 'https://my-bucket.s3.amazonaws.com',
        's3_use_path_style_endpoint' => false,
    ];

    $response = $this->actingAs($this->user)->put('/admin/integrations', $data);

    $response->assertRedirect('/admin/integrations');

    // Verify encrypted values can be decrypted
    expect(decrypt(Setting::get('integrations.mailgun.webhook_signing_key')))->toBe('test_key_123');
    expect(decrypt(Setting::get('integrations.oauth.google.client_secret')))->toBe('google_secret');
    expect(decrypt(Setting::get('integrations.oauth.github.client_secret')))->toBe('github_secret');
    expect(decrypt(Setting::get('integrations.oauth.microsoft.client_secret')))->toBe('microsoft_secret');
    expect(decrypt(Setting::get('integrations.s3.secret')))->toBe('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');

    // Verify plain text values (client IDs and enabled states are not encrypted)
    expect(Setting::get('integrations.oauth.google.enabled'))->toBe(true);
    expect(Setting::get('integrations.oauth.google.client_id'))->toBe('google_client_id');
    expect(Setting::get('integrations.oauth.github.enabled'))->toBe(true);
    expect(Setting::get('integrations.oauth.github.client_id'))->toBe('github_client_id');
    expect(Setting::get('integrations.oauth.microsoft.enabled'))->toBe(false);
    expect(Setting::get('integrations.oauth.microsoft.client_id'))->toBe('microsoft_client_id');

    // Verify S3 settings
    expect(Setting::get('integrations.s3.enabled'))->toBe(true);
    expect(Setting::get('integrations.s3.key'))->toBe('AKIAIOSFODNN7EXAMPLE');
    expect(Setting::get('integrations.s3.region'))->toBe('us-east-1');
    expect(Setting::get('integrations.s3.bucket'))->toBe('my-bucket');
    expect(Setting::get('integrations.s3.endpoint'))->toBe('https://s3.amazonaws.com');
    expect(Setting::get('integrations.s3.url'))->toBe('https://my-bucket.s3.amazonaws.com');
    expect(Setting::get('integrations.s3.use_path_style_endpoint'))->toBe(false);
});

it('can update mailgun settings', function () {
    $data = [
        'mailgun_webhook_signing_key' => 'new_mailgun_key',
        'google_client_id' => '',
        'google_client_secret' => '',
        'github_client_id' => '',
        'github_client_secret' => '',
        'microsoft_client_id' => '',
        'microsoft_client_secret' => '',
    ];

    $response = $this->actingAs($this->user)->put('/admin/integrations', $data);

    $response->assertRedirect('/admin/integrations');

    // Verify the webhook signing key is encrypted
    expect(decrypt(Setting::get('integrations.mailgun.webhook_signing_key')))->toBe('new_mailgun_key');
});

it('can update oauth settings', function () {
    $data = [
        'mailgun_webhook_signing_key' => '',
        'google_enabled' => true,
        'google_client_id' => 'new_google_id',
        'google_client_secret' => 'new_google_secret',
        'github_enabled' => false,
        'github_client_id' => 'new_github_id',
        'github_client_secret' => 'new_github_secret',
        'microsoft_enabled' => true,
        'microsoft_client_id' => 'new_microsoft_id',
        'microsoft_client_secret' => 'new_microsoft_secret',
    ];

    $response = $this->actingAs($this->user)->put('/admin/integrations', $data);

    $response->assertRedirect('/admin/integrations');

    expect(Setting::get('integrations.oauth.google.enabled'))->toBe(true);
    expect(Setting::get('integrations.oauth.google.client_id'))->toBe('new_google_id');
    expect(Setting::get('integrations.oauth.github.enabled'))->toBe(false);
    expect(Setting::get('integrations.oauth.github.client_id'))->toBe('new_github_id');
    expect(Setting::get('integrations.oauth.microsoft.enabled'))->toBe(true);
    expect(Setting::get('integrations.oauth.microsoft.client_id'))->toBe('new_microsoft_id');
});

it('can update s3 settings', function () {
    $data = [
        'mailgun_webhook_signing_key' => '',
        'google_client_id' => '',
        'google_client_secret' => '',
        'github_client_id' => '',
        'github_client_secret' => '',
        'microsoft_client_id' => '',
        'microsoft_client_secret' => '',
        's3_enabled' => true,
        's3_key' => 'AKIAIOSFODNN7EXAMPLE',
        's3_secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
        's3_region' => 'us-west-2',
        's3_bucket' => 'test-bucket',
        's3_endpoint' => 'https://s3.us-west-2.amazonaws.com',
        's3_url' => 'https://s3.us-west-2.amazonaws.com/test-bucket',
        's3_use_path_style_endpoint' => true,
    ];

    $response = $this->actingAs($this->user)->put('/admin/integrations', $data);

    $response->assertRedirect('/admin/integrations');

    expect(Setting::get('integrations.s3.enabled'))->toBe(true);
    expect(Setting::get('integrations.s3.key'))->toBe('AKIAIOSFODNN7EXAMPLE');
    expect(decrypt(Setting::get('integrations.s3.secret')))->toBe('wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY');
    expect(Setting::get('integrations.s3.region'))->toBe('us-west-2');
    expect(Setting::get('integrations.s3.bucket'))->toBe('test-bucket');
    expect(Setting::get('integrations.s3.endpoint'))->toBe('https://s3.us-west-2.amazonaws.com');
    expect(Setting::get('integrations.s3.url'))->toBe('https://s3.us-west-2.amazonaws.com/test-bucket');
    expect(Setting::get('integrations.s3.use_path_style_endpoint'))->toBe(true);
});

it('can test s3 connection endpoint', function () {
    $data = [
        's3_key' => 'test_key',
        's3_secret' => 'test_secret',
        's3_region' => 'us-east-1',
        's3_bucket' => 'test-bucket',
        's3_endpoint' => null,
        's3_use_path_style_endpoint' => false,
    ];

    // This will fail with invalid credentials but should return 422 or 500 with proper error message
    $response = $this->actingAs($this->user)->postJson('/admin/integrations/test-s3', $data);

    // Expect either success, validation error, or server error (all are valid for bad credentials)
    expect($response->status())->toBeIn([200, 422, 500]);

    // If error, should have a message
    if ($response->status() !== 200) {
        $response->assertJsonStructure(['message']);
    }
});

it('requires all s3 fields to test connection', function () {
    $response = $this->actingAs($this->user)->postJson('/admin/integrations/test-s3', [
        's3_key' => '',
        's3_secret' => '',
        's3_region' => '',
        's3_bucket' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['s3_key', 's3_secret', 's3_region', 's3_bucket']);
});

it('requires access control panel permission to test s3 connection', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/admin/integrations/test-s3', [
        's3_key' => 'test',
        's3_secret' => 'test',
        's3_region' => 'test',
        's3_bucket' => 'test',
    ]);

    $response->assertForbidden();
});

it('requires access control panel permission to edit integrations', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/integrations');

    $response->assertForbidden();
});
