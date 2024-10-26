-- Start the transaction
START TRANSACTION;

CREATE DATABASE IF NOT EXISTS crop_production_db;
USE crop_production_db;

-- Seed user data
INSERT INTO users (name, email, password, role, status) VALUES
    ('System Admin', 'admin@agrikonnect.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
    ('John Smith', 'john.smith@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'active'),
    ('Maria Garcia', 'maria.garcia@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'active'),
    ('Alice Johnson', 'alice.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- Seed farmer profiles
INSERT INTO farmer_profiles (user_id, farm_name, location, farm_type, farm_size, farming_experience, primary_products, organic_certified, phone_number, status) 
SELECT id,
       CONCAT(name, '''s Farm'),
       CASE WHEN id % 3 = 0 THEN 'Kingston, Jamaica'
            WHEN id % 3 = 1 THEN 'Montego Bay, Jamaica'
            ELSE 'Spanish Town, Jamaica'
       END,
       CASE WHEN id % 4 = 0 THEN 'vegetables'
            WHEN id % 4 = 1 THEN 'fruits'
            ELSE 'mixed'
       END,
       CONCAT(FLOOR(RAND() * 50 + 10), ' acres'),
       FLOOR(RAND() * 20 + 5),
       CASE WHEN id % 3 = 0 THEN 'Tomatoes, Peppers, Lettuce'
            ELSE 'Mixed Vegetables and Fruits'
       END,
       id % 2 = 0,
       CONCAT('876', LPAD(FLOOR(RAND() * 9999999), 7, '0')),
       'active'
FROM users WHERE role = 'farmer';

-- Seed customer profiles
INSERT INTO customer_profiles (user_id, address, phone_number, preferences, profile_picture)
SELECT id,
       CASE WHEN id % 3 = 0 THEN '123 Main St, Kingston'
            ELSE '456 Beach Rd, Montego Bay'
       END,
       CONCAT('876', LPAD(FLOOR(RAND() * 9999999), 7, '0')),
       JSON_OBJECT(
           'preferred_categories', JSON_ARRAY('vegetables'),
           'delivery_preference', 'morning'
       ),
       NULL
FROM users WHERE role = 'customer';

-- Seed products using CROSS JOIN with product templates
INSERT INTO products (farmer_id, name, category, description, price_per_unit, unit_type, stock_quantity, organic_certified, is_gmo, status)
SELECT 
    fp.farmer_id,
    pt.name,
    pt.category,
    pt.description,
    pt.price_per_unit,
    pt.unit_type,
    FLOOR(RAND() * 100 + 20),
    pt.organic_certified,
    FALSE,
    'available'
FROM farmer_profiles fp
CROSS JOIN (
    SELECT 'Tomatoes' AS name, 'vegetables' AS category, 'Fresh ripe tomatoes' AS description, 3.99 AS price_per_unit, 'kg' AS unit_type, TRUE AS organic_certified
    UNION ALL
    SELECT 'Lettuce', 'vegetables', 'Crisp lettuce', 2.99, 'head', TRUE
    UNION ALL
    SELECT 'Carrots', 'vegetables', 'Organic carrots', 2.49, 'kg', TRUE
    UNION ALL
    SELECT 'Mangoes', 'fruits', 'Sweet mangoes', 5.99, 'dozen', FALSE
) pt;

-- Record migration completion
INSERT INTO migrations (version) VALUES ('3.0');

-- Commit the transaction
COMMIT;
