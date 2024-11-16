SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

-- Insert admin user if not exists
INSERT IGNORE INTO users (name, email, password, role, status, created_at, updated_at)
VALUES (
    'System Administrator' COLLATE utf8mb4_unicode_ci,
    'admin@agrikonnect.com' COLLATE utf8mb4_unicode_ci,
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' COLLATE utf8mb4_unicode_ci,
    'admin' COLLATE utf8mb4_unicode_ci,
    'active' COLLATE utf8mb4_unicode_ci,
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);

-- Ensure admin role exists
INSERT IGNORE INTO roles (name, description)
VALUES ('admin' COLLATE utf8mb4_unicode_ci, 'System administrator with full access' COLLATE utf8mb4_unicode_ci);

-- Ensure all permissions exist
INSERT IGNORE INTO permissions (name, description) VALUES
('manage_users' COLLATE utf8mb4_unicode_ci, 'Can create, update, and delete users' COLLATE utf8mb4_unicode_ci),
('manage_products' COLLATE utf8mb4_unicode_ci, 'Can manage product listings' COLLATE utf8mb4_unicode_ci),
('manage_orders' COLLATE utf8mb4_unicode_ci, 'Can manage orders' COLLATE utf8mb4_unicode_ci),
('view_reports' COLLATE utf8mb4_unicode_ci, 'Can view system reports' COLLATE utf8mb4_unicode_ci),
('manage_profile' COLLATE utf8mb4_unicode_ci, 'Can manage own profile' COLLATE utf8mb4_unicode_ci),
('manage_farmers' COLLATE utf8mb4_unicode_ci, 'Can manage farmer accounts' COLLATE utf8mb4_unicode_ci),
('manage_customers' COLLATE utf8mb4_unicode_ci, 'Can manage customer accounts' COLLATE utf8mb4_unicode_ci),
('manage_system' COLLATE utf8mb4_unicode_ci, 'Can manage system settings' COLLATE utf8mb4_unicode_ci);

-- Grant all permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT 
    (SELECT id FROM roles WHERE name = 'admin' COLLATE utf8mb4_unicode_ci),
    id 
FROM permissions;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('6.0', 'V6__insert_admin_user.sql');

COMMIT;