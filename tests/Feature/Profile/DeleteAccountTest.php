<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function () {
    Storage::fake('public');
});

test('user can delete account with valid password', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount')
        ->assertRedirect(route('login'));

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('password is required to delete account', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', '')
        ->call('deleteAccount')
        ->assertHasErrors(['delete_password' => 'required']);
});

test('password must match to delete account', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('CorrectPassword123!'),
    ]);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'WrongPassword123!')
        ->call('deleteAccount')
        ->assertHasErrors(['delete_password' => 'current_password']);
});

test('deletes user avatar from storage', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    // Create avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');
    $path = $avatar->store('avatars', 'public');
    $user->update(['avatar_path' => $path]);

    Storage::disk('public')->assertExists($path);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount');

    Storage::disk('public')->assertMissing($path);
});

test('deletes user settings', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    // Verify settings exist (created by HasSettings trait)
    expect($user->settings()->count())->toBeGreaterThan(0);

    $settingIds = $user->settings()->pluck('id')->toArray();

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount');

    foreach ($settingIds as $settingId) {
        assertDatabaseMissing('user_settings', ['id' => $settingId]);
    }
});

test('logs out user after deletion', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount');

    expect(auth()->check())->toBeFalse();
});

test('redirects to login page after deletion', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount')
        ->assertRedirect(route('login'));
});

test('delete account requires authentication', function () {
    Volt::test('user.profile-edit')
        ->assertUnauthorized();
});

test('handles account without avatar gracefully', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
        'avatar_path' => null,
    ]);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount')
        ->assertRedirect(route('login'));

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('only deletes authenticated users account', function () {
    /** @var User $user1 */
    $user1 = User::factory()->create([
        'password' => Hash::make('Password123!'),
    ]);
    /** @var User $user2 */
    $user2 = User::factory()->create();

    actingAs($user1);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount');

    assertDatabaseMissing('users', ['id' => $user1->id]);

    // User2 should still exist
    expect(User::find($user2->id))->not->toBeNull();
});

test('confirming account deletion toggle works', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    Volt::test('user.profile-edit')
        ->assertSet('confirming_account_deletion', false)
        ->call('$toggle', 'confirming_account_deletion')
        ->assertSet('confirming_account_deletion', true)
        ->call('$toggle', 'confirming_account_deletion')
        ->assertSet('confirming_account_deletion', false);
});

test('component mounts with user data', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    actingAs($user);

    $component = Volt::test('user.profile-edit');

    expect($component->get('user')->name)->toBe('Test User')
        ->and($component->get('user')->email)->toBe('test@example.com');
});

test('deletes avatar even if storage path does not exist', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'password' => Hash::make('Password123!'),
        'avatar_path' => 'avatars/nonexistent.jpg',
    ]);

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount')
        ->assertRedirect(route('login'));

    assertDatabaseMissing('users', ['id' => $user->id]);
});

test('account deletion is permanent', function () {
    /** @var User $user */
    $user = User::factory()->create([
        'email' => 'permanent@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $userId = $user->id;

    actingAs($user);

    Volt::test('user.profile-edit')
        ->set('delete_password', 'Password123!')
        ->call('deleteAccount');

    // Verify user is completely removed
    expect(User::find($userId))->toBeNull();
    assertDatabaseMissing('users', ['email' => 'permanent@example.com']);
});

