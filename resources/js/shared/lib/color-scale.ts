import { formatHex, oklch, parse, rgb, type Oklch, type Rgb } from 'culori';

export type TailwindShade = 50 | 100 | 200 | 300 | 400 | 500 | 600 | 700 | 800 | 900 | 950;

export type ColorScale = Record<TailwindShade, string>;

/**
 * Lightness values for each Tailwind shade in OKLCH space.
 * These values are perceptually uniform, ensuring consistent visual steps.
 */
const LIGHTNESS_MAP: Record<TailwindShade, number> = {
  50: 0.95, // lightest
  100: 0.90,
  200: 0.80,
  300: 0.70,
  400: 0.60,
  500: 0.50, // base
  600: 0.40,
  700: 0.30,
  800: 0.22,
  900: 0.15,
  950: 0.08, // darkest
};

/**
 * Calculate chroma scaling factor based on lightness.
 * Keeps colors vibrant in the middle range and reduces saturation at extremes.
 * Uses a power curve for smooth falloff.
 */
function chromaScale(lightness: number): number {
  return Math.pow(1 - Math.abs(lightness - 0.5) * 2, 0.85);
}

/**
 * Generates a full Tailwind-compatible color scale from a single hex color.
 * Uses OKLCH color space to maintain perceptual uniformity and stable hue/chroma.
 *
 * @param hexColor - Base color in hex format (e.g., "#eab308")
 * @returns Object mapping Tailwind shades (50-950) to hex color strings
 */
export function generateColorScale(hexColor: string): ColorScale {
  // Parse and convert to OKLCH
  const parsed = parse(hexColor);
  if (!parsed) {
    throw new Error(`Invalid hex color: ${hexColor}`);
  }

  const baseOklch = oklch(parsed);
  if (!baseOklch) {
    throw new Error(`Failed to convert color to OKLCH: ${hexColor}`);
  }

  // Extract hue and chroma from base color
  const baseHue = baseOklch.h ?? 0;
  const baseChroma = baseOklch.c ?? 0;

  // Generate scale by interpolating lightness while keeping hue/chroma stable
  const scale: Partial<ColorScale> = {};

  for (const [shadeStr, targetLightness] of Object.entries(LIGHTNESS_MAP)) {
    const shade = Number(shadeStr) as TailwindShade;
    // Scale chroma based on lightness distance from midpoint
    // This keeps colors vibrant in the middle and more neutral at extremes
    const adjustedChroma = baseChroma * chromaScale(targetLightness);

    // Create color in OKLCH space
    const color: Oklch = {
      mode: 'oklch',
      l: targetLightness,
      c: adjustedChroma,
      h: baseHue,
    };

    // Convert back to hex
    const hex = formatHex(color);
    scale[shade] = hex;
  }

  return scale as ColorScale;
}

/**
 * Calculates the relative luminance of a color using the WCAG formula.
 * Used to determine text contrast requirements.
 *
 * @param hex - Color in hex format
 * @returns Relative luminance value between 0 (black) and 1 (white)
 */
function getRelativeLuminance(hex: string): number {
  const parsed = parse(hex);
  if (!parsed) {
    throw new Error(`Invalid hex color: ${hex}`);
  }

  // Convert to RGB color object
  const rgbColor = rgb(parsed);
  if (!rgbColor) {
    throw new Error(`Failed to convert color to RGB: ${hex}`);
  }
  
  const r = rgbColor.r;
  const g = rgbColor.g;
  const b = rgbColor.b;

  // Apply gamma correction
  const [rLinear, gLinear, bLinear] = [r, g, b].map((c) => {
    // Apply gamma correction
    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
  });

  // Calculate relative luminance using WCAG formula
  return 0.2126 * rLinear + 0.7152 * gLinear + 0.0722 * bLinear;
}

/**
 * Determines whether black or white text should be used on a given background color
 * to meet WCAG contrast guidelines.
 *
 * @param hexColor - Background color in hex format
 * @returns 'white' for dark backgrounds, 'black' for light backgrounds
 */
export function getContrastTextColor(hexColor: string): 'white' | 'black' {
  const luminance = getRelativeLuminance(hexColor);
  // Threshold of 0.179 corresponds to ~4.5:1 contrast ratio with white
  // This ensures WCAG AA compliance for normal text
  return luminance > 0.179 ? 'black' : 'white';
}

/**
 * Generates CSS variables in the format expected by shadcn/ui and Tailwind.
 * Outputs RGB values as space-separated numbers (e.g., "255 128 64")
 * which can be used with `rgb(var(--color) / opacity)` syntax.
 *
 * @param name - Base name for the color scale (e.g., "primary" or "secondary")
 * @param hexColor - Base color in hex format
 * @returns CSS variable declarations as a string
 */
export function generateCSSVariables(name: string, hexColor: string): string {
  const scale = generateColorScale(hexColor);
  const variables: string[] = [];

  for (const [shadeStr, hex] of Object.entries(scale)) {
    const shade = Number(shadeStr) as TailwindShade;
    const parsed = parse(hex);
    if (!parsed) {
      continue;
    }

    // Convert to RGB
    const rgbColor = rgb(parsed);
    if (!rgbColor) {
      continue;
    }

    // Convert from 0-1 range to 0-255
    const r = Math.round(rgbColor.r * 255);
    const g = Math.round(rgbColor.g * 255);
    const b = Math.round(rgbColor.b * 255);

    // Format as space-separated RGB values for CSS variable
    const rgbValues = `${r} ${g} ${b}`;
    variables.push(`  --${name}-${shade}: ${rgbValues};`);
  }

  return variables.join('\n');
}

/**
 * Generates a Tailwind config snippet for consuming the generated color scales.
 * Note: With Tailwind v4, this is primarily for reference as colors are defined
 * in CSS using @theme directive.
 *
 * @param scales - Object mapping color names to their base hex values
 * @returns Tailwind config snippet as a string
 */
export function generateTailwindConfig(scales: Record<string, string>): string {
  const config: string[] = ['colors: {'];

  for (const [name, hexColor] of Object.entries(scales)) {
    const scale = generateColorScale(hexColor);
    config.push(`  ${name}: {`);

    for (const [shadeStr] of Object.entries(scale)) {
      const shade = Number(shadeStr) as TailwindShade;

      config.push(
        `    ${shade}: 'rgb(var(--${name}-${shade}) / <alpha-value>)',`
      );
    }

    config.push('  },');
  }

  config.push('}');
  return config.join('\n');
}
