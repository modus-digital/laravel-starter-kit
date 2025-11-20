<?php

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
class NestedJsonLoader extends FileLoader
{
    protected function loadJsonPaths($locale)
    {
        // Load the JSON file from the main lang path
        $path = $this->paths[0]."/{$locale}.json";

        if (! is_file($path)) {
            return [];
        }

        $translations = json_decode(file_get_contents($path), true);

        // Flatten nested arrays with dot notation
        return $this->flattenTranslations($translations ?? []);
    }

    protected function flattenTranslations(array $array, string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenTranslations($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }
}
