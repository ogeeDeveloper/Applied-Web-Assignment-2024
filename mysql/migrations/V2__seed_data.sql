START TRANSACTION;

-- Seed initial users with different roles
INSERT INTO users (name, email, password, role, status) VALUES
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('John Farmer', 'john@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'active'),
('Mary Farmer', 'mary@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'active'),
('Alice Customer', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Bob Customer', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- Seed roles
INSERT INTO roles (id, name, description) VALUES
(1, 'admin', 'Full system access'),
(2, 'farmer', 'Farmer access level'),
(3, 'customer', 'Customer access level');

-- Seed permissions
INSERT INTO permissions (id, name, description) VALUES
(1, 'manage_users', 'Can manage user accounts'),
(2, 'manage_products', 'Can manage products'),
(3, 'manage_orders', 'Can manage orders'),
(4, 'view_reports', 'Can view reports');

-- Seed role_permissions (link roles and permissions)
-- Ensure role_id and permission_id values match those in roles and permissions tables
INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), -- Admin has all permissions
(2, 2), (2, 3),                -- Farmer can manage products and orders
(3, 3);                        -- Customer can manage their orders

-- Assign roles to users
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1), -- Admin
(2, 2), -- Farmer
(3, 2), -- Farmer
(4, 3), -- Customer
(5, 3); -- Customer

-- Seed customer profiles
INSERT INTO customer_profiles (user_id, address, phone_number, preferences) VALUES
(4, '123 Main St, City', '+1234567890', '{"preferred_delivery_time": "morning", "organic_preference": true}'),
(5, '456 Oak St, Town', '+1234567891', '{"preferred_delivery_time": "evening", "organic_preference": false}');

-- Seed farmer profiles
INSERT INTO farmer_profiles (user_id, farm_name, location, farm_type, farm_size, farming_experience, organic_certified, phone_number) VALUES
(2, 'Green Valley Farm', 'Northern Region', 'vegetables', '5 hectares', 10, true, '+1234567892'),
(3, 'Sunny Fields', 'Southern Region', 'mixed', '3 hectares', 5, false, '+1234567893');

-- Seed crop types
INSERT INTO crop_types (name, category, description, typical_growth_duration, growing_season, recommended_soil_type, growing_guidelines) VALUES
('Tomatoes', 'vegetables', 'Fresh organic tomatoes', 80, 'Spring-Summer', 'Loamy', 'Plant in well-drained soil with full sun exposure'),
('Carrots', 'vegetables', 'Orange root vegetables', 70, 'Spring-Fall', 'Sandy loam', 'Requires deep, loose soil for proper root development'),
('Lettuce', 'vegetables', 'Crispy green lettuce', 45, 'Year-round', 'Well-drained', 'Good for greenhouse and open field cultivation'),
('Sweet Corn', 'vegetables', 'Golden sweet corn', 90, 'Summer', 'Rich loam', 'Requires full sun and regular watering'),
('Bell Peppers', 'vegetables', 'Colorful bell peppers', 70, 'Spring-Summer', 'Loamy', 'Start indoors and transplant after frost');

-- Seed plantings
INSERT INTO plantings (farmer_id, crop_type_id, field_location, area_size, planting_date, expected_harvest_date, status, growing_method, soil_preparation) VALUES
(1, 1, 'Field A1', 0.5, '2024-02-01', '2024-04-22', 'growing', 'Traditional', 'Tilled and fertilized with compost'),
(1, 2, 'Field A2', 0.3, '2024-02-15', '2024-04-26', 'growing', 'Organic', 'Double-dug beds with organic matter');

-- Seed harvests
INSERT INTO harvests (planting_id, harvest_date, quantity, unit, quality_grade, storage_location) VALUES
(1, '2024-04-15', 500, 'kg', 'A', 'Warehouse A');

-- Seed products
INSERT INTO products (farmer_id, harvest_id, name, category, description, price_per_unit, unit_type, stock_quantity, organic_certified) VALUES
(1, 1, 'Fresh Organic Lettuce', 'vegetables', 'Crispy and fresh lettuce heads', 2.99, 'head', 400, true);

-- Record this migration
-- INSERT INTO migrations (version, name) VALUES ('2.0', 'V2__seed_data.sql');

COMMIT;
