<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations\Concerns;

use function collect;
use function config;
use function data_forget;
use function str_contains;

trait FiltersModuleTranslations
{
    /**
     * Get explicit translation key mappings for specific modules and groups.
     *
     * @return array<string, string|array<string, array<int, string>>>
     */
    private function getModuleTranslationKeyMap(): array
    {
        return [
            'registration' => 'register',
            'socialite' => 'socialite_providers',
        ];
    }

    /**
     * Remove translations that belong to disabled optional modules within a given group.
     *
     * @param  array<string, mixed>  $translations
     * @return array<string, mixed>
     */
    private function filterDisabledModuleTranslations(array $translations, string $group): array
    {
        /** @var array<int, string> $disabledModules */
        $disabledModules = collect(config('modules', []))
            ->filter(
                fn (mixed $moduleConfig): bool => is_array($moduleConfig)
                    && array_key_exists('enabled', $moduleConfig)
                    && $moduleConfig['enabled'] === false,
            )
            ->keys()
            ->all();

        $moduleKeyMap = $this->getModuleTranslationKeyMap();

        foreach ($disabledModules as $module) {
            // Generic convention-based cleanup based on module name.
            if ($group === 'navigation') {
                data_forget($translations, "labels.{$module}");
            }

            if ($group === 'admin') {
                data_forget($translations, $module);
            }

            // Additional or overridden keys from the lookup table.
            if (! array_key_exists($module, $moduleKeyMap)) {
                continue;
            }

            $mapped = $moduleKeyMap[$module];

            // String: treat as pattern and remove any keys that contain it at any nesting level.
            if (is_string($mapped)) {
                $translations = $this->forgetKeysContaining($translations, $mapped);

                continue;
            }

            // Array: group => [keys...] mapping.
            if (is_array($mapped)) {
                $groupSpecificKeys = $mapped[$group] ?? [];

                foreach ($groupSpecificKeys as $key) {
                    data_forget($translations, $key);
                }
            }
        }

        return $translations;
    }

    /**
     * Recursively remove any entries whose key contains the given needle.
     *
     * @param  array<string, mixed>  $translations
     * @return array<string, mixed>
     */
    private function forgetKeysContaining(array $translations, string $needle): array
    {
        foreach ($translations as $key => $value) {
            if (str_contains((string) $key, $needle)) {
                unset($translations[$key]);

                continue;
            }

            if (is_array($value)) {
                $translations[$key] = $this->forgetKeysContaining($value, $needle);
            }
        }

        return $translations;
    }

    /**
     * Apply module-based filtering to a specific root group.
     *
     * @param  array<string, mixed>  $groupTranslations
     * @return array<string, mixed>
     */
    public function filterGroupTranslationsByModules(array $groupTranslations, string $group): array
    {
        return $this->filterDisabledModuleTranslations($groupTranslations, $group);
    }
}

