@php
    $user = auth()->user();
    $hasAvatar = $user?->avatar_url ?? false;

    $sizeClasses = $size ?? 'w-16 h-16';
    $widthClass = null;
    foreach (explode(' ', $sizeClasses) as $c) {
        if (strpos($c, 'w-') === 0) { $widthClass = $c; break; }
    }
    $widthNum = $widthClass ? (int) preg_replace('/\D/', '', $widthClass) : 16;

    $fontSizeClass = 'text-xs';
    if ($widthNum >= 11 && $widthNum <= 12) { $fontSizeClass = 'text-base'; }
    elseif ($widthNum >= 13 && $widthNum <= 16) { $fontSizeClass = 'text-xl'; }
    elseif ($widthNum >= 17 && $widthNum <= 20) { $fontSizeClass = 'text-2xl'; }
    elseif ($widthNum >= 21 && $widthNum <= 24) { $fontSizeClass = 'text-3xl'; }
    elseif ($widthNum >= 25 && $widthNum <= 32) { $fontSizeClass = 'text-4xl'; }
    elseif ($widthNum > 32) { $fontSizeClass = 'text-5xl'; }
@endphp

<div class="flex-shrink-0" @avatar-updated.window="$wire.$refresh()">
    @if (! $editable)
        @if ($hasAvatar)
            <img
                src="{{ $user?->avatar_url }}"
                alt="{{ $user?->name ?? 'User' }}"
                class="{{ $size }} rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700"
            >
        @else
            <div class="{{ $size }} rounded-full ring-2 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-800 grid place-items-center">
                <span class="text-zinc-600 dark:text-zinc-300 font-medium {{ $fontSizeClass }} leading-none select-none">
                    {{ $user?->initials() }}
                </span>
            </div>
        @endif

    @else

    <button type="button" class="relative group cursor-pointer">
        @if ($hasAvatar)
            <img
            src="{{ $user?->avatar_url }}"
            alt="{{ $user?->name ?? __('user.avatar.default_alt') }}"
            class="{{ $size }} rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700">
        @else
            <div class="{{ $size }} rounded-full ring-2 ring-zinc-200 dark:ring-zinc-700 bg-zinc-100 dark:bg-zinc-800 grid place-items-center">
                <span class="text-zinc-600 dark:text-zinc-300 font-medium {{ $fontSizeClass }} leading-none select-none">
                    {{ $user?->initials() ?? __('user.avatar.default_alt') }}
                </span>
            </div>
        @endif

            <div @click="Livewire.dispatch('open-modal', { name: 'change-avatar' })" class="absolute inset-0 rounded-full bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="text-white text-xs font-medium text-center px-2">{{ __('user.avatar.change') }}</span>
            </div>
        </button>

        <x-modal name="change-avatar" title="{{ __('user.avatar.modal_title') }}">
            <livewire:user.change-avatar />
        </x-modal>
    @endif
</div>
