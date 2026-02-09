<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\Modules\Mailgun\EmailEvent;
use App\Models\Modules\Mailgun\EmailMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create permissions
    foreach (Permission::cases() as $permission) {
        if ($permission->shouldSync()) {
            Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission->value]);
        }
    }

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::AccessControlPanel);
});

it('can view mailgun analytics dashboard', function () {
    $response = $this->actingAs($this->user)->get('/admin/mailgun');

    $response->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->component('core/admin/mailgun/index')
            ->has('stats')
            ->has('recentMessages')
            ->has('eventBreakdown')
        );
});

it('displays correct email statistics', function () {
    // Create 5 email messages
    $messages = EmailMessage::factory()->count(5)->create();

    // Create events for 3 delivered (using first 3 messages)
    EmailEvent::factory()->count(3)->create([
        'event_type' => 'delivered',
        'email_message_id' => fn () => $messages->random()->id,
    ]);

    // Create events for 2 opened (using first 2 messages)
    EmailEvent::factory()->count(2)->create([
        'event_type' => 'opened',
        'email_message_id' => fn () => $messages->random()->id,
    ]);

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
    EmailEvent::factory()->count(5)->create(['event_type' => 'delivered']);
    EmailEvent::factory()->count(3)->create(['event_type' => 'opened']);
    EmailEvent::factory()->count(2)->create(['event_type' => 'failed']);

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

it('requires access control panel permission to view mailgun analytics', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/admin/mailgun');

    $response->assertForbidden();
});
