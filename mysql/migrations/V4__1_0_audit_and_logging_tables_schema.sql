START TRANSACTION;
-- Create system_logs table for application-level logging
CREATE TABLE IF NOT EXISTS system_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    log_type ENUM('error', 'warning', 'info', 'debug') NOT NULL,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context JSON DEFAULT NULL COMMENT 'Additional contextual data',
    component VARCHAR(100) COMMENT 'System component or module name',
    trace TEXT COMMENT 'Stack trace for errors',
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_log_type (log_type),
    INDEX idx_level (level),
    INDEX idx_component (component),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create audit_logs table for user action tracking
CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL COMMENT 'User who performed the action',
    action_type VARCHAR(50) NOT NULL COMMENT 'Type of action performed',
    entity_type VARCHAR(50) NOT NULL COMMENT 'Type of entity affected (user, product, order, etc.)',
    entity_id INT NOT NULL COMMENT 'ID of the affected entity',
    old_values JSON DEFAULT NULL COMMENT 'Previous state of the entity',
    new_values JSON DEFAULT NULL COMMENT 'New state of the entity',
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    additional_info JSON DEFAULT NULL COMMENT 'Any additional contextual information',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_action_type (action_type),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create status_change_logs table for tracking status changes
CREATE TABLE IF NOT EXISTS status_change_logs (
    change_id INT AUTO_INCREMENT PRIMARY KEY,
    entity_type ENUM('user', 'farmer', 'order', 'product') NOT NULL,
    entity_id INT NOT NULL,
    old_status VARCHAR(50) DEFAULT NULL,
    new_status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL COMMENT 'User ID who made the change',
    reason TEXT DEFAULT NULL COMMENT 'Reason for status change',
    additional_notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (changed_by) REFERENCES users(id) ON DELETE RESTRICT,
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_changed_by (changed_by),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('4.0', 'V4__audit_and_logging_tables_schema.sql');
COMMIT;