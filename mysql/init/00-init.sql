-- -- Create admin user with proper permissions and access from any host
-- CREATE USER IF NOT EXISTS 'admin'@'%' IDENTIFIED BY 'Test@000';
-- CREATE USER IF NOT EXISTS 'admin'@'localhost' IDENTIFIED BY 'Test@000';
-- CREATE USER IF NOT EXISTS 'admin'@'172.18.0.3' IDENTIFIED BY 'Test@000';

-- -- Grant privileges to admin user
-- GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%' WITH GRANT OPTION;
-- GRANT ALL PRIVILEGES ON *.* TO 'admin'@'localhost' WITH GRANT OPTION;
-- GRANT ALL PRIVILEGES ON *.* TO 'admin'@'172.18.0.3' WITH GRANT OPTION;

-- -- Flush privileges to apply changes
-- FLUSH PRIVILEGES;

-- Create user dynamically using environment variables
CREATE USER IF NOT EXISTS '{{MYSQL_USER}}'@'%' IDENTIFIED BY '{{MYSQL_PASSWORD}}';
GRANT ALL PRIVILEGES ON {{MYSQL_DATABASE}}.* TO '{{MYSQL_USER}}'@'%';
FLUSH PRIVILEGES;
