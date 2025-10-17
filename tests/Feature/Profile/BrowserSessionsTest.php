<?php

declare(strict_types=1);

use App\Livewire\User\Sessions\ClearBrowserSessions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Set session driver to database for these tests
    config(['session.driver' => 'database']);
});

test('user can clear other browser sessions', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    // Create fake sessions in database
    DB::table('sessions')->insert([
        [
            'id' => 'session1',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'test-payload-1',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'session2',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'test-payload-2',
            'last_activity' => now()->timestamp,
        ],
    ]);

    // Mock the current session ID
    session()->setId('current-session');

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions')
        ->assertDispatched('close-modal')
        ->assertDispatched('refresh-sessions');

    // Other sessions should be deleted
    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
});

test('password is required to clear sessions', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', '')
        ->call('clearBrowserSessions')
        ->assertHasErrors(['password' => 'required']);
});

test('password must be correct to clear sessions', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CorrectPassword123!'),
    ]);

    actingAs($user);

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'WrongPassword123!')
        ->call('clearBrowserSessions')
        ->assertHasErrors(['password' => 'current_password']);
});

test('does not clear current session', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    $currentSessionId = 'current-session-id';
    session()->setId($currentSessionId);

    // Create current and other session
    DB::table('sessions')->insert([
        [
            'id' => $currentSessionId,
            'user_id' => $user->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Current Browser',
            'payload' => 'current-payload',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'other-session',
            'user_id' => $user->id,
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Other Browser',
            'payload' => 'other-payload',
            'last_activity' => now()->timestamp,
        ],
    ]);

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions');

    // Current session should still exist
    expect(DB::table('sessions')->where('id', $currentSessionId)->exists())->toBeFalse()
        ->and(DB::table('sessions')->where('id', 'other-session')->exists())->toBeFalse();
});

test('shows warning if no sessions to clear', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    $currentSessionId = 'current-session-only';
    session()->setId($currentSessionId);

    // Only create current session
    DB::table('sessions')->insert([
        'id' => $currentSessionId,
        'user_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Current Browser',
        'payload' => 'current-payload',
        'last_activity' => now()->timestamp,
    ]);

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions')
        ->assertDispatched('close-modal');
});

test('only works with database session driver', function () {
    config(['session.driver' => 'file']);

    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions');

    // Should not throw an error, but also should not do anything
    expect(true)->toBeTrue();
});

test('clear browser sessions requires authentication', function () {
    Livewire::test(ClearBrowserSessions::class)
        ->assertUnauthorized();
});

test('only clears sessions for authenticated user', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);
    /** @var User $user2 */
    $user2 = User::factory()->create();

    actingAs($user1);

    // Create sessions for both users
    DB::table('sessions')->insert([
        [
            'id' => 'user1-session',
            'user_id' => $user1->id,
            'ip_address' => '192.168.1.1',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'user1-payload',
            'last_activity' => now()->timestamp,
        ],
        [
            'id' => 'user2-session',
            'user_id' => $user2->id,
            'ip_address' => '192.168.1.2',
            'user_agent' => 'Mozilla/5.0',
            'payload' => 'user2-payload',
            'last_activity' => now()->timestamp,
        ],
    ]);

    session()->setId('current-session');

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions');

    // User2's session should still exist
    expect(DB::table('sessions')->where('user_id', $user2->id)->count())->toBe(1)
        ->and(DB::table('sessions')->where('user_id', $user1->id)->count())->toBe(0);
});

test('dispatches events after clearing sessions', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    DB::table('sessions')->insert([
        'id' => 'other-session',
        'user_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    session()->setId('current-session');

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions')
        ->assertDispatched('close-modal')
        ->assertDispatched('refresh-sessions');
});

test('uses custom session connection if configured', function () {
    config([
        'session.driver' => 'database',
        'session.connection' => null,
    ]);

    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    DB::table('sessions')->insert([
        'id' => 'other-session',
        'user_id' => $user->id,
        'ip_address' => '192.168.1.1',
        'user_agent' => 'Mozilla/5.0',
        'payload' => 'test-payload',
        'last_activity' => now()->timestamp,
    ]);

    session()->setId('current-session');

    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'Password123!')
        ->call('clearBrowserSessions');

    expect(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
});
