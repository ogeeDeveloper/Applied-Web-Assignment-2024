SET FOREIGN_KEY_CHECKS = 0;

-- Drop all tables
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS status_change_logs;
DROP TABLE IF EXISTS system_logs;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS media_files;
DROP TABLE IF EXISTS saved_products;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS harvests;
DROP TABLE IF EXISTS chemical_usage;
DROP TABLE IF EXISTS plantings;
DROP TABLE IF EXISTS crop_types;
DROP TABLE IF EXISTS farmer_profiles;
DROP TABLE IF EXISTS customer_profiles;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS migrations;

-- Drop stored procedures and events
DROP PROCEDURE IF EXISTS cleanup_old_logs;
DROP EVENT IF EXISTS cleanup_logs_event;

-- Recreate the migrations table
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(255) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL COMMENT 'Migration file name',
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
