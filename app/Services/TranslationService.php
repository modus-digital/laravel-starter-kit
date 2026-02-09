<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Concerns\FiltersModuleTranslations;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class TranslationService
{
    use FiltersModuleTranslations;

    /**
     * Get all available language codes from the lang directory.
     *
     * @return array<int, string>
     */
    public function getAvailableLanguages(): array
    {
        $langPath = lang_path();
        $files = File::files($langPath);

        $languages = [];
        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $languages[] = $file->getFilenameWithoutExtension();
            }
        }

        return $languages;
    }

    /**
     * Load and decode a language JSON file.
     *
     * @return array<string, mixed>
     */
    public function getLanguageFile(string $lang): array
    {
        $path = lang_path("{$lang}.json");

        if (! File::exists($path)) {
            return [];
        }

        // Clear stat cache before reading to ensure fresh data
        clearstatcache(true, $path);

        // Force fresh read by using file_get_contents instead of File facade
        $content = file_get_contents($path);

        if ($content === false) {
            return [];
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($content, true) ?? [];

        return $decoded;
    }

    /**
     * Save a language file with proper formatting.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveLanguageFile(string $lang, array $data): void
    {
        $path = lang_path("{$lang}.json");

        if ($data === []) {
            File::put($path, "{}\n");
            $this->clearFileCache($path);

            return;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        File::put($path, $json."\n");
        $this->clearFileCache($path);
    }

    /**
     * Get root groups (first-level keys) from English JSON.
     *
     * @return array<int|string, mixed>
     */
    public function getRootGroups(): array
    {
        $english = $this->getLanguageFile('en');

        return array_keys($english);
    }

    /**
     * Flatten nested translations to dot notation.
     *
     * @param  array<string, mixed>  $translations
     * @return array<string, mixed>
     */
    public function flattenTranslations(array $translations, string $prefix = ''): array
    {
        $result = [];

        foreach ($translations as $key => $value) {
            $keyString = (string) $key;
            $newKey = $prefix !== '' && $prefix !== '0' ? "{$prefix}.{$keyString}" : $keyString;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenTranslations($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Get missing translations for a target language.
     *
     * @return array<string, mixed>
     */
    public function getMissingTranslations(string $targetLang, ?string $group = null): array
    {
        $english = $this->getLanguageFile('en');
        $target = $this->getLanguageFile($targetLang);

        // Filter by group if specified
        if ($group !== null) {
            $english = $english[$group] ?? [];
            $target = $target[$group] ?? [];

            if (is_array($english)) {
                $english = $this->filterGroupTranslationsByModules($english, $group);
            }

            if (is_array($target)) {
                $target = $this->filterGroupTranslationsByModules($target, $group);
            }

            // Handle cases where the group value is a string instead of an array
            $englishFlat = is_string($english)
                ? [$group => $english]
                : $this->flattenTranslations($english);

            $targetFlat = is_string($target)
                ? [$group => $target]
                : $this->flattenTranslations($target);

            // Prepend group to keys for consistency
            $missing = [];
            foreach ($englishFlat as $key => $value) {
                if (! isset($targetFlat[$key]) || $targetFlat[$key] === '') {
                    $missing["{$group}.{$key}"] = $value;
                }
            }

            return $missing;
        }

        $englishFlat = $this->flattenTranslations($english);
        $targetFlat = $this->flattenTranslations($target);

        $missing = [];
        foreach ($englishFlat as $key => $value) {
            if (! isset($targetFlat[$key]) || $targetFlat[$key] === '') {
                $missing[$key] = $value;
            }
        }

        return $missing;
    }

    /**
     * Get translation progress for a language and group.
     *
     * @return array{missing: int, total: int, translated: int}
     */
    public function getTranslationProgress(string $lang, string $group): array
    {
        $english = $this->getLanguageFile('en');
        $target = $this->getLanguageFile($lang);

        $englishGroup = $english[$group] ?? [];
        $targetGroup = $target[$group] ?? [];

        if (is_array($englishGroup)) {
            $englishGroup = $this->filterGroupTranslationsByModules($englishGroup, $group);
        }

        if (is_array($targetGroup)) {
            $targetGroup = $this->filterGroupTranslationsByModules($targetGroup, $group);
        }

        $englishFlat = is_string($englishGroup)
            ? [$group => $englishGroup]
            : $this->flattenTranslations($englishGroup);

        $targetFlat = is_string($targetGroup)
            ? [$group => $targetGroup]
            : $this->flattenTranslations($targetGroup);

        $total = count($englishFlat);
        $translated = 0;

        foreach (array_keys($englishFlat) as $key) {
            if (isset($targetFlat[$key]) && $targetFlat[$key] !== '') {
                $translated++;
            }
        }

        $missing = $total - $translated;

        return [
            'missing' => $missing,
            'total' => $total,
            'translated' => $translated,
        ];
    }

    /**
     * Set a translation value using dot notation.
     * Handles keys that may contain dots as part of the key name.
     */
    public function setTranslation(string $lang, string $key, string $value): void
    {
        $data = $this->getLanguageFile($lang);

        // Split the key to identify the group and the actual key path
        $parts = explode('.', $key);
        $group = array_shift($parts); // First part is always the group (e.g., 'activity')

        // The remaining parts could be nested structure OR a single key with dots
        // We need to check the English structure to understand the intended nesting
        $englishData = $this->getLanguageFile('en');
        $englishGroup = $englishData[$group] ?? [];

        // Navigate through the structure to find where this key should be set
        $current = &$data;
        if (! isset($current[$group])) {
            $current[$group] = [];
        }
        $current = &$current[$group];

        // Try to match the structure from English
        $this->setNestedValue($current, $parts, $value, $englishGroup);

        $this->saveLanguageFile($lang, $data);
    }

    /**
     * Get a translation value using dot notation.
     * Handles keys that may contain dots as part of the key name.
     */
    public function getTranslation(string $lang, string $key): ?string
    {
        $data = $this->getLanguageFile($lang);

        // Split to get group
        $parts = explode('.', $key);
        $group = array_shift($parts);

        if (! isset($data[$group])) {
            return null;
        }

        return $this->getNestedValue($data[$group], $parts);
    }

    /**
     * Convert group name to human-readable format.
     */
    public function humanizeGroupName(string $group): string
    {
        return Str::of($group)
            ->replace('_', ' ')
            ->title()
            ->toString();
    }

    /**
     * Create a new language file.
     */
    public function createLanguage(string $code): void
    {
        $this->saveLanguageFile($code, []);
    }

    /**
     * Check if a language exists.
     */
    public function languageExists(string $code): bool
    {
        return File::exists(lang_path("{$code}.json"));
    }

    /**
     * Get the target language currently selected for translations.
     */
    public function getTargetLanguage(): string
    {
        $available = $this->getAvailableLanguages();
        $default = $this->determineDefaultTargetLanguage($available);

        $selected = Session::get('translations.target_language', $default);

        if (! in_array($selected, $available, true)) {
            $selected = $default;
        }

        Session::put('translations.target_language', $selected);

        return $selected;
    }

    /**
     * Persist the target language selection.
     */
    public function setTargetLanguage(string $language): void
    {
        $available = $this->getAvailableLanguages();

        if (! in_array($language, $available, true)) {
            throw new InvalidArgumentException("Language [{$language}] is not available.");
        }

        Session::put('translations.target_language', $language);
    }

    /**
     * Clear file cache to ensure fresh reads.
     */
    private function clearFileCache(string $path): void
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }

        clearstatcache(true, $path);

        // Also clear Laravel's translation cache
        app('translator')->setLoaded([]);
    }

    /**
     * Set a value in nested array, respecting keys that contain dots.
     *
     * @param  array<string, mixed>  &$target
     * @param  array<int, string>  $parts
     * @param  array<string, mixed>  $englishStructure
     */
    private function setNestedValue(array &$target, array $parts, string $value, array $englishStructure): void
    {
        if (count($parts) === 0) {
            return;
        }

        // Try progressively longer key combinations to match the English structure
        for ($i = count($parts); $i > 0; $i--) {
            $testKey = implode('.', array_slice($parts, 0, $i));
            $remaining = array_slice($parts, $i);

            // If this key exists in English structure
            if (array_key_exists($testKey, $englishStructure)) {
                if (count($remaining) === 0) {
                    // This is the final key
                    $target[$testKey] = $value;

                    return;
                }
                // Navigate deeper
                if (! isset($target[$testKey]) || ! is_array($target[$testKey])) {
                    $target[$testKey] = [];
                }
                $this->setNestedValue(
                    $target[$testKey],
                    $remaining,
                    $value,
                    is_array($englishStructure[$testKey]) ? $englishStructure[$testKey] : []
                );

                return;

            }
        }

        // Fallback: use first part as key and recurse
        $firstPart = array_shift($parts);
        if (count($parts) === 0) {
            $target[$firstPart] = $value;
        } else {
            if (! isset($target[$firstPart]) || ! is_array($target[$firstPart])) {
                $target[$firstPart] = [];
            }
            $this->setNestedValue(
                $target[$firstPart],
                $parts,
                $value,
                is_array($englishStructure[$firstPart] ?? null) ? $englishStructure[$firstPart] : []
            );
        }
    }

    /**
     * Get a value from nested array, respecting keys that contain dots.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $parts
     */
    private function getNestedValue(array $data, array $parts): ?string
    {
        if (count($parts) === 0) {
            return null;
        }

        // Try progressively longer key combinations
        for ($i = count($parts); $i > 0; $i--) {
            $testKey = implode('.', array_slice($parts, 0, $i));
            $remaining = array_slice($parts, $i);

            if (array_key_exists($testKey, $data)) {
                if (count($remaining) === 0) {
                    return is_string($data[$testKey]) ? $data[$testKey] : null;
                }
                if (is_array($data[$testKey])) {
                    return $this->getNestedValue($data[$testKey], $remaining);
                }

                return null;
            }
        }

        return null;
    }

    /**
     * Determine the default target language when none has been picked.
     *
     * @param  array<string>  $languages
     */
    private function determineDefaultTargetLanguage(array $languages): string
    {
        return collect($languages)
            ->first(fn (string $code): bool => $code !== 'en') ?? 'en';
    }
}
