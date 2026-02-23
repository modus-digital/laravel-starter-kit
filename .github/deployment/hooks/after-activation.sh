php_executable=$1

"$php_executable" artisan queue:restart

if [[ $("$php_executable" artisan list) =~ "horizon:terminate" ]]; then
  echo "Restarting Laravel Horizon."

  "$php_executable" artisan horizon:terminate
fi
