<?php

declare(strict_types=1);

namespace App\View\Composers;

use App\Services\BrandingService;
use Illuminate\View\View;
use Throwable;

final class AppViewComposer
{
    private const FONT_MAP = [
        'inter' => 'Inter',
        'roboto' => 'Roboto',
        'poppins' => 'Poppins',
        'lato' => 'Lato',
        'inria_serif' => 'Inria Serif',
        'arvo' => 'Arvo',
    ];

    private const SERIF_FONTS = ['inria_serif', 'arvo', 'lato'];

    private const DEFAULT_PRIMARY = '#f59e0b';

    private const DEFAULT_SECONDARY = '#6b7280';

    public function __construct(private readonly BrandingService $brandingService) {}

    public function compose(View $view): void
    {
        try {
            $branding = $this->brandingService->getSettings();
            $fontKey = $branding['font'] ?? 'inter';
            $selectedFont = self::FONT_MAP[$fontKey] ?? 'Inter';
            $isSerif = in_array($fontKey, self::SERIF_FONTS);

            $primaryColor = $branding['primary_color'] ?? self::DEFAULT_PRIMARY;
            $secondaryColor = $branding['secondary_color'] ?? self::DEFAULT_SECONDARY;
            $primaryColor = $this->validateHex($primaryColor) ? $primaryColor : self::DEFAULT_PRIMARY;
            $secondaryColor = $this->validateHex($secondaryColor) ? $secondaryColor : self::DEFAULT_SECONDARY;
            $selectedFont = preg_match('/^[a-zA-Z0-9\s-]+$/', $selectedFont) ? $selectedFont : 'Inter';

            $emblemLight = $branding['emblem_light'] ?? null;
            $emblemDark = $branding['emblem_dark'] ?? null;
            $emblemFallback = $emblemLight ?? $emblemDark;

            $view->with([
                'primaryColor' => $primaryColor,
                'secondaryColor' => $secondaryColor,
                'selectedFont' => $selectedFont,
                'isSerif' => $isSerif,
                'primaryCSSVars' => $this->brandingService->generateCSSVariables('primary', $primaryColor),
                'secondaryCSSVars' => $this->brandingService->generateCSSVariables('secondary', $secondaryColor),
                'emblemLight' => $emblemLight,
                'emblemDark' => $emblemDark,
                'emblemFallback' => $emblemFallback,
            ]);
        } catch (Throwable) {
            $view->with([
                'primaryColor' => self::DEFAULT_PRIMARY,
                'secondaryColor' => self::DEFAULT_SECONDARY,
                'selectedFont' => 'Inter',
                'isSerif' => false,
                'primaryCSSVars' => '',
                'secondaryCSSVars' => '',
                'emblemLight' => null,
                'emblemDark' => null,
                'emblemFallback' => null,
            ]);
        }
    }

    private function validateHex(string $hex): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $hex);
    }
}
