@use("App\Enums\Settings\UserSettings")
@use("App\Enums\Settings\Appearance")

@php
    $user = auth()->user();

    $displaySettings = collect($user->settings->where("key", UserSettings::DISPLAY)->first()->value);
    $localizationSettings = collect($user->settings->where("key", UserSettings::LOCALIZATION)->first()->value);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace("_", "-", $localizationSettings->get("locale")) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        @vite("resources/css/app.css")

        <title>
            {{ (isset($title) ? $title . " | " : "") . config("app.name") }}
        </title>
    </head>

    <body
        data-theme="{{ $displaySettings->get("theme") }}"
        @class([
            "bg-zinc-50 antialiased dark:bg-zinc-900",
            "dark" => $displaySettings->get("appearance") === Appearance::DARK->value,
            "system" => $displaySettings->get("appearance") === Appearance::SYSTEM->value,
        ])
        x-data
        x-on:reload-page.window="window.location.reload()"
    >
        <x-toaster-hub />

        <x-layouts.navigation.header title="{{ $title ?? __('navigation.header.default_title') }}" />
        <x-layouts.navigation.sidebar />

        <main class="p-4 md:ml-64 h-auto pt-20">
            {{ $slot }}
        </main>

        @vite("resources/js/app.js")

        @if ($displaySettings->get("appearance") === Appearance::SYSTEM->value)
        <script>
            window.matchMedia('(prefers-color-scheme: dark)').matches
                ? document.documentElement.classList.add('dark')
                : document.documentElement.classList.remove('dark');
        </script>
        @endif
    </body>
</html>
