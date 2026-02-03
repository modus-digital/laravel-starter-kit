<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'system') == 'dark'])>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Apply system dark mode immediately to avoid flash --}}
    <script>
        (function () {
            const appearance = '{{ $appearance ?? "system" }}';
            if (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <style>
        html {
            background-color: oklch(1 0 0);
        }

        html.dark {
            background-color: oklch(0.145 0 0);
        }
    </style>

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    @if ($emblemFallback)
        <link rel="icon" href="{{ $emblemFallback }}" sizes="any">
        @if ($emblemLight)
            <link rel="icon" href="{{ $emblemLight }}" media="(prefers-color-scheme: light)">
        @endif
        @if ($emblemDark)
            <link rel="icon" href="{{ $emblemDark }}" media="(prefers-color-scheme: dark)">
        @endif
        <link rel="apple-touch-icon" href="{{ $emblemFallback }}">
    @else
        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    @endif

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=inter:400,500,600&family=roboto:400,500,600&family=poppins:400,500,600&family=lato:400,500,600&family=inria-serif:400,500,600&family=arvo:400,600,700"
        rel="stylesheet">

    <style id="branding-styles">
        :root {
            --brand-primary:
                {{ $primaryColor }}
            ;
            --brand-secondary:
                {{ $secondaryColor }}
            ;
            --brand-font-sans: '{{ addslashes($selectedFont) }}',
                {{ $isSerif ? 'ui-serif, Georgia, serif' : 'ui-sans-serif, system-ui, sans-serif' }}
                , 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji';
            --primary:
                {{ $primaryColor }}
            ;
            --secondary:
                {{ $secondaryColor }}
            ;
            @if ($primaryCSSVars)
                {!! $primaryCSSVars !!}
            @endif
            @if ($secondaryCSSVars)
                {!! $secondaryCSSVars !!}
            @endif
        }

        .dark {
            --primary:
                {{ $primaryColor }}
            ;
            --secondary:
                {{ $secondaryColor }}
            ;
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

<body class="font-sans antialiased max-h-screen overflow-y-auto">
    @inertia
</body>

</html>