<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class SearchService
{
    /**
     * Get all models that use the Searchable trait.
     *
     * @return array<int, class-string<Model>>
     */
    public function getSearchableModels(): array
    {
        $models = [];

        // Get all model classes from common locations
        $modelPaths = [
            app_path('Models'),
            app_path('Models/Modules'),
        ];

        foreach ($modelPaths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            // Get files directly in the directory
            $directFiles = glob($path.'/*.php');

            // Get files in subdirectories
            $subdirFiles = glob($path.'/**/*.php');

            // Merge and remove duplicates
            $files = array_unique(array_merge($directFiles ?: [], $subdirFiles ?: []));

            foreach ($files as $file) {
                $className = $this->getClassNameFromFile($file);

                if ($className && $this->usesSearchableTrait($className)) {
                    $models[] = $className;
                }
            }
        }

        return array_unique($models);
    }

    /**
     * Search across all searchable models that the user has permission to access.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $query, User $user, int $limit = 10): array
    {
        if (in_array(mb_trim($query), ['', '0'], true)) {
            return [];
        }

        $searchableModels = $this->getSearchableModels();
        $permittedModels = $this->filterByPermission($searchableModels, $user);

        $results = [];

        foreach ($permittedModels as $modelClass) {
            $modelResults = $modelClass::performSearch($query, $limit)->get();

            foreach ($modelResults as $model) {
                $results[] = $this->formatResult($model);
            }
        }

        // Sort results by relevance (simple: by label match)
        usort($results, function (array $a, array $b) use ($query): int {
            $aScore = $this->calculateRelevanceScore($a['label'], $query);
            $bScore = $this->calculateRelevanceScore($b['label'], $query);

            return $bScore <=> $aScore;
        });

        // Limit total results
        return array_slice($results, 0, $limit);
    }

    /**
     * Filter models by user permissions.
     *
     * @param  array<int, class-string<Model>>  $models
     * @return array<int, class-string<Model>>
     */
    private function filterByPermission(array $models, User $user): array
    {
        return array_filter($models, function (string $modelClass) use ($user): bool {
            $permission = $modelClass::getSearchPermission();

            // No permission required - all authenticated users can search
            if ($permission === null) {
                return true;
            }

            return $user->hasPermissionTo($permission->value);
        });
    }

    /**
     * Format a model instance as a search result.
     *
     * @return array<string, mixed>
     */
    private function formatResult(Model $model): array
    {
        $routeName = $model::getShowRouteName();

        // Extract the route parameter name from the route name
        // e.g., 'admin.users.show' -> 'user', 'admin.clients.show' -> 'client'
        $routeParam = $this->getRouteParameterName($routeName);

        // Use the model instance directly - Laravel will resolve the route key automatically
        $url = route($routeName, [$routeParam => $model->getKey()]);

        return [
            'id' => (string) $model->getKey(),
            'type' => $model::getSearchResultType(),
            'label' => $model->getSearchResultLabel(),
            'subtitle' => $model->getSearchResultSubtitle(),
            'icon' => $model::getSearchResultIcon(),
            'url' => $url,
        ];
    }

    /**
     * Extract the route parameter name from a route name.
     * e.g., 'admin.users.show' -> 'user'
     */
    private function getRouteParameterName(string $routeName): string
    {
        // Split by dots and get the second-to-last segment (the resource name)
        $parts = explode('.', $routeName);

        if (count($parts) >= 2) {
            // Get the resource name (e.g., 'users' -> 'user', 'clients' -> 'client')
            $resource = $parts[count($parts) - 2];

            return Str::singular($resource);
        }

        // Fallback: try to extract from common patterns
        if (preg_match('/\.(\w+)\.show$/', $routeName, $matches)) {
            return Str::singular($matches[1]);
        }

        // Default fallback
        return 'id';
    }

    /**
     * Calculate a simple relevance score for a label against a query.
     */
    private function calculateRelevanceScore(string $label, string $query): int
    {
        $labelLower = mb_strtolower($label);
        $queryLower = mb_strtolower($query);

        // Exact match gets highest score
        if ($labelLower === $queryLower) {
            return 100;
        }

        // Starts with query gets high score
        if (Str::startsWith($labelLower, $queryLower)) {
            return 80;
        }

        // Contains query gets medium score
        if (Str::contains($labelLower, $queryLower)) {
            return 50;
        }

        return 0;
    }

    /**
     * Check if a class uses the Searchable trait.
     *
     * @param  class-string  $className
     */
    private function usesSearchableTrait(string $className): bool
    {
        if (! class_exists($className)) {
            return false;
        }

        $traits = class_uses_recursive($className);

        return in_array(Searchable::class, $traits, true);
    }

    /**
     * Extract class name from a PHP file.
     *
     * @return class-string|null
     */
    private function getClassNameFromFile(string $file): ?string
    {
        $content = file_get_contents($file);

        if (! $content) {
            return null;
        }

        // Extract namespace
        if (! preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            return null;
        }

        $namespace = $namespaceMatches[1];

        // Extract class name
        if (! preg_match('/\b(class|final\s+class|abstract\s+class)\s+(\w+)/', $content, $classMatches)) {
            return null;
        }

        $className = $classMatches[2];

        return $namespace.'\\'.$className;
    }
}
