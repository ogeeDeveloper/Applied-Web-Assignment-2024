START TRANSACTION;

-- Disable foreign key checks during setup
SET FOREIGN_KEY_CHECKS = 0;

-- -- Drop existing tables in reverse order of dependencies
-- DROP TABLE IF EXISTS chemical_usage;
-- DROP TABLE IF EXISTS harvests;
-- DROP TABLE IF EXISTS plantings;
-- DROP TABLE IF EXISTS crop_types;
-- DROP TABLE IF EXISTS order_items;
-- DROP TABLE IF EXISTS orders;
-- DROP TABLE IF EXISTS products;
-- DROP TABLE IF EXISTS user_roles;
-- DROP TABLE IF EXISTS role_permissions;
-- DROP TABLE IF EXISTS permissions;
-- DROP TABLE IF EXISTS roles;
-- DROP TABLE IF EXISTS farmer_profiles;
-- DROP TABLE IF EXISTS customer_profiles;
-- DROP TABLE IF EXISTS users;
-- DROP TABLE IF EXISTS migrations;

-- -- Create migrations table
-- CREATE TABLE migrations (
--     id INT AUTO_INCREMENT PRIMARY KEY,
--     version VARCHAR(255) NOT NULL UNIQUE,
--     name VARCHAR(255) NOT NULL COMMENT 'Migration file name',
--     executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create users table (base table with no dependencies)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'farmer', 'admin') NOT NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create roles and permissions tables
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create profile tables
CREATE TABLE customer_profiles (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    address VARCHAR(255),
    phone_number VARCHAR(15),
    preferences JSON,
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE farmer_profiles (
    farmer_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    farm_name VARCHAR(255),
    location VARCHAR(255),
    farm_type ENUM('vegetables', 'fruits', 'rice', 'mixed') NOT NULL,
    farm_size VARCHAR(255),
    farming_experience INT,
    primary_products TEXT,
    organic_certified BOOLEAN DEFAULT FALSE,
    phone_number VARCHAR(15),
    additional_info TEXT,
    status ENUM('pending', 'active', 'inactive', 'suspended') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_farmer_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create crop_types table (formerly crops)
CREATE TABLE crop_types (
    crop_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    typical_growth_duration INT COMMENT 'in days',
    growing_season VARCHAR(100),
    recommended_soil_type VARCHAR(100),
    growing_guidelines TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_crop_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create plantings table
CREATE TABLE plantings (
    planting_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    crop_type_id INT NOT NULL,
    field_location VARCHAR(255) NOT NULL,
    area_size DECIMAL(10,2),
    area_unit VARCHAR(20) DEFAULT 'hectares',
    planting_date DATE NOT NULL,
    expected_harvest_date DATE,
    actual_harvest_date DATE,
    status ENUM('planned', 'planted', 'growing', 'harvested', 'failed') DEFAULT 'planned',
    growing_method VARCHAR(100),
    soil_preparation TEXT,
    weather_conditions TEXT,
    irrigation_method VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(farmer_id),
    FOREIGN KEY (crop_type_id) REFERENCES crop_types(crop_id),
    INDEX idx_planting_status (status),
    INDEX idx_planting_date (planting_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create harvests table
CREATE TABLE harvests (
    harvest_id INT AUTO_INCREMENT PRIMARY KEY,
    planting_id INT NOT NULL,
    harvest_date DATE NOT NULL,
    quantity DECIMAL(10,2) NOT NULL,
    unit VARCHAR(20) NOT NULL,
    quality_grade VARCHAR(10),
    loss_quantity DECIMAL(10,2) DEFAULT 0,
    loss_reason TEXT,
    storage_location VARCHAR(255),
    storage_conditions TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    INDEX idx_harvest_date (harvest_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create products table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    harvest_id INT,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    unit_type VARCHAR(50) NOT NULL DEFAULT 'kg',
    stock_quantity INT DEFAULT 0 NOT NULL,
    low_stock_alert_threshold INT DEFAULT 10,
    availability BOOLEAN DEFAULT TRUE,
    organic_certified BOOLEAN DEFAULT FALSE,
    is_gmo BOOLEAN DEFAULT FALSE,
    status ENUM('available', 'out_of_stock', 'discontinued') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(farmer_id) ON DELETE RESTRICT,
    FOREIGN KEY (harvest_id) REFERENCES harvests(harvest_id),
    INDEX idx_product_status (status),
    INDEX idx_product_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create chemical_usage table
CREATE TABLE chemical_usage (
    chemical_usage_id INT AUTO_INCREMENT PRIMARY KEY,
    planting_id INT NOT NULL,
    chemical_name VARCHAR(255) NOT NULL,
    chemical_type ENUM('pesticide', 'fertilizer', 'herbicide', 'other') NOT NULL,
    date_applied DATE,
    purpose VARCHAR(255),
    amount_used DECIMAL(10,2),
    unit_of_measurement VARCHAR(50),
    safety_period_days INT,
    weather_conditions TEXT,
    application_method VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (planting_id) REFERENCES plantings(planting_id),
    INDEX idx_chemical_date (date_applied)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT NOT NULL,
    delivery_notes TEXT DEFAULT NULL,
    ordered_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customer_profiles(customer_id) ON DELETE RESTRICT,
    INDEX idx_order_status (order_status),
    INDEX idx_order_date (ordered_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create order_items table
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Record this migration
-- INSERT INTO migrations (version, name) VALUES ('1.0', 'V1__complete_schema.sql');

COMMIT;