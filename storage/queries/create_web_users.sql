-- Start transaction
START TRANSACTION;

-- Admin user - Update if exists, insert if not
INSERT INTO users (name, email, password, role, status, created_at, updated_at)
VALUES (
    'System Administrator',
    'admin@agrikonnect.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    updated_at = CURRENT_TIMESTAMP;

-- Farmer user - Update if exists, insert if not
INSERT INTO users (name, email, password, role, status, created_at, updated_at)
VALUES (
    'John Smith',
    'farmer@agrikonnect.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'farmer',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    updated_at = CURRENT_TIMESTAMP;

-- Get the farmer's ID (whether it was just inserted or already existed)
SET @farmer_user_id = (SELECT id FROM users WHERE email = 'farmer@agrikonnect.com');

-- Create/Update Farmer Profile if user exists
INSERT INTO farmer_profiles (
    user_id,
    farm_name,
    location,
    farm_type,
    farm_size,
    farming_experience,
    primary_products,
    organic_certified,
    phone_number,
    status,
    created_at,
    updated_at
)
VALUES (
    @farmer_user_id,
    'Green Valley Farm',
    'Kingston, Jamaica',
    'mixed',
    '5 hectares',
    10,
    'vegetables, fruits',
    true,
    '876-555-0100',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE
    farm_name = VALUES(farm_name),
    location = VALUES(location),
    farm_type = VALUES(farm_type),
    updated_at = CURRENT_TIMESTAMP;

-- Customer user - Update if exists, insert if not
INSERT INTO users (name, email, password, role, status, created_at, updated_at)
VALUES (
    'Jane Doe',
    'customer@agrikonnect.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'customer',
    'active',
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    updated_at = CURRENT_TIMESTAMP;

-- Get the customer's ID (whether it was just inserted or already existed)
SET @customer_user_id = (SELECT id FROM users WHERE email = 'customer@agrikonnect.com');

-- Create/Update Customer Profile if user exists
INSERT INTO customer_profiles (
    user_id,
    address,
    phone_number,
    preferences,
    created_at,
    updated_at
)
VALUES (
    @customer_user_id,
    '123 Main Street, Kingston, Jamaica',
    '876-555-0200',
    JSON_OBJECT(
        'preferred_delivery_time', 'morning',
        'organic_preference', true,
        'notification_preferences', JSON_OBJECT(
            'email', true,
            'sms', true
        )
    ),
    CURRENT_TIMESTAMP,
    CURRENT_TIMESTAMP
)
ON DUPLICATE KEY UPDATE
    address = VALUES(address),
    phone_number = VALUES(phone_number),
    preferences = VALUES(preferences),
    updated_at = CURRENT_TIMESTAMP;

-- Ensure role assignments exist
INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT id, (SELECT id FROM roles WHERE name = 'admin')
FROM users WHERE email = 'admin@agrikonnect.com';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT id, (SELECT id FROM roles WHERE name = 'farmer')
FROM users WHERE email = 'farmer@agrikonnect.com';

INSERT IGNORE INTO user_roles (user_id, role_id)
SELECT id, (SELECT id FROM roles WHERE name = 'customer')
FROM users WHERE email = 'customer@agrikonnect.com';

COMMIT;