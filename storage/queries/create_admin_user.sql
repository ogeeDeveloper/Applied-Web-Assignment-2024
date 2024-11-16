-- Start transaction to ensure data consistency
START TRANSACTION;

-- Set variables for the admin user
SET @admin_email = 'admin@agrikonnect.com';
SET @admin_name = 'System Administrator';
-- Using a hashed version of 'admin123' - In real application, this should be properly hashed
SET @admin_password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

-- Insert the admin user into the users table
INSERT INTO users (
    name,
    email,
    password,
    role,
    status,
    created_at,
    updated_at
) VALUES (
    @admin_name,
    @admin_email,
    @admin_password,
    'admin',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
);

-- Store the new user's ID
SET @admin_user_id = LAST_INSERT_ID();

-- Make sure the admin role exists in the roles table
INSERT IGNORE INTO roles (name, description)
VALUES ('admin', 'System administrator with full access');

-- Get the admin role ID
SET @admin_role_id = (SELECT id FROM roles WHERE name = 'admin');

-- Ensure all permissions exist
INSERT IGNORE INTO permissions (name, description) VALUES
('manage_users', 'Can create, update, and delete users'),
('manage_products', 'Can manage product listings'),
('manage_orders', 'Can manage orders'),
('view_reports', 'Can view system reports'),
('manage_profile', 'Can manage own profile'),
('manage_farmers', 'Can manage farmer accounts'),
('manage_customers', 'Can manage customer accounts'),
('manage_system', 'Can manage system settings');

-- Grant all permissions to admin role
INSERT IGNORE INTO role_permissions (role_id, permission_id)
SELECT @admin_role_id, id FROM permissions;

-- Commit the transaction
COMMIT;

-- Verify the admin user was created (optional)
SELECT 
    u.id,
    u.name,
    u.email,
    u.role,
    u.status,
    COUNT(DISTINCT rp.permission_id) as permission_count
FROM users u
LEFT JOIN roles r ON r.name = u.role
LEFT JOIN role_permissions rp ON rp.role_id = r.id
WHERE u.email = @admin_email
GROUP BY u.id, u.name, u.email, u.role, u.status;