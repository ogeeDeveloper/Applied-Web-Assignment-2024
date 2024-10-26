#!/bin/bash
set -e

mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < /docker-entrypoint-initdb.d/00-init.sql

# Run migrations in order
for migration in /docker-entrypoint-initdb.d/migrations/V*__*.sql
do
    echo "Running migration: $migration"
    mysql -u"$MYSQL_USER" -p"$MYSQL_PASSWORD" "$MYSQL_DATABASE" < "$migration"
done
