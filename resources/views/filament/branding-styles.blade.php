@php
    // Get the Filament color palette where user's color is shade 400
    $brandingService = app(\App\Services\BrandingService::class);
    $primaryPalette = $brandingService->getFilamentPrimaryColorPalette();
@endphp

<style>
    @if (!empty($primaryPalette))
        :root,
        .dark {
            /* Set primary color palette - user's chosen color is shade 400 */
            @foreach ($primaryPalette as $shade => $color)
                --color-primary-{{ $shade }}:
                    {{ $color }}
                    !important;
            @endforeach
        }

    @endif
</style>