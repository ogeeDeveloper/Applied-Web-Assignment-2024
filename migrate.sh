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

# Use MSYS_NO_PATHCONV to prevent Git Bash from converting paths
MSYS_NO_PATHCONV=1 docker-compose exec php php public/migrations.php "$@"