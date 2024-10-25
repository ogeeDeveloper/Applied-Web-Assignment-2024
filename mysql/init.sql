-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS ${MYSQL_DATABASE};

-- Set up permissions for application user
GRANT ALL PRIVILEGES ON ${MYSQL_DATABASE}.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;