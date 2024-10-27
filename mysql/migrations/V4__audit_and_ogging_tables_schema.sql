-- Create system_logs table for application-level logging
CREATE TABLE system_logs (
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
CREATE TABLE audit_logs (
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
CREATE TABLE status_change_logs (
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

-- Create stored procedure for cleaning old logs
CREATE PROCEDURE cleanup_old_logs(IN retention_days INT)
BEGIN
    DECLARE cleanup_date TIMESTAMP;
    SET cleanup_date = DATE_SUB(NOW(), INTERVAL retention_days DAY);
    
    -- Delete old system logs
    DELETE FROM system_logs 
    WHERE created_at < cleanup_date 
    AND level NOT IN ('error', 'critical');
    
    -- Delete old audit logs
    DELETE FROM audit_logs 
    WHERE created_at < cleanup_date;
    
    -- Delete old status change logs
    DELETE FROM status_change_logs 
    WHERE created_at < cleanup_date;
END;

-- Create event to automatically clean logs older than 90 days
CREATE EVENT cleanup_logs_event
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
DO
    CALL cleanup_old_logs(90);

-- Record this migration
INSERT INTO migrations (version) VALUES ('4.0');