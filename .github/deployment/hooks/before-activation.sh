php_executable=$1

# Running migrations
"$php_executable" artisan migrate --force

# Clearing old caches
"$php_executable" artisan optimize:clear

# Creating a symlink to the storage directory.
"$php_executable" artisan storage:link

# Caching configuration, routes, and views
"$php_executable" artisan event:cache
"$php_executable" artisan config:cache
"$php_executable" artisan route:cache
"$php_executable" artisan view:cache


# Generating api documentation
"$php_executable" artisan scribe:generate
