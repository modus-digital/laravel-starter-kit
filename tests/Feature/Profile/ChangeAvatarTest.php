<?php

declare(strict_types=1);

use App\Livewire\User\ChangeAvatar;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Storage::fake('public');
});

test('user can upload avatar', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

    Livewire::test(ChangeAvatar::class)
        ->set('avatar', $file)
        ->call('save')
        ->assertDispatched('avatar-updated')
        ->assertDispatched('close-modal', name: 'change-avatar');

    $user->refresh();

    expect($user->avatar_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('user can replace existing avatar', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Create existing avatar
    $oldAvatar = UploadedFile::fake()->image('old-avatar.jpg');
    $oldPath = $oldAvatar->store('avatars', 'public');
    $user->update(['avatar_path' => $oldPath]);

    actingAs($user);

    $newFile = UploadedFile::fake()->image('new-avatar.jpg');

    Livewire::test(ChangeAvatar::class)
        ->set('avatar', $newFile)
        ->call('save')
        ->assertDispatched('avatar-updated');

    $user->refresh();

    // Old avatar should be deleted
    Storage::disk('public')->assertMissing($oldPath);

    // New avatar should exist
    expect($user->avatar_path)->not->toBe($oldPath);
    Storage::disk('public')->assertExists($user->avatar_path);
});

test('user can remove avatar', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Create existing avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');
    $path = $avatar->store('avatars', 'public');
    $user->update(['avatar_path' => $path]);

    actingAs($user);

    Livewire::test(ChangeAvatar::class)
        ->call('removeAvatar')
        ->assertDispatched('avatar-updated')
        ->assertDispatched('close-modal', name: 'change-avatar');

    $user->refresh();

    expect($user->avatar_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});

test('avatar must be an image', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    Livewire::test(ChangeAvatar::class)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'image']);
});

test('avatar must not exceed 5MB', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $file = UploadedFile::fake()->image('large-avatar.jpg')->size(5121); // 5121 KB > 5MB

    Livewire::test(ChangeAvatar::class)
        ->set('avatar', $file)
        ->call('save')
        ->assertHasErrors(['avatar' => 'max']);
});

test('avatar validation runs on update', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $file = UploadedFile::fake()->create('document.pdf', 100);

    Livewire::test(ChangeAvatar::class)
        ->set('avatar', $file)
        ->assertHasErrors(['avatar' => 'image']);
});

test('component mounts with current avatar', function () {
    /** @var User $user */
    $user = User::factory()->create();

    // Create existing avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');
    $path = $avatar->store('avatars', 'public');
    $user->update(['avatar_path' => $path]);

    actingAs($user);

    Livewire::test(ChangeAvatar::class)
        ->assertSet('currentAvatar', Storage::disk('public')->url($path));
});

test('change avatar requires authentication', function () {
    Livewire::test(ChangeAvatar::class)
        ->assertUnauthorized();
});

test('remove avatar only affects user with avatar', function () {
    /** @var User $user */
    $user = User::factory()->create(['avatar_path' => null]);

    actingAs($user);

    Livewire::test(ChangeAvatar::class)
        ->call('removeAvatar')
        ->assertDispatched('avatar-updated');

    $user->refresh();

    expect($user->avatar_path)->toBeNull();
});

test('avatar resets after successful save', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user);

    $file = UploadedFile::fake()->image('avatar.jpg');

    Livewire::test(ChangeAvatar::class)
        ->set('avatar', $file)
        ->call('save')
        ->assertSet('avatar', null);
});

