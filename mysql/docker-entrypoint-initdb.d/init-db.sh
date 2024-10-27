#!/bin/bash
set -e

# Substitute environment variables into the SQL file and save it to a temporary file
temp_init_sql=$(mktemp)
sed "s/{{MYSQL_USER}}/$MYSQL_USER/g; s/{{MYSQL_PASSWORD}}/$MYSQL_PASSWORD/g; s/{{MYSQL_DATABASE}}/$MYSQL_DATABASE/g" /docker-entrypoint-initdb.d/init/00-init.sql > "$temp_init_sql"

# Use root user to initialize the database with the updated SQL
echo "Running initial SQL setup..."
mysql -uroot -p"$MYSQL_ROOT_PASSWORD" < "$temp_init_sql" || {
    echo "Failed to execute initial SQL setup."
    exit 1
}

# Run migrations in order using root user
for migration in /docker-entrypoint-initdb.d/migrations/V*__*.sql
do
    echo "Running migration: $migration"
    if ! mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE" < "$migration"; then
        echo "Failed to run migration: $migration"
        exit 1
    fi
    echo "Successfully ran migration: $migration"
done

# Clean up temporary SQL file
rm "$temp_init_sql"
