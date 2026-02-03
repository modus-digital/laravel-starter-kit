<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;

final class BrandingService
{
    private const CACHE_KEY = 'branding_settings'; // 1 hour

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
        return Cache::remember(self::CACHE_KEY, now()->addHour(), fn (): array => [
            'primary_color' => $this->getPrimaryColorHex(),
            'secondary_color' => $this->getSecondaryColorHex(),
            'font' => $this->getFont(),
            'logo_light' => $this->getLogoLightUrl(),
            'logo_dark' => $this->getLogoDarkUrl(),
            'emblem_light' => $this->getEmblemLightUrl(),
            'emblem_dark' => $this->getEmblemDarkUrl(),
        ]);
    }

    /**
     * Backwards-compatible primary color accessor returning hex.
     */
    public function getPrimaryColor(): string
    {
        return $this->getPrimaryColorHex();
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
     * Backwards-compatible secondary color accessor returning hex.
     */
    public function getSecondaryColor(): string
    {
        return $this->getSecondaryColorHex();
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
        return setting('branding.font') ?? self::DEFAULT_FONT;
    }

    /**
     * Get light mode logo URL or null if not set.
     */
    public function getLogoLightUrl(): ?string
    {
        return $this->getAssetUrl('branding.logo_light');
    }

    /**
     * Get dark mode logo URL or null if not set.
     */
    public function getLogoDarkUrl(): ?string
    {
        return $this->getAssetUrl('branding.logo_dark');
    }

    /**
     * Get light mode emblem URL (logo without text, used for favicon and icon) or null if not set.
     */
    public function getEmblemLightUrl(): ?string
    {
        return $this->getAssetUrl('branding.emblem_light');
    }

    /**
     * Get dark mode emblem URL (logo without text, used for favicon and icon) or null if not set.
     */
    public function getEmblemDarkUrl(): ?string
    {
        return $this->getAssetUrl('branding.emblem_dark');
    }

    /**
     * Get primary color as an RGB array.
     *
     * @return array{0:int,1:int,2:int}
     */
    public function getPrimaryColorRgb(): array
    {
        return $this->hexToRgb($this->getPrimaryColorHex());
    }

    /**
     * Get secondary color as an RGB array.
     *
     * @return array{0:int,1:int,2:int}
     */
    public function getSecondaryColorRgb(): array
    {
        return $this->hexToRgb($this->getSecondaryColorHex());
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
     * Generate a full Tailwind-compatible color scale from a single hex color.
     * Uses OKLCH color space to maintain perceptual uniformity and stable hue/chroma.
     * The input color becomes the 500 shade.
     *
     * @return array<int, string> Map of shade (50-950) to hex color string
     */
    public function generateColorScale(string $hexColor): array
    {
        // Convert hex to RGB
        $rgb = $this->hexToRgb($hexColor);

        // Convert RGB to OKLCH
        $baseOklch = $this->rgbToOklch($rgb[0], $rgb[1], $rgb[2]);

        // Extract hue and chroma from base color
        $baseHue = $baseOklch['h'];
        $baseChroma = $baseOklch['c'];

        // Lightness values for each Tailwind shade in OKLCH space
        // These values are perceptually uniform, ensuring consistent visual steps
        $lightnessMap = [
            50 => 0.95, // lightest
            100 => 0.90,
            200 => 0.80,
            300 => 0.70,
            400 => 0.60,
            500 => 0.50, // base
            600 => 0.40,
            700 => 0.30,
            800 => 0.22,
            900 => 0.15,
            950 => 0.08, // darkest
        ];

        // Generate scale by interpolating lightness while keeping hue/chroma stable
        $scale = [];

        foreach ($lightnessMap as $shade => $targetLightness) {
            // Scale chroma based on lightness distance from midpoint
            // This keeps colors vibrant in the middle and more neutral at extremes
            $adjustedChroma = $baseChroma * $this->chromaScale($targetLightness);

            // Convert OKLCH back to RGB
            $shadeRgb = $this->oklchToRgb($targetLightness, $adjustedChroma, $baseHue);

            // Convert RGB to hex
            $scale[$shade] = $this->rgbToHex($shadeRgb[0], $shadeRgb[1], $shadeRgb[2]);
        }

        return $scale;
    }

    /**
     * Generate CSS variables in the format expected by shadcn/ui and Tailwind.
     * Outputs RGB values as space-separated numbers (e.g., "255 128 64")
     * which can be used with `rgb(var(--color) / opacity)` syntax.
     *
     * @param name Base name for the color scale (e.g., "primary" or "secondary")
     * @param hexColor Base color in hex format
     * @return string CSS variable declarations
     */
    public function generateCSSVariables(string $name, string $hexColor): string
    {
        $scale = $this->generateColorScale($hexColor);
        $variables = [];

        foreach ($scale as $shade => $hex) {
            $rgb = $this->hexToRgb($hex);
            // Format as space-separated RGB values for CSS variable
            $rgbValues = sprintf('%d %d %d', $rgb[0], $rgb[1], $rgb[2]);
            $variables[] = sprintf('  --%s-%d: %s;', $name, $shade, $rgbValues);
        }

        return implode("\n", $variables);
    }

    /**
     * Determines whether black or white text should be used on a given background color
     * to meet WCAG contrast guidelines.
     *
     * @param hexColor Background color in hex format
     * @return 'white' for dark backgrounds, 'black' for light backgrounds
     */
    public function getContrastTextColor(string $hexColor): string
    {
        $luminance = $this->getRelativeLuminance($hexColor);

        // Threshold of 0.179 corresponds to ~4.5:1 contrast ratio with white
        // This ensures WCAG AA compliance for normal text
        return $luminance > 0.179 ? 'black' : 'white';
    }

    /**
     * Get asset URL from setting key.
     */
    private function getAssetUrl(string $settingKey): ?string
    {
        $asset = setting($settingKey);

        if (! $asset) {
            return null;
        }

        return $asset;
    }

    /**
     * Calculate chroma scaling factor based on lightness.
     * Keeps colors vibrant in the middle range and reduces saturation at extremes.
     * Uses a power curve for smooth falloff.
     */
    private function chromaScale(float $lightness): float
    {
        return pow(1 - abs($lightness - 0.5) * 2, 0.85);
    }

    /**
     * Calculate the relative luminance of a color using the WCAG formula.
     * Used to determine text contrast requirements.
     *
     * @param hex Color in hex format
     * @return float Relative luminance value between 0 (black) and 1 (white)
     */
    private function getRelativeLuminance(string $hex): float
    {
        $rgb = $this->hexToRgb($hex);

        // Normalize RGB values to 0-1 range
        $r = $rgb[0] / 255.0;
        $g = $rgb[1] / 255.0;
        $b = $rgb[2] / 255.0;

        // Apply gamma correction
        $r = $r <= 0.03928 ? $r / 12.92 : (($r + 0.055) / 1.055) ** 2.4;
        $g = $g <= 0.03928 ? $g / 12.92 : (($g + 0.055) / 1.055) ** 2.4;
        $b = $b <= 0.03928 ? $b / 12.92 : (($b + 0.055) / 1.055) ** 2.4;

        // Calculate relative luminance using WCAG formula
        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }

    /**
     * Convert OKLCH to RGB.
     *
     * @return array{0: int, 1: int, 2: int} RGB values as integers 0-255
     */
    private function oklchToRgb(float $l, float $c, float $h): array
    {
        // Convert OKLCH to OKLab
        $hRad = deg2rad($h);
        $a = $c * cos($hRad);
        $b_lab = $c * sin($hRad);

        // Convert OKLab to XYZ
        $l_ = $l + 0.3963377774 * $a + 0.2158037573 * $b_lab;
        $m_ = $l - 0.1055613458 * $a - 0.0638541728 * $b_lab;
        $s_ = $l - 0.0894841775 * $a - 1.2914855480 * $b_lab;

        $l_ = $l_ ** 3;
        $m_ = $m_ ** 3;
        $s_ = $s_ ** 3;

        $x = 1.2270138511 * $l_ - 0.5577999807 * $m_ + 0.2812561490 * $s_;
        $y = 0.0405793773 * $l_ + 0.1849682802 * $m_ - 0.2254883756 * $s_;
        $z = -0.0763812849 * $l_ + 0.1456170499 * $m_ + 0.6167253928 * $s_;

        // Convert XYZ to linear RGB (D65 illuminant)
        $r = 3.2404542 * $x - 1.5371385 * $y - 0.4985314 * $z;
        $g = -0.9692660 * $x + 1.8760108 * $y + 0.0415560 * $z;
        $b = 0.0556434 * $x - 0.2040259 * $y + 1.0572252 * $z;

        // Apply gamma correction (linear RGB to sRGB)
        $r = $r <= 0.0031308 ? 12.92 * $r : 1.055 * ($r ** (1 / 2.4)) - 0.055;
        $g = $g <= 0.0031308 ? 12.92 * $g : 1.055 * ($g ** (1 / 2.4)) - 0.055;
        $b = $b <= 0.0031308 ? 12.92 * $b : 1.055 * ($b ** (1 / 2.4)) - 0.055;

        // Clamp to 0-1 and convert to 0-255
        return [
            (int) round(max(0, min(255, $r * 255))),
            (int) round(max(0, min(255, $g * 255))),
            (int) round(max(0, min(255, $b * 255))),
        ];
    }

    /**
     * Convert RGB to hex color string.
     */
    private function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02X%02X%02X', $r, $g, $b);
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
