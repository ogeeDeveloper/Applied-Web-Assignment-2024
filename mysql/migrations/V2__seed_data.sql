START TRANSACTION;

-- Seed initial users with different roles
INSERT INTO users (name, email, password, role, status) VALUES
-- Password hash for 'password123' - you should use proper password hashing in production
('Admin User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active'),
('John Farmer', 'john@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'active'),
('Mary Farmer', 'mary@farm.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer', 'active'),
('Alice Customer', 'alice@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active'),
('Bob Customer', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'active');

-- Seed roles and permissions
INSERT INTO roles (name, description) VALUES
('admin', 'Full system access'),
('farmer', 'Farmer access level'),
('customer', 'Customer access level');

INSERT INTO permissions (name, description) VALUES
('manage_users', 'Can manage user accounts'),
('manage_products', 'Can manage products'),
('manage_orders', 'Can manage orders'),
('view_reports', 'Can view reports');

-- Link roles and permissions
INSERT INTO role_permissions (role_id, permission_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), -- Admin has all permissions
(2, 2), (2, 3), -- Farmer can manage products and orders
(3, 3);          -- Customer can manage their orders

-- Assign roles to users
INSERT INTO user_roles (user_id, role_id) VALUES
(1, 1), -- Admin
(2, 2), -- Farmers
(3, 2),
(4, 3), -- Customers
(5, 3);

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
(1, 2, 'Field A2', 0.3, '2024-02-15', '2024-04-26', 'growing', 'Organic', 'Double-dug beds with organic matter'),
(1, 3, 'Field B1', 0.2, '2024-03-01', '2024-04-15', 'harvested', 'Greenhouse', 'Raised beds with rich compost'),
(2, 4, 'Field C1', 1.0, '2024-03-15', '2024-06-13', 'planted', 'Traditional', 'Deep plowing with organic fertilizer'),
(2, 5, 'Field C2', 0.4, '2024-03-01', '2024-05-10', 'growing', 'Organic', 'Natural composting and mulching');

-- Seed harvests (for completed plantings)
INSERT INTO harvests (planting_id, harvest_date, quantity, unit, quality_grade, storage_location) VALUES
(3, '2024-04-15', 500, 'kg', 'A', 'Warehouse A'),
(1, '2024-04-22', 800, 'kg', 'A', 'Warehouse B');

-- Seed products
INSERT INTO products (farmer_id, harvest_id, name, category, description, price_per_unit, unit_type, stock_quantity, organic_certified) VALUES
(1, 1, 'Fresh Organic Lettuce', 'vegetables', 'Crispy and fresh lettuce heads', 2.99, 'head', 400, true),
(1, 2, 'Organic Tomatoes', 'vegetables', 'Ripe and juicy tomatoes', 3.99, 'kg', 600, true),
(2, NULL, 'Sweet Corn', 'vegetables', 'Fresh sweet corn', 1.99, 'ear', 0, false),
(2, NULL, 'Bell Peppers', 'vegetables', 'Mixed color bell peppers', 4.99, 'kg', 0, true);

-- Seed chemical usage (for active plantings)
INSERT INTO chemical_usage (planting_id, chemical_name, chemical_type, date_applied, purpose, amount_used, unit_of_measurement, safety_period_days) VALUES
(1, 'Organic Neem Oil', 'pesticide', '2024-03-01', 'Pest control', 2.5, 'liters', 7),
(2, 'Organic Compost Tea', 'fertilizer', '2024-03-15', 'Growth enhancement', 100, 'liters', 0),
(4, 'Natural Fertilizer', 'fertilizer', '2024-03-20', 'Soil enrichment', 50, 'kg', 0),
(5, 'Organic Pest Control', 'pesticide', '2024-03-25', 'Pest prevention', 3.0, 'liters', 5);

-- Seed orders
INSERT INTO orders (customer_id, order_status, payment_status, total_amount, delivery_address, delivery_notes) VALUES
(1, 'delivered', 'paid', 29.90, '123 Main St, City', 'Leave at front door'),
(1, 'processing', 'paid', 45.85, '123 Main St, City', 'Call upon arrival'),
(2, 'pending', 'pending', 19.95, '456 Oak St, Town', 'Delivery after 6 PM');

-- Seed order items
INSERT INTO order_items (order_id, product_id, quantity, unit_price, total_price) VALUES
(1, 1, 5, 2.99, 14.95),
(1, 2, 3, 3.99, 11.97),
(2, 1, 8, 2.99, 23.92),
(2, 2, 5, 3.99, 19.95),
(3, 1, 4, 2.99, 11.96);

COMMIT;