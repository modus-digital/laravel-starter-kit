<?php

declare(strict_types=1);

namespace App\Traits;

use App\Enums\RBAC\Permission;
use Illuminate\Support\Str;

/**
 * Trait Searchable
 *
 * Makes a model searchable in the global search.
 *
 * Usage:
 *   1. Add `use Searchable;` to your model
 *   2. Define `protected static array $searchable = ['name', 'email'];` with columns to search
 *   3. Optionally define `protected static string $searchPermission = 'read:users';`
 *   4. Optionally define `protected static string $searchRouteName = 'admin.users.show';`
 *
 * If not specified, the trait will:
 *   - Search the 'name' column by default
 *   - Derive the route name from the model name (e.g., User -> admin.users.show)
 *   - Use no permission check (all authenticated users can search)
 */
trait Searchable
{
    /**
     * Get the columns that should be searched.
     * Override by defining: protected static array $searchable = ['name', 'email'];
     *
     * @return array<int, string>
     */
    public static function getSearchableColumns(): array
    {
        return static::$searchable ?? ['name'];
    }

    /**
     * Get the permission required to search this model.
     * Override by defining: protected static string $searchPermission = 'read:users';
     * Returns null if no permission is required.
     */
    public static function getSearchPermission(): ?Permission
    {
        if (! isset(static::$searchPermission)) {
            return null;
        }

        // Try to find the matching Permission enum case from the string value
        return Permission::tryFrom(static::$searchPermission);
    }

    /**
     * Get the route name for showing a single model instance.
     * Override by defining: protected static string $searchRouteName = 'admin.users.show';
     *
     * By default, derives from model name: User -> admin.users.show, Client -> admin.clients.show
     */
    public static function getShowRouteName(): string
    {
        if (isset(static::$searchRouteName)) {
            return static::$searchRouteName;
        }

        // Derive from class name: User -> admin.users.show
        $modelName = class_basename(static::class);
        $pluralName = Str::plural(Str::snake($modelName, '-'));

        return "admin.{$pluralName}.show";
    }

    /**
     * Get the icon identifier for this model type in search results.
     * Override by defining: protected static string $searchIcon = 'user';
     */
    public static function getSearchResultIcon(): ?string
    {
        return static::$searchIcon ?? null;
    }

    /**
     * Get the model type name for grouping search results.
     * Override by defining: protected static string $searchType = 'User';
     */
    public static function getSearchResultType(): string
    {
        return static::$searchType ?? class_basename(static::class);
    }

    /**
     * Perform a search query on this model.
     *
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    public static function performSearch(string $query, int $limit = 10)
    {
        $columns = static::getSearchableColumns();

        $queryBuilder = static::query();

        // Exclude soft deleted models if the model uses SoftDeletes
        if (in_array(\Illuminate\Database\Eloquent\SoftDeletes::class, class_uses_recursive(static::class), true)) {
            $queryBuilder->whereNull('deleted_at');
        }

        return $queryBuilder
            ->where(function ($q) use ($query, $columns): void {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$query}%");
                }
            })
            ->limit($limit);
    }

    /**
     * Get the display label for this search result.
     * Override this method for custom labels.
     */
    public function getSearchResultLabel(): string
    {
        return $this->name ?? (string) $this->getKey();
    }

    /**
     * Get the subtitle for this search result.
     * Override this method for custom subtitles.
     */
    public function getSearchResultSubtitle(): ?string
    {
        return $this->email ?? null;
    }
}
