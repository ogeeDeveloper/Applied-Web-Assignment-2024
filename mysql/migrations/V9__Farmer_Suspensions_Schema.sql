SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

CREATE TABLE IF NOT EXISTS farmer_suspensions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    duration VARCHAR(50) NOT NULL,
    reason TEXT NOT NULL,
    suspended_by INT NOT NULL,
    suspended_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(farmer_id) ON DELETE CASCADE,
    FOREIGN KEY (suspended_by) REFERENCES users(id),
    INDEX idx_farmer_suspensions (farmer_id, suspended_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('9.0', 'V9__Farmer_Suspensions_Schema.sql');

COMMIT;