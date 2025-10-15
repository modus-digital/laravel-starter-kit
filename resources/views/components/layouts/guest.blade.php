<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        @vite("resources/css/app.css")

        <title>
            {{ (isset($title) ? $title . " | " : "") . config("app.name") }}
        </title>
    </head>

    <body class="h-screen overflow-hidden bg-zinc-50 antialiased dark:bg-zinc-900">
        <x-toaster-hub />

        {{ $slot }}

        @vite("resources/js/app.js")
        <script>
            window.matchMedia('(prefers-color-scheme: dark)').matches
                ? document.documentElement.classList.add('dark')
                : document.documentElement.classList.remove('dark');
        </script>
    </body>
</html>
