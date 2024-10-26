#!/bin/sh
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! mysqladmin ping -h"$MYSQL_HOST" --silent; do
    sleep 1
done

echo "MySQL is ready! Running migrations..."

# Update path to match your public directory
php /var/www/public/run-migrations.php

# Start PHP-FPM
exec php-fpm