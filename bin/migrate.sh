#!/bin/bash

# Exit on error
set -e

# Check if script is run with arguments
if [ $# -eq 0 ]; then
    echo "Usage: ./migrate.sh [command]"
    echo "Commands:"
    echo "  status   - Show migration status"
    echo "  migrate  - Run pending migrations"
    exit 1
fi

# Run the migration command in the PHP container
docker-compose exec php php /var/www/public/migrations.php "$@"
