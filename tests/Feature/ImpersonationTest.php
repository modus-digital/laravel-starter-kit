<?php

declare(strict_types=1);

use App\Enums\ActivityStatus;
use App\Enums\RBAC\Permission;
use App\Enums\RBAC\Role;
use App\Filament\Resources\Core\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\post;
use function Pest\Livewire\livewire;

beforeEach(function () {
    // Seed roles and permissions
    $this->artisan('db:seed', ['--class' => 'Database\\Seeders\\RolesAndPermissionsSeeder']);

    // Create test users with different roles
    $this->adminUser = User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    $this->adminUser->assignRole(Role::ADMIN);

    $this->regularUser = User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    $this->regularUser->assignRole(Role::USER);

    $this->inactiveUser = User::factory()->create(['status' => ActivityStatus::INACTIVE]);
    $this->inactiveUser->assignRole(Role::USER);

    $this->superAdmin = User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    $this->superAdmin->assignRole(Role::SUPER_ADMIN);

    $this->userWithoutPermission = User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    $this->userWithoutPermission->assignRole(Role::USER);
});

// ==================== VISIBILITY TESTS ====================

test('impersonation action is enabled for admin user impersonating regular user', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionEnabled(TestAction::make('impersonate-action')->table($this->regularUser));
});

test('impersonation action is disabled for user trying to impersonate themselves', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($this->adminUser));
});

test('impersonation action is disabled for inactive user', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($this->inactiveUser));
});

test('impersonation action is disabled when user lacks impersonate permission', function () {
    actingAs($this->userWithoutPermission);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($this->regularUser));
});

test('impersonation action is disabled for admin trying to impersonate super admin', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($this->superAdmin));
});

test('impersonation action is disabled for admin trying to impersonate another admin', function () {
    $anotherAdmin = User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    $anotherAdmin->assignRole(Role::ADMIN);

    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($anotherAdmin));
});

// ==================== STARTING IMPERSONATION TESTS ====================

test('admin can start impersonating a regular user', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->callAction(TestAction::make('impersonate-action')->table($this->regularUser));

    // Assert the user is now authenticated as the target user
    expect(Auth::id())->toBe($this->regularUser->id);
});

test('impersonation session data is set correctly when starting impersonation', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->callAction(TestAction::make('impersonate-action')->table($this->regularUser));

    // Assert session has correct impersonation data
    expect(session('impersonation.is_impersonating'))->toBeTrue()
        ->and(session('impersonation.original_user_id'))->toBe($this->adminUser->id)
        ->and(session('impersonation.can_bypass_2fa'))->toBeTrue()
        ->and(session('impersonation.return_url'))->not->toBeNull();
});

test('impersonation start is logged in activity log', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->callAction(TestAction::make('impersonate-action')->table($this->regularUser));

    // Assert activity log was created
    $activity = Activity::where('log_name', 'impersonation')
        ->where('event', 'impersonate.start')
        ->where('subject_id', $this->regularUser->id)
        ->where('causer_id', $this->adminUser->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('User impersonation started')
        ->and($activity->properties->get('issuer'))->toBe($this->adminUser->name)
        ->and($activity->properties->get('target'))->toBe($this->regularUser->name)
        ->and($activity->properties->get('ip'))->not->toBeNull()
        ->and($activity->properties->get('user_agent'))->not->toBeNull();
});

test('user without permission cannot start impersonation', function () {
    actingAs($this->userWithoutPermission);

    // Action should be disabled, so calling it should not change auth or session
    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($this->regularUser));

    expect(Auth::id())->toBe($this->userWithoutPermission->id)
        ->and(session()->has('impersonation'))->toBeFalse();
});

// ==================== STOPPING IMPERSONATION TESTS ====================

test('can stop impersonation and return to original user', function () {
    // Start impersonation
    actingAs($this->adminUser);

    session()->put('impersonation', [
        'is_impersonating' => true,
        'original_user_id' => $this->adminUser->id,
        'return_url' => route('dashboard'),
        'can_bypass_2fa' => true,
    ]);

    Auth::login($this->regularUser);

    // Stop impersonation
    $response = post(route('impersonate.leave'));

    // Assert redirected back to original user
    assertAuthenticated();
    expect(Auth::id())->toBe($this->adminUser->id)
        ->and(session()->has('impersonation'))->toBeFalse();
});

test('impersonation session is cleared when stopping impersonation', function () {
    // Start impersonation
    actingAs($this->adminUser);

    session()->put('impersonation', [
        'is_impersonating' => true,
        'original_user_id' => $this->adminUser->id,
        'return_url' => route('dashboard'),
        'can_bypass_2fa' => true,
    ]);

    Auth::login($this->regularUser);

    // Stop impersonation
    post(route('impersonate.leave'));

    // Assert session data is cleared
    expect(session()->has('impersonation'))->toBeFalse()
        ->and(session()->get('impersonation.is_impersonating'))->toBeNull()
        ->and(session()->get('impersonation.original_user_id'))->toBeNull();
});

test('impersonation stop is logged in activity log', function () {
    // Start impersonation
    actingAs($this->adminUser);

    session()->put('impersonation', [
        'is_impersonating' => true,
        'original_user_id' => $this->adminUser->id,
        'return_url' => route('dashboard'),
        'can_bypass_2fa' => true,
    ]);

    Auth::login($this->regularUser);

    // Stop impersonation
    post(route('impersonate.leave'));

    // Assert activity log was created
    $activity = Activity::where('log_name', 'impersonation')
        ->where('event', 'impersonate.leave')
        ->where('subject_id', $this->regularUser->id)
        ->where('causer_id', $this->adminUser->id)
        ->latest()
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->description)->toBe('User impersonation ended')
        ->and($activity->properties->get('issuer'))->toBe($this->adminUser->name)
        ->and($activity->properties->get('target'))->toBe($this->regularUser->name)
        ->and($activity->properties->get('ip_address'))->not->toBeNull()
        ->and($activity->properties->get('user_agent'))->not->toBeNull();
});

test('leaving impersonation without active session redirects to login', function () {
    actingAs($this->regularUser);

    // Try to leave impersonation without having started one
    $response = post(route('impersonate.leave'));

    $response->assertRedirect(route('login'));
});

// ==================== AUTHORIZATION TESTS ====================

test('super admin can impersonate regular users', function () {
    actingAs($this->superAdmin);

    livewire(ListUsers::class)
        ->assertActionEnabled(TestAction::make('impersonate-action')->table($this->regularUser));
});

test('super admin cannot impersonate themselves', function () {
    actingAs($this->superAdmin);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($this->superAdmin));
});

test('regular user cannot impersonate anyone', function () {
    actingAs($this->regularUser);

    $anotherUser = User::factory()->create(['status' => ActivityStatus::ACTIVE]);
    $anotherUser->assignRole(Role::USER);

    livewire(ListUsers::class)
        ->assertActionDisabled(TestAction::make('impersonate-action')->table($anotherUser));

    expect($this->regularUser->hasPermissionTo(Permission::IMPERSONATE_USERS))->toBeFalse();
});

test('impersonation action exists in users table', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionExists(TestAction::make('impersonate-action')->table($this->regularUser));
});

test('impersonation action is visible to users with permission', function () {
    actingAs($this->adminUser);

    livewire(ListUsers::class)
        ->assertActionVisible(TestAction::make('impersonate-action')->table($this->regularUser));
});
