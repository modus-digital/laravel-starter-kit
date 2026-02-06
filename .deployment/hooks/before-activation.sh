php_executable=$1

# Clearing old caches
"$php_executable" artisan optimize:clear
"$php_executable" artisan filament:optimize-clear

# Creating a symlink to the storage directory.
"$php_executable" artisan storage:link

# Caching configuration, routes, and views
"$php_executable" artisan event:cache
"$php_executable" artisan config:cache
"$php_executable" artisan route:cache
"$php_executable" artisan view:cache

# Filament optimization
"$php_executable" artisan filament:optimize

# Running migrations
"$php_executable" artisan migrate --force

# Syncing changelogs
"$php_executable" artisan changelog:sync

# Regenerate API Docs
"$php_executable" artisan l5-swagger:generate
