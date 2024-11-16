#!/bin/sh
set -e

# Check if vendor directory exists in container and install dependencies if needed
if [ ! "$(ls -A /var/www/vendor 2>/dev/null)" ]; then
    echo "Vendor directory is empty. Installing dependencies..."
    cd /var/www
    composer update --no-dev --optimize-autoloader
    
    # Ensure correct permissions
    chown -R www-data:www-data /var/www/vendor
fi

# Validate MySQL environment variables
if [ -z "$MYSQL_HOST" ] || [ -z "$MYSQL_USER" ] || [ -z "$MYSQL_PASSWORD" ]; then
    echo "Error: MySQL environment variables are not set."
    exit 1
fi

# Function to wait for MySQL
wait_for_mysql() {
    echo "Waiting for MySQL to be ready..."
    for i in $(seq 1 30); do
        if mysqladmin ping -h"$MYSQL_HOST" -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" --silent; then
            echo "MySQL is ready!"
            return 0
        fi
        echo "Attempt $i/30: MySQL not ready yet..."
        sleep 2
    done
    echo "MySQL did not become ready in time"
    return 1
}

# Wait for MySQL
wait_for_mysql || exit 1

# Always run migrations using PHP migration manager
echo "Running migrations using PHP migration manager..."
php /var/www/public/run-migrations.php || {
    echo "Migrations failed."
    exit 1
}

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm