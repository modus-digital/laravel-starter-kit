<?php

use App\Livewire\Profile\Sessions\ShowBrowserSessions;
use App\Livewire\Profile\Sessions\ClearBrowserSessions;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    // Create and authenticate a user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    $this->actingAs($this->user);
});

test('clear browser sessions component renders correctly', function () {
    Livewire::test(ClearBrowserSessions::class)
        ->assertStatus(200)
        ->assertViewIs('livewire.profile.sessions.clear-browser-sessions')
        ->assertSee('password');
});

test('clear browser sessions validates password requirement', function () {
    Livewire::test(ClearBrowserSessions::class)
        ->call('clearBrowserSessions')
        ->assertHasErrors(['password' => 'required']);
});

test('clear browser sessions validates current password', function () {
    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'wrong-password')
        ->call('clearBrowserSessions')
        ->assertHasErrors(['password']);
});

test('clear browser sessions handles array driver without errors', function () {
    config(['session.driver' => 'array']);

    // Should complete without errors when using array driver
    Livewire::test(ClearBrowserSessions::class)
        ->set('password', 'password123')
        ->call('clearBrowserSessions')
        ->assertHasNoErrors();
});

test('livewire components can be instantiated', function () {
    // Basic instantiation test
    $showBrowserSessions = new ShowBrowserSessions();
    expect($showBrowserSessions)->toBeInstanceOf(ShowBrowserSessions::class);

    $clearBrowserSessions = new ClearBrowserSessions();
    expect($clearBrowserSessions)->toBeInstanceOf(ClearBrowserSessions::class);
});

test('clear browser sessions password field is reactive', function () {
    $component = Livewire::test(ClearBrowserSessions::class);

    // Test setting password field
    $component->set('password', 'test-password');
    expect($component->get('password'))->toBe('test-password');

    // Test clearing password field
    $component->set('password', '');
    expect($component->get('password'))->toBe('');
});

test('clear browser sessions password validation rule exists', function () {
    $component = Livewire::test(ClearBrowserSessions::class);

    // Test that password field starts empty
    expect($component->get('password'))->toBe('');

    // Test validation triggers on empty password
    $component->call('clearBrowserSessions')
        ->assertHasErrors(['password']);
});

test('clear browser sessions component methods exist', function () {
    $component = new ClearBrowserSessions();

    // Test that required methods exist
    expect(method_exists($component, 'clearBrowserSessions'))->toBeTrue();
    expect(method_exists($component, 'render'))->toBeTrue();
});

test('show browser sessions component methods exist', function () {
    $component = new ShowBrowserSessions();

    // Test that required methods exist
    expect(method_exists($component, 'mount'))->toBeTrue();
    expect(method_exists($component, 'render'))->toBeTrue();
});

test('show browser sessions component can be instantiated without errors', function () {
    // Just test that we can create the component without session store issues
    config(['session.driver' => 'array']);

    $component = new ShowBrowserSessions();
    expect($component)->toBeInstanceOf(ShowBrowserSessions::class);

    // Test that sessions property exists
    expect(property_exists($component, 'sessions'))->toBeTrue();
});

test('clear browser sessions uses correct validation rules', function () {
    $component = Livewire::test(ClearBrowserSessions::class);

    // Test required validation
    $component->call('clearBrowserSessions')
        ->assertHasErrors(['password' => 'required']);

    // Test current_password validation
    $component->set('password', 'invalid-password')
        ->call('clearBrowserSessions')
        ->assertHasErrors(['password']);
});

test('browser session components have proper livewire structure', function () {
    // Test ShowBrowserSessions structure
    $showComponent = new ShowBrowserSessions();
    expect($showComponent)->toBeInstanceOf(\Livewire\Component::class);

    // Test ClearBrowserSessions structure
    $clearComponent = new ClearBrowserSessions();
    expect($clearComponent)->toBeInstanceOf(\Livewire\Component::class);
});
