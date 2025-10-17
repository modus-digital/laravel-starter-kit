<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Hooks;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

final class HookManager
{
    /**
     * @var array<string, array<int, array{priority:int, content:mixed}>>
     */
    private array $hooks = [];

    public function register(Hooks $hook, Closure|callable|Htmlable|ViewContract|string $content, int $priority = 10): self
    {
        $key = $hook->value;
        $this->hooks[$key] ??= [];
        $this->hooks[$key][] = [
            'priority' => $priority,
            'content' => $content,
        ];

        return $this;
    }

    public function prepend(Hooks $hook, Closure|callable|Htmlable|ViewContract|string $content): self
    {
        return $this->register($hook, $content, PHP_INT_MIN);
    }

    public function append(Hooks $hook, Closure|callable|Htmlable|ViewContract|string $content): self
    {
        return $this->register($hook, $content, PHP_INT_MAX);
    }

    public function has(Hooks $hook): bool
    {
        $key = $hook->value;

        return isset($this->hooks[$key]) && $this->hooks[$key] !== [];
    }

    public function clear(Hooks $hook): void
    {
        unset($this->hooks[$hook->value]);
    }

    public function flush(): void
    {
        $this->hooks = [];
    }

    public function renderHook(Hooks $hook, array $context = []): HtmlString
    {
        if (! $this->has($hook)) {
            return new HtmlString('');
        }

        $items = (new Collection($this->hooks[$hook->value]))
            ->sortBy('priority')
            ->values();

        $output = '';

        foreach ($items as $item) {
            $output .= $this->evaluate($item['content'], $context);
        }

        return new HtmlString($output);
    }

    private function evaluate(Closure|callable|Htmlable|ViewContract|string $content, array $context = []): string
    {
        if ($content instanceof Htmlable) {
            return $content->toHtml();
        }

        if ($content instanceof ViewContract) {
            return $content->with($context)->render();
        }

        if ($content instanceof Closure || is_callable($content)) {
            $evaluated = $content($context);

            return $this->evaluate($evaluated, $context);
        }

        return (string) $content;
    }
}

/**
 * Usage:
 *
 * In the core application (rendering hooks):
 *
 * <x-layouts.guest>
 *
 *     @renderHook(App\Enums\Hooks::AUTH_LOGIN_FORM_BEFORE)
 *     <x-auth.login />
 *     @renderHook(App\Enums\Hooks::AUTH_LOGIN_FORM_AFTER)
 * </x-layouts.guest>
 *
 * In a plugin (registering content at hooks via Blade):
 *
 * @hook(App\Enums\Hooks::AUTH_LOGIN_FORM_BEFORE)
 *     <div class="alert">
 *         <h1>Before Auth Form</h1>
 *     </div>
 *
 * @endhook
 *
 * In a plugin service provider (registering content programmatically):
 *
 * use App\Support\Facades\Hooks;
 *
 * Hooks::register(
 *     Hooks::AUTH_LOGIN_FORM_BEFORE,
 *     '<div>Content</div>',
 *     10  // priority (optional, default: 10)
 * );
 */
