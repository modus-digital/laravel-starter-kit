@use ('App\Enums\Settings')

@php
    $theme = 'blue'; // TODO: Get from user settings once implemented
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @vite('resources/css/app.css')

        <title>{{ (isset($title) ? $title . " | " : "") . config('app.name') }}</title>
    </head>
    <body class="antialiased bg-gray-50 dark:bg-gray-900" data-theme="{{ $theme }}">
        <x-layouts.navigation.header title="Modus digital" />
        <x-layouts.navigation.sidebar />

        <main class="p-4 md:ml-64 h-auto pt-20">
            {{ $slot }}
        </main>

        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
        @vite('resources/ts/app.ts')
        <x-toaster-hub />
    </body>
</html>
