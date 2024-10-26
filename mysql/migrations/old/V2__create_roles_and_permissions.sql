-- V2__create_roles_and_permissions

START TRANSACTION;
CREATE DATABASE IF NOT EXISTS crop_production_db;
USE crop_production_db;

-- Insert base roles if they don't exist
INSERT IGNORE INTO roles (name, description) VALUES
('admin', 'System administrator with full access'),
('farmer', 'Farmer with product management capabilities'),
('customer', 'Regular customer with ordering capabilities');

-- Insert base permissions if they don't exist
INSERT IGNORE INTO permissions (name, description) VALUES
('manage_users', 'Can create, update, and delete users'),
('manage_products', 'Can manage product listings'),
('manage_orders', 'Can manage orders'),
('view_reports', 'Can view system reports'),
('manage_profile', 'Can manage own profile');


-- Record this migration
INSERT INTO migrations (version) VALUES ('2.0');

COMMIT;