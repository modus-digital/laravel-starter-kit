<?php

declare(strict_types=1);

namespace App\Translation;

use Illuminate\Translation\FileLoader;

/**
 * This class is a workaround to load nested JSON files and add support for nested json using __() and trans() helper functions.
 *
 * @author Alex van Steenhoven <alex@modus-digital.com>
 *
 * @version 1.0.0
 *
 * @since 1.0.0
 */
final class NestedJsonLoader extends FileLoader
{
    /**
     * @return array<string, mixed>
     */
    protected function loadJsonPaths($locale): array
    {
        // Load the JSON file from the main lang path
        $path = $this->paths[0]."/{$locale}.json";

        if (! is_file($path)) {
            return [];
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return [];
        }

        $translations = json_decode($content, true);

        // Flatten nested arrays with dot notation
        return $this->flattenTranslations($translations ?? []);
    }

    /**
     * @param array<string, mixed> $array
     * @return array<string, mixed>
     */
    private function flattenTranslations(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix !== '' && $prefix !== '0' ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenTranslations($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
