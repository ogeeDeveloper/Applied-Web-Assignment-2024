SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

CREATE TABLE farmer_status_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    status VARCHAR(50) NOT NULL,
    changed_by INT NOT NULL,
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(farmer_id),
    FOREIGN KEY (changed_by) REFERENCES users(id),
    INDEX idx_farmer_status_logs (farmer_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('10.0', 'V10__Create_Farmer_Status_Logs_Table.sql');

COMMIT;