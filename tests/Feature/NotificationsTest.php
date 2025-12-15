<?php

declare(strict_types=1);

use App\Enums\RBAC\Permission;
use App\Models\User;
use App\Notifications\SimpleDatabaseNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission as SpatiePermission;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    foreach (Permission::cases() as $permission) {
        SpatiePermission::firstOrCreate(
            ['name' => $permission->value, 'guard_name' => 'web']
        );
    }
});

it('shows notifications for the authenticated user', function (): void {
    $user = User::factory()->create();
    $user->notify(new SimpleDatabaseNotification(title: 'Welcome', body: 'Thanks for joining!'));

    $this->actingAs($user);

    $response = $this->get('/notifications');

    $response->assertOk()
        ->assertSee('Welcome');
});

it('shares unread notifications count with inertia views', function (): void {
    $user = User::factory()->create();
    $user->notify(new SimpleDatabaseNotification(title: 'New notification'));

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('unreadNotificationsCount', 1)
        );
});

it('marks notifications as read and unread', function (): void {
    $user = User::factory()->create();
    $user->notify(new SimpleDatabaseNotification(title: 'Action needed'));
    $notification = $user->notifications()->first();
    assert($notification !== null);

    $this->actingAs($user);

    $this->post("/notifications/{$notification->id}/read")
        ->assertRedirect();

    expect($notification->fresh()->read_at)->not->toBeNull();

    $this->post("/notifications/{$notification->id}/unread")
        ->assertRedirect();

    expect($notification->fresh()->read_at)->toBeNull();
});

it('filters read notifications when tab is read', function (): void {
    $user = User::factory()->create();
    $user->notify(new SimpleDatabaseNotification(title: 'Read one'));
    $user->notify(new SimpleDatabaseNotification(title: 'Unread one'));

    $readNotification = $user->notifications()->first();
    assert($readNotification !== null);

    $readNotification->markAsRead();

    $this->actingAs($user);

    $response = $this->get('/notifications?tab=read');

    $response->assertOk();
    $response->assertSee('Read one');
    $response->assertDontSee('Unread one');
});

it('bulk marks notifications as read and unread', function (): void {
    $user = User::factory()->create();
    $user->notify(new SimpleDatabaseNotification(title: 'First'));
    $user->notify(new SimpleDatabaseNotification(title: 'Second'));

    $notifications = $user->notifications()->get();
    expect($notifications)->toHaveCount(2);

    $this->actingAs($user);

    $this->post('/notifications/bulk/read', [
        'ids' => $notifications->pluck('id')->all(),
    ])->assertRedirect();

    expect($user->fresh()->unreadNotifications)->toHaveCount(0);

    $this->post('/notifications/bulk/unread', [
        'ids' => $notifications->pluck('id')->all(),
    ])->assertRedirect();

    expect($user->fresh()->unreadNotifications)->toHaveCount(2);
});

it('prevents accessing another users notification', function (): void {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();

    $owner->notify(new SimpleDatabaseNotification(title: 'Private'));
    $notification = $owner->notifications()->first();
    assert($notification !== null);

    $this->actingAs($intruder);

    $this->post("/notifications/{$notification->id}/read")
        ->assertForbidden();
});

it('clears all notifications for the user', function (): void {
    $user = User::factory()->create();
    $user->notify(new SimpleDatabaseNotification(title: 'First'));
    $user->notify(new SimpleDatabaseNotification(title: 'Second'));

    $this->actingAs($user);

    $this->delete('/notifications')
        ->assertRedirect();

    expect($user->notifications()->count())->toBe(0);
});

it('allows Filament users to access the notifications page', function (): void {
    $user = User::factory()->create();
    $permission = SpatiePermission::firstOrCreate(
        ['name' => Permission::ACCESS_CONTROL_PANEL->value, 'guard_name' => 'web']
    );
    $user->givePermissionTo($permission);

    $this->actingAs($user);

    $this->get(route('filament.control.pages.notifications'))
        ->assertOk();
});
