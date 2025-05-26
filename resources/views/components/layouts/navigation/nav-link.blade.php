@props([
    'href' => '#',
    'icon' => null,
    'active' => false,
    'children' => [],
    'label' => null,
])

<li class="w-full">
    @if (count($children) > 0)
        <button
            type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-{{ Str::slug($label) }}"
            data-collapse-toggle="dropdown-{{ Str::slug($label) }}"
            wire:navigate.hover
        >
            @isset($icon)
                {{  $icon }}
            @endisset

            <span class="flex-1 ml-3 text-left whitespace-nowrap">{{ $label }}</span>
        </button>

        <ul id="dropdown-{{ Str::slug($label) }}" class="hidden py-2 space-y-2">
            @foreach ($children as $child)
                <li>
                    <a href="{{ $child['href'] }}" class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700">
                        {{ $child['label'] ?? $slot }}
                    </a>
                </li>
            @endforeach
        </ul>
    @else
        <a
            wire:navigate.hover
            href="{{ $href }}"
            @class([
                "flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group",
                'dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 dark:bg-gray-700 dark:hover:text-white' => $active,
            ])
        >
        @isset($icon)
            {{  $icon }}
        @endisset

        <span class="ml-3">{{ $label ?? $slot }}</span>
        </a>
    @endif
  </li>
