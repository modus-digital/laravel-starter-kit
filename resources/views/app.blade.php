<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600&family=roboto:400,500,600&family=poppins:400,500,600&family=lato:400,500,600&family=inria-serif:400,500,600&family=arvo:400,600,700" rel="stylesheet" />

        @php
            try {
                $branding = app(\App\Services\BrandingService::class)->getSettings();
                $fontMap = [
                    'inter' => 'Inter',
                    'roboto' => 'Roboto',
                    'poppins' => 'Poppins',
                    'lato' => 'Lato',
                    'inria_serif' => 'Inria Serif',
                    'arvo' => 'Arvo',
                ];
                $serifFonts = ['inria_serif', 'arvo'];
                $selectedFontKey = $branding['font'] ?? 'inter';
                $selectedFont = $fontMap[$selectedFontKey] ?? 'Inter';
                $isSerif = in_array($selectedFontKey, $serifFonts);
                $primaryColor = $branding['primary_color'] ?? '#f59e0b';
                $secondaryColor = $branding['secondary_color'] ?? '#6b7280';
                
                // Validate hex colors are safe
                $primaryColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor) ? $primaryColor : '#f59e0b';
                $secondaryColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $secondaryColor) ? $secondaryColor : '#6b7280';
                $selectedFont = preg_match('/^[a-zA-Z0-9\s-]+$/', $selectedFont) ? $selectedFont : 'Inter';
            } catch (\Throwable $e) {
                // Fallback to defaults if branding service fails
                $selectedFont = 'Inter';
                $isSerif = false;
                $primaryColor = '#f59e0b';
                $secondaryColor = '#6b7280';
            }
        @endphp

        <style id="branding-styles">
            :root {
                --brand-primary: {{ $primaryColor }};
                --brand-secondary: {{ $secondaryColor }};
                --brand-font-sans: '{{ addslashes($selectedFont) }}', {{ $isSerif ? 'ui-serif, Georgia, serif' : 'ui-sans-serif, system-ui, sans-serif' }}, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
                --primary: {{ $primaryColor }};
                --secondary: {{ $secondaryColor }};
            }

            .dark {
                --primary: {{ $primaryColor }};
                --secondary: {{ $secondaryColor }};
            }

            html,
            body {
                font-family: var(--brand-font-sans) !important;
            }
        </style>

        @viteReactRefresh
        @vite(['resources/js/app.tsx', "resources/js/pages/{$page['component']}.tsx"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased max-h-screen overflow-hidden">
        @inertia
    </body>
</html>
