<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        @vite('resources/css/app.css')

        <title>{{ $title ?? 'Page Title' }}</title>
    </head>
    <body class="antialiased bg-gray-50 dark:bg-gray-900">

        {{ $slot }}

        <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
        @vite('resources/ts/app.ts')
    </body>
</html>
