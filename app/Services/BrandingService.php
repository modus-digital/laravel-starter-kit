<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

final class BrandingService
{
    private const CACHE_KEY = 'branding_settings';

    private const CACHE_TTL = 3600; // 1 hour

    private const DEFAULT_PRIMARY_COLOR = '#f59e0b'; // Amber-500

    private const DEFAULT_SECONDARY_COLOR = '#6b7280'; // Gray-500

    private const DEFAULT_FONT = 'inter';

    /**
     * Get all branding settings with caching.
     *
     * @return array<string, mixed>
     */
    public function getSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, fn (): array => [
            'primary_color' => $this->getPrimaryColorHex(),
            'secondary_color' => $this->getSecondaryColorHex(),
            'font' => $this->getFont(),
            'logo' => $this->getLogoUrl(),
        ]);
    }

    /**
     * Get primary color in hex format.
     */
    public function getPrimaryColorHex(): string
    {
        $hex = setting('branding.primary_color');

        // Use default if null or empty
        if (empty($hex)) {
            return self::DEFAULT_PRIMARY_COLOR;
        }

        // Normalize hex color: ensure # prefix and uppercase
        $hex = str_starts_with((string) $hex, '#') ? $hex : '#'.$hex;
        $hex = mb_strtoupper((string) $hex);

        // Validate hex format (6 digits)
        if (! preg_match('/^#[0-9A-F]{6}$/', $hex)) {
            return self::DEFAULT_PRIMARY_COLOR;
        }

        return $hex;
    }

    /**
     * Get secondary color in hex format.
     */
    public function getSecondaryColorHex(): string
    {
        $hex = setting('branding.secondary_color');

        // Use default if null or empty
        if (empty($hex)) {
            return self::DEFAULT_SECONDARY_COLOR;
        }

        // Normalize hex color: ensure # prefix and uppercase
        $hex = str_starts_with((string) $hex, '#') ? $hex : '#'.$hex;
        $hex = mb_strtoupper((string) $hex);

        // Validate hex format (6 digits)
        if (! preg_match('/^#[0-9A-F]{6}$/', $hex)) {
            return self::DEFAULT_SECONDARY_COLOR;
        }

        return $hex;
    }

    /**
     * Get selected font name.
     */
    public function getFont(): string
    {
        return setting('branding.font', self::DEFAULT_FONT);
    }

    /**
     * Get logo URL or null if not set.
     */
    public function getLogoUrl(): ?string
    {
        $logoFilename = setting('branding.logo');

        if (! $logoFilename) {
            return null;
        }

        return Storage::disk('public')->url($logoFilename);
    }

    /**
     * Get primary color palette for Filament with auto-detected base shade.
     *
     * @return array<int, string>
     */
    public function getFilamentPrimaryColorPalette(): array
    {
        $hex = $this->getPrimaryColorHex();

        return $this->generatePaletteFromColor($hex);
    }

    /**
     * Get secondary color palette for Filament with auto-detected base shade.
     *
     * @return array<int, string>
     */
    public function getFilamentSecondaryColorPalette(): array
    {
        $hex = $this->getSecondaryColorHex();

        return $this->generatePaletteFromColor($hex);
    }

    /**
     * Clear branding cache.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Generate a color palette from a hex color by detecting its natural shade.
     * The input color's lightness determines which shade (50-950) it becomes.
     *
     * @return array<int, string>
     */
    private function generatePaletteFromColor(string $hex): array
    {
        // Convert hex to RGB
        $rgb = $this->hexToRgb($hex);

        // Convert RGB to OKLCH
        $oklch = $this->rgbToOklch($rgb[0], $rgb[1], $rgb[2]);

        // Extract components
        $baseL = $oklch['l'];
        $baseC = $oklch['c'];
        $baseH = $oklch['h'];

        // Determine which shade the input color should be based on its lightness
        // Filament uses this logic: lighter colors = lower shades, darker colors = higher shades
        $baseShade = $this->determineShadeFromLightness($baseL);

        // Define standard lightness values for each shade
        // These are approximate target lightness values
        $standardLightness = [
            50 => 0.98,
            100 => 0.95,
            200 => 0.90,
            300 => 0.84,
            400 => 0.75,
            500 => 0.65,
            600 => 0.55,
            700 => 0.45,
            800 => 0.35,
            900 => 0.30,
            950 => 0.25,
        ];

        // Calculate the difference needed to align our color to standard values
        $targetL = $standardLightness[$baseShade];
        $lightnessDiff = $baseL - $targetL;

        // Generate palette by offsetting standard lightness values
        $palette = [];
        foreach ($standardLightness as $shade => $standardL) {
            // Apply the same lightness difference to maintain the color's character
            $adjustedL = $standardL + $lightnessDiff;

            // Clamp lightness to valid range
            $adjustedL = max(0.05, min(0.99, $adjustedL));

            // Adjust chroma based on shade (reduce for very light/dark)
            $chromaFactor = $this->getChromaFactorForShade($shade, $baseShade);
            $adjustedC = $baseC * $chromaFactor;

            $palette[$shade] = sprintf(
                'oklch(%.11f %.11f %.3f)',
                $adjustedL,
                $adjustedC,
                $baseH
            );
        }

        return $palette;
    }

    /**
     * Determine which shade (50-950) a color should be based on its lightness.
     */
    private function determineShadeFromLightness(float $lightness): int
    {
        // Map lightness values to shades
        if ($lightness >= 0.97) {
            return 50;
        }
        if ($lightness >= 0.92) {
            return 100;
        }
        if ($lightness >= 0.87) {
            return 200;
        }
        if ($lightness >= 0.79) {
            return 300;
        }
        if ($lightness >= 0.70) {
            return 400;
        }
        if ($lightness >= 0.60) {
            return 500;
        }
        if ($lightness >= 0.50) {
            return 600;
        }
        if ($lightness >= 0.40) {
            return 700;
        }
        if ($lightness >= 0.32) {
            return 800;
        }
        if ($lightness >= 0.27) {
            return 900;
        }

        return 950;
    }

    /**
     * Get chroma multiplier for a shade relative to the base shade.
     */
    private function getChromaFactorForShade(int $shade, int $baseShade): float
    {
        // If it's the base shade, use full chroma
        if ($shade === $baseShade) {
            return 1.0;
        }

        // For very light shades, reduce chroma significantly
        if ($shade <= 100) {
            return 0.15;
        }
        if ($shade === 200) {
            return 0.35;
        }
        if ($shade === 300) {
            return 0.60;
        }

        // For middle shades (400-600), maintain high chroma
        if ($shade >= 400 && $shade <= 600) {
            return 0.95;
        }

        // For dark shades, gradually reduce chroma
        if ($shade === 700) {
            return 0.85;
        }
        if ($shade === 800) {
            return 0.70;
        }
        if ($shade === 900) {
            return 0.60;
        }
        if ($shade === 950) {
            return 0.45;
        }

        return 0.90; // Default
    }

    /**
     * Convert hex color to RGB array.
     *
     * @return array{int, int, int}
     */
    private function hexToRgb(string $hex): array
    {
        $hex = mb_ltrim($hex, '#');

        return [
            (int) hexdec(mb_substr($hex, 0, 2)),
            (int) hexdec(mb_substr($hex, 2, 2)),
            (int) hexdec(mb_substr($hex, 4, 2)),
        ];
    }

    /**
     * Convert RGB to OKLCH color space.
     *
     * @return array{l: float, c: float, h: float}
     */
    private function rgbToOklch(int $r, int $g, int $b): array
    {
        // Normalize RGB to 0-1
        $r /= 255.0;
        $g /= 255.0;
        $b /= 255.0;

        // Apply gamma correction (sRGB to linear RGB)
        $r = $r <= 0.04045 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.04045 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.04045 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        // Convert linear RGB to XYZ (D65 illuminant)
        $x = $r * 0.4124564 + $g * 0.3575761 + $b * 0.1804375;
        $y = $r * 0.2126729 + $g * 0.7151522 + $b * 0.0721750;
        $z = $r * 0.0193339 + $g * 0.1191920 + $b * 0.9503041;

        // Convert XYZ to OKLab
        $l_ = 0.8189330101 * $x + 0.3618667424 * $y - 0.1288597137 * $z;
        $m_ = 0.0329845436 * $x + 0.9293118715 * $y + 0.0361456387 * $z;
        $s_ = 0.0482003018 * $x + 0.2643662691 * $y + 0.6338517070 * $z;

        $l_ **= 1 / 3;
        $m_ **= 1 / 3;
        $s_ **= 1 / 3;

        $L = 0.2104542553 * $l_ + 0.7936177850 * $m_ - 0.0040720468 * $s_;
        $a = 1.9779984951 * $l_ - 2.4285922050 * $m_ + 0.4505937099 * $s_;
        $b_lab = 0.0259040371 * $l_ + 0.7827717662 * $m_ - 0.8086757660 * $s_;

        // Convert OKLab to OKLCH
        $C = sqrt($a * $a + $b_lab * $b_lab);
        $h = atan2($b_lab, $a) * 180 / M_PI;
        if ($h < 0) {
            $h += 360;
        }

        return [
            'l' => max(0, min(1, $L)),
            'c' => max(0, $C),
            'h' => $h,
        ];
    }
}
