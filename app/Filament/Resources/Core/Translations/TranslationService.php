<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class TranslationService
{
    /**
     * Get all available language codes from the lang directory.
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
     */
    public function getLanguageFile(string $lang): array
    {
        $path = lang_path("{$lang}.json");

        if (! File::exists($path)) {
            return [];
        }

        $content = File::get($path);

        return json_decode($content, true) ?? [];
    }

    /**
     * Save a language file with proper formatting.
     */
    public function saveLanguageFile(string $lang, array $data): void
    {
        $path = lang_path("{$lang}.json");

        if ($data === []) {
            File::put($path, "{}\n");

            return;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        File::put($path, $json."\n");
    }

    /**
     * Get root groups (first-level keys) from English JSON.
     */
    public function getRootGroups(): array
    {
        $english = $this->getLanguageFile('en');

        return array_keys($english);
    }

    /**
     * Flatten nested translations to dot notation.
     */
    public function flattenTranslations(array $translations, string $prefix = ''): array
    {
        $result = [];

        foreach ($translations as $key => $value) {
            $newKey = $prefix !== '' && $prefix !== '0' ? "{$prefix}.{$key}" : $key;

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
     */
    public function getMissingTranslations(string $targetLang, ?string $group = null): array
    {
        $english = $this->getLanguageFile('en');
        $target = $this->getLanguageFile($targetLang);

        // Filter by group if specified
        if ($group !== null) {
            $english = $english[$group] ?? [];
            $target = $target[$group] ?? [];

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
     */
    public function getTranslationProgress(string $lang, string $group): array
    {
        $english = $this->getLanguageFile('en');
        $target = $this->getLanguageFile($lang);

        $englishGroup = $english[$group] ?? [];
        $targetGroup = $target[$group] ?? [];

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
     */
    public function setTranslation(string $lang, string $key, string $value): void
    {
        $data = $this->getLanguageFile($lang);

        data_set($data, $key, $value);

        $this->saveLanguageFile($lang, $data);
    }

    /**
     * Get a translation value using dot notation.
     */
    public function getTranslation(string $lang, string $key): ?string
    {
        $data = $this->getLanguageFile($lang);

        return data_get($data, $key);
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
