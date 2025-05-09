@props([
    'name' => Str::uuid()->toString(),
    'title' => null,
    'description' => null,
])

<!-- Modal Container -->
<div
    x-data="{ show: false, name: '{{ $name }}' }"
    x-on:open-modal.window="$event.detail.name === name && (show = true)"
    x-on:close-modal.window="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-transition.opacity
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center"
>

    <!-- Modal Backdrop -->
    <div x-on:click="show = false" class="fixed inset-0 bg-black/75 dark:bg-gray-900/80 transition-opacity"></div>

    <!-- Modal Content -->
    <div
        class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-auto"
        x-on:click.stop
    >
        <!-- Modal Header -->
        @if($title || $description)
            <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                @if($title)
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $title }}
                    </h3>
                @endif

                @if($description)
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        {{ $description }}
                    </p>
                @endif
            </div>
        @endif

        <!-- Modal Content -->
        <div class="p-4 text-gray-800 dark:text-gray-200">
            {{ $slot }}
        </div>

        <!-- Modal Footer -->
        @if(isset($footer))
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                {{ $footer }}
            </div>
        @endif

        <!-- Close Button -->
        <button
            type="button"
            class="absolute top-3 right-3 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white rounded-lg text-sm p-1.5 inline-flex items-center transition-colors"
            x-on:click="show = false"
            aria-label="Close modal"
        >
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            <span class="sr-only">Close modal</span>
        </button>
    </div>

</div>
