@props([
    'length' => $length ?? 4,
    'alphaNumeric' => $alphaNumeric ?? false,
    'size' => $size ?? 'md',
    'name' => 'pin_' . Str::random(10),
    'container' => $container ?? '',
    'separator' => false,
])

@php
    /** Size mapping → Tailwind width/height & text‑size */
    $sizes = [
        'sm' => 'w-9  h-9  text-base',
        'md' => 'w-12 h-12 text-xl',
        'lg' => 'w-14 h-14 text-2xl',
    ];

    $flowbiteBase = 'block rounded-lg border outline-none text-center uppercase bg-gray-50 border-gray-300 text-gray-900 dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-primary-500';
@endphp

<div
    data-pin-input
    data-length="{{ $length }}"
    data-alpha-numeric="{{ $alphaNumeric ? '1' : '0' }}"
    role="group"
    @class([ 'flex gap-2 mb-4', $container ])
>
    @for ($i = 0; $i < $length; $i++)
        <input
            type="text"
            maxlength="1"
            inputmode="{{ $alphaNumeric ? 'text' : 'numeric' }}"
            aria-label="Character {{ $i + 1 }} of {{ $length }}"
            data-index="{{ $i }}"
            name="{{ $name }}[{{ $i }}]"
            class="{{ $flowbiteBase }} {{ $sizes[$size] ?? $sizes['md'] }}"
            data-pin-inputs
        />
        @if ($separator && $i < $length - 1 && $i == intval($length / 2) - 1)
            <div class="flex items-center justify-center">
                <span class="text-gray-500 dark:text-gray-400 font-bold text-2xl">-</span>
            </div>
        @endif
    @endfor

    {{-- Hidden input to store the combined pin value --}}
    <input type="hidden" data-pin-input-value="{{ $name }}" {{ $attributes }} />

    @vite('resources/ts/components/pin-input.ts')
</div>
