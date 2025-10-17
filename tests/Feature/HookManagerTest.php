<?php

declare(strict_types=1);

use App\Enums\Hooks;
use App\Services\HookManager;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

test('can register and render hook content', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Test Content</div>');

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output)->toBeInstanceOf(HtmlString::class)
        ->and($output->toHtml())->toBe('<div>Test Content</div>');
});

test('renders multiple hooks in priority order', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Third</div>', 30);
    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>First</div>', 10);
    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Second</div>', 20);

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output->toHtml())->toBe('<div>First</div><div>Second</div><div>Third</div>');
});

test('prepend adds content with lowest priority', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Middle</div>', 10);
    $manager->prepend(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>First</div>');
    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Last</div>', 20);

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output->toHtml())->toBe('<div>First</div><div>Middle</div><div>Last</div>');
});

test('append adds content with highest priority', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>First</div>', 10);
    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Middle</div>', 20);
    $manager->append(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Last</div>');

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output->toHtml())->toBe('<div>First</div><div>Middle</div><div>Last</div>');
});

test('has returns true when hook has content', function () {
    $manager = new HookManager();

    expect($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeFalse();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Content</div>');

    expect($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeTrue();
});

test('renders empty string when hook has no content', function () {
    $manager = new HookManager();

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output->toHtml())->toBe('');
});

test('can register closure content', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, fn () => '<div>From Closure</div>');

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output->toHtml())->toBe('<div>From Closure</div>');
});

test('passes context to closures', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, fn ($context) => '<div>Hello '.$context['name'].'</div>');

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE, ['name' => 'World']);

    expect($output->toHtml())->toBe('<div>Hello World</div>');
});

test('can register view content', function () {
    $manager = new HookManager();

    $view = view('welcome');
    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, $view);

    $output = $manager->renderHook(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($output->toHtml())->toContain('Laravel');
});

test('can clear specific hook', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Content</div>');
    $manager->register(Hooks::AUTH_LOGIN_FORM_AFTER, '<div>Other Content</div>');

    expect($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeTrue();

    $manager->clear(Hooks::AUTH_LOGIN_FORM_BEFORE);

    expect($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeFalse()
        ->and($manager->has(Hooks::AUTH_LOGIN_FORM_AFTER))->toBeTrue();
});

test('can flush all hooks', function () {
    $manager = new HookManager();

    $manager->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Content</div>');
    $manager->register(Hooks::AUTH_LOGIN_FORM_AFTER, '<div>Other Content</div>');

    expect($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeTrue()
        ->and($manager->has(Hooks::AUTH_LOGIN_FORM_AFTER))->toBeTrue();

    $manager->flush();

    expect($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeFalse()
        ->and($manager->has(Hooks::AUTH_LOGIN_FORM_AFTER))->toBeFalse();
});

test('renderHook blade directive is registered', function () {
    $directives = Blade::getCustomDirectives();

    expect($directives)->toHaveKey('renderHook');
});

test('hook blade directive is registered', function () {
    $directives = Blade::getCustomDirectives();

    expect($directives)->toHaveKey('hook')
        ->and($directives)->toHaveKey('endhook');
});

test('can chain register methods', function () {
    $manager = new HookManager();

    $result = $manager
        ->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>First</div>')
        ->register(Hooks::AUTH_LOGIN_FORM_BEFORE, '<div>Second</div>')
        ->register(Hooks::AUTH_LOGIN_FORM_AFTER, '<div>Third</div>');

    expect($result)->toBeInstanceOf(HookManager::class)
        ->and($manager->has(Hooks::AUTH_LOGIN_FORM_BEFORE))->toBeTrue()
        ->and($manager->has(Hooks::AUTH_LOGIN_FORM_AFTER))->toBeTrue();
});
