<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::MANAGE_SETTINGS);
});

it('can view mailgun analytics dashboard', function () {
    $response = $this->actingAs($this->user)->get('/admin/mailgun');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('admin/mailgun/index')
            ->has('stats')
            ->has('recentMessages')
            ->has('eventBreakdown')
        );
});

it('displays correct email statistics', function () {
    EmailMessage::factory()->count(5)->create();
    EmailEvent::factory()->count(3)->create(['event' => 'delivered']);
    EmailEvent::factory()->count(2)->create(['event' => 'opened']);

    $response = $this->actingAs($this->user)->get('/admin/mailgun');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total_sent', 5)
            ->where('stats.total_delivered', 3)
            ->where('stats.total_opened', 2)
        );
});

it('displays recent messages', function () {
    EmailMessage::factory()->count(15)->create();

    $response = $this->actingAs($this->user)->get('/admin/mailgun');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('recentMessages', 10) // Should only show 10 most recent
        );
});

it('displays event breakdown', function () {
    EmailEvent::factory()->count(5)->create(['event' => 'delivered']);
    EmailEvent::factory()->count(3)->create(['event' => 'opened']);
    EmailEvent::factory()->count(2)->create(['event' => 'bounced']);

    $response = $this->actingAs($this->user)->get('/admin/mailgun');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('eventBreakdown')
        );
});

it('handles empty state gracefully', function () {
    $response = $this->actingAs($this->user)->get('/admin/mailgun');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->where('stats.total_sent', 0)
            ->where('stats.total_delivered', 0)
        );
});

it('requires manage settings permission to view mailgun analytics', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/mailgun');

    $response->assertForbidden();
});
