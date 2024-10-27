-- Disable foreign key checks for updates
SET FOREIGN_KEY_CHECKS = 0;

-- Add saved products table
CREATE TABLE IF NOT EXISTS saved_products (
    customer_id INT NOT NULL,
    product_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customer_profiles(customer_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Enhanced order status tracking
ALTER TABLE orders
    MODIFY COLUMN order_status ENUM(
        'pending',          -- Initial state when order is created
        'confirmed',        -- Order confirmed by system
        'processing',       -- Being prepared by farmer
        'ready_for_pickup', -- Ready for pickup (if pickup option)
        'out_for_delivery', -- Out for delivery (if delivery option)
        'delivered',        -- Successfully delivered
        'completed',        -- Order completed
        'cancelled',        -- Order cancelled
        'refunded'         -- Order refunded
    ) NOT NULL DEFAULT 'pending',
    ADD COLUMN status_history JSON DEFAULT NULL COMMENT 'Array of status changes with timestamps',
    ADD COLUMN estimated_delivery DATETIME DEFAULT NULL,
    ADD COLUMN actual_delivery DATETIME DEFAULT NULL,
    ADD COLUMN cancellation_reason TEXT DEFAULT NULL;

-- Create media files table for better media management
CREATE TABLE media_files (
    file_id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('product', 'farmer_profile', 'customer_profile', 'harvest') NOT NULL,
    entity_id INT NOT NULL,
    file_type ENUM('image', 'video', 'document') NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('processing', 'active', 'deleted') DEFAULT 'processing',
    metadata JSON DEFAULT NULL COMMENT 'Additional file metadata',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create order status history table for detailed tracking
CREATE TABLE order_status_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL COMMENT 'User ID who changed the status',
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_order_status (order_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Record this migration
INSERT INTO migrations (version) VALUES ('3.0');