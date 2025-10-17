<?php

declare(strict_types=1);

namespace App\Support\Facades;

use App\Enums\Hooks as HooksEnum;
use App\Services\HookManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static HookManager register(HooksEnum $hook, \Closure|callable|\Illuminate\Contracts\Support\Htmlable|\Illuminate\Contracts\View\View|string $content, int $priority = 10)
 * @method static HookManager prepend(HooksEnum $hook, \Closure|callable|\Illuminate\Contracts\Support\Htmlable|\Illuminate\Contracts\View\View|string $content)
 * @method static HookManager append(HooksEnum $hook, \Closure|callable|\Illuminate\Contracts\Support\Htmlable|\Illuminate\Contracts\View\View|string $content)
 * @method static bool has(HooksEnum $hook)
 * @method static void clear(HooksEnum $hook)
 * @method static void flush()
 * @method static \Illuminate\Support\HtmlString renderHook(HooksEnum $hook, array $context = [])
 */
final class Hooks extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HookManager::class;
    }
}
