@props([
    "href" => "#",
    "active" => false,
    "icon" => null,
    "label" => null,
    "children" => [],
])

<li class="w-full">
    @if (count($children) > 0)
        <button
            type="button"
            class="group flex w-full items-center rounded-lg p-2 text-base font-medium text-zinc-900 transition duration-75 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700"
            aria-controls="dropdown-{{ Str::slug($label) }}"
            data-collapse-toggle="dropdown-{{ Str::slug($label) }}"
        >
            @isset($icon)
                <x-dynamic-component :component="$icon" class="h-6 w-6" />
            @endisset

            <span class="ml-3 flex-1 text-left whitespace-nowrap">
                {{ $label }}
            </span>
        </button>

        <ul
            id="dropdown-{{ Str::slug($label) }}"
            class="hidden space-y-2 py-2"
        >
            @foreach ($children as $child)
                <li>
                    <a
                        href="{{ $child["href"] }}"
                        class="group flex w-full items-center rounded-lg p-2 pl-11 text-base font-medium text-zinc-900 transition duration-75 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700"
                    >
                        {{ $child["label"] ?? $slot }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <a
            href="{{ $href }}"
            @class([
                "group flex items-center rounded-lg p-2 text-base font-medium text-zinc-900 hover:bg-zinc-100 dark:text-white dark:hover:bg-zinc-700 dark:hover:text-white",
                "bg-zinc-100 dark:bg-zinc-700 dark:text-white dark:hover:bg-zinc-700 dark:hover:text-white" => $active,
            ])
        >
            @isset($icon)
                {{ $icon }}
            @endisset

            <span class="ml-3">{{ $label ?? $slot }}</span>
        </a>
    @endif
</li>
