CREATE DATABASE crop_production_db;

USE crop_production_db;

-- Create the customer table
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    phone_number VARCHAR(10)
);

-- Create the farmers table
CREATE TABLE farmers (
    farmer_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    farm_name VARCHAR(255),
    location VARCHAR(255),
    type_of_farmer VARCHAR(255),
    description TEXT,
    phone_number VARCHAR(10),
    media JSON
);

-- Create the products table
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT,
    price_per_unit DECIMAL(10, 2) NOT NULL,
    stock_quantity INT DEFAULT 0 NOT NULL,
    availability BOOLEAN DEFAULT TRUE,
    status VARCHAR(50) NOT NULL,
    delivery_option VARCHAR(100) NOT NULL,
    FOREIGN KEY (farmer_id) REFERENCES farmers(farmer_id)
);

-- Create the crops table
CREATE TABLE crops (
    crop_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    planting_date DATE,
    expected_harvest DATE,
    growth_duration INT,
    current_status VARCHAR(50),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);

-- Create the chemical_usage table
CREATE TABLE chemical_usage (
    chemical_usage_id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    chemical_name VARCHAR(255) NOT NULL,
    date_applied DATE,
    purpose VARCHAR(255),
    amount_used DECIMAL(10,2),
    FOREIGN KEY (crop_id) REFERENCES crops(crop_id)
);

-- Create the orders table
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity_ordered INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    ordered_date DATE NOT NULL,
    delivery_option VARCHAR(50),
    ordered_status VARCHAR(50),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);