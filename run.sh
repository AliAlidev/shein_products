#!/bin/bash
#sleep 20

#echo "Running migrations ..."
#php artisan migrate --force

#echo "Checking migration status ... "
#php artisan migrate:status

echo "Linking storage ... "
php artisan storage:link

echo "Starting server ..."
php artisan serve --port=$PORT --host=0.0.0.0 --env=.env
