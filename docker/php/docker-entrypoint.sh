#!/bin/bash

# Check if vendor directory exists in container and install dependencies if needed
if [ ! "$(ls -A /var/www/vendor 2>/dev/null)" ]; then
    echo "Vendor directory is empty. Installing dependencies..."
    cd /var/www
    composer update --no-dev --optimize-autoloader
   
    # Ensure correct permissions
    chown -R www-data:www-data /var/www/vendor
fi

# Start PHP-FPM
echo "Starting PHP-FPM..."
exec php-fpm