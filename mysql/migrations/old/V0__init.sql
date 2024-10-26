-- V0__init

-- Create and use database
CREATE DATABASE IF NOT EXISTS crop_production_db;
USE crop_production_db;

-- Set proper charset and collation
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET collation_connection = utf8mb4_unicode_ci;

-- Disable foreign key checks during initialization
SET FOREIGN_KEY_CHECKS = 0;

-- Create migrations table if it doesn't exist
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(255) NOT NULL UNIQUE,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;