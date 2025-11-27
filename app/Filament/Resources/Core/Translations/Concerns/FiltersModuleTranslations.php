<?php

declare(strict_types=1);

namespace App\Filament\Resources\Core\Translations\Concerns;

use Illuminate\Support\Collection;

trait FiltersModuleTranslations
{
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

    /**
     * Get explicit translation key mappings for specific modules and groups.
     *
     * @return array<string, string>
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
        /** @var array<string, mixed> $modulesConfig */
        $modulesConfig = config('modules', []);

        /** @var Collection<string, mixed> $modulesCollection */
        $modulesCollection = collect($modulesConfig);

        /** @var Collection<string, mixed> $filteredModules */
        $filteredModules = $modulesCollection->filter(
            static fn (mixed $moduleConfig): bool => is_array($moduleConfig)
                && array_key_exists('enabled', $moduleConfig)
                && $moduleConfig['enabled'] === false
        );

        /** @var array<int, string> $disabledModules */
        $disabledModules = $filteredModules->keys()->all();

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
            $translations = $this->forgetKeysContaining($translations, $mapped);

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
}
