@props([
    'name' => Str::uuid()->toString(),
    'title' => null,
    'description' => null,
    'position' => 'right', // right, left, top, bottom
])

@php
    $positionClasses = [
        'right' => 'justify-end items-center',
        'left' => 'justify-start items-center',
        'top' => 'justify-center items-start',
        'bottom' => 'justify-center items-end',
    ];

    $drawerClasses = [
        'right' => 'h-full w-1/3',
        'left' => 'h-full w-1/3',
        'top' => 'w-full h-1/3',
        'bottom' => 'w-full h-1/3',
    ];

    $transitionClasses = [
        'right' => [
            'enter' => 'translate-x-full',
            'enter-end' => 'translate-x-0',
            'leave' => 'translate-x-0',
            'leave-end' => 'translate-x-full',
        ],
        'left' => [
            'enter' => '-translate-x-full',
            'enter-end' => 'translate-x-0',
            'leave' => 'translate-x-0',
            'leave-end' => '-translate-x-full',
        ],
        'top' => [
            'enter' => '-translate-y-full',
            'enter-end' => 'translate-y-0',
            'leave' => 'translate-y-0',
            'leave-end' => '-translate-y-full',
        ],
        'bottom' => [
            'enter' => 'translate-y-full',
            'enter-end' => 'translate-y-0',
            'leave' => 'translate-y-0',
            'leave-end' => 'translate-y-full',
        ],
    ];
@endphp

<!-- Drawer Container -->
<div
    x-data="{
        show: false,
        name: '{{ $name }}',
        title: '{{ $title }}',
        setTitle(newTitle) {
            this.title = newTitle;
        }
    }"
    x-on:open-drawer.window="$event.detail.name === name && (show = true, $event.detail.title && setTitle($event.detail.title))"
    x-on:close-drawer.window="show = false"
    x-on:keydown.escape.window="show = false; $dispatch('close-drawer')"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-cloak
    class="fixed inset-0 z-50 flex {{ $positionClasses[$position] }}"
>
    <!-- Drawer Backdrop -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click="show = false; $dispatch('close-drawer')"
        class="fixed inset-0 bg-black/50 dark:bg-zinc-900/65"
    ></div>

    <!-- Drawer Content -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="{{ $transitionClasses[$position]['enter'] }}"
        x-transition:enter-end="{{ $transitionClasses[$position]['enter-end'] }}"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="{{ $transitionClasses[$position]['leave'] }}"
        x-transition:leave-end="{{ $transitionClasses[$position]['leave-end'] }}"
        class="relative bg-white dark:bg-zinc-800 rounded shadow-xl {{ $drawerClasses[$position] }} overflow-hidden flex flex-col"
        x-on:click.stop
    >
        <!-- Drawer Header -->
        @if($title || $description)
            <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                @if($title)
                    <h3
                        class="text-lg font-semibold uppercase tracking-wider text-zinc-600 dark:text-zinc-200"
                        x-text="title"
                    ></h3>
                @endif

                @if($description)
                    <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-300">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Drawer Content -->
        <div class="flex-1 overflow-hidden text-zinc-800 dark:text-zinc-200">
            {{ $slot }}
        </div>

        <!-- Close Button -->
        <button
            type="button"
            class="absolute top-3 right-3 text-zinc-400 bg-transparent hover:bg-zinc-200 hover:text-zinc-900 dark:hover:bg-zinc-700 dark:hover:text-white rounded-lg text-sm p-1.5 inline-flex items-center transition-colors"
            x-on:click="show = false; $dispatch('close-drawer')"
            aria-label="{{ __('common.close') }}"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">{{ __('common.close') }}</span>
        </button>
    </div>
</div>
