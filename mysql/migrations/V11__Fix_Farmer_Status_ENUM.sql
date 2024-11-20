SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

ALTER TABLE farmer_profiles 
MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'rejected', 'inactive') NOT NULL DEFAULT 'pending';

-- Create a table to track farmer rejections
CREATE TABLE IF NOT EXISTS farmer_rejections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    reason TEXT NOT NULL,
    rejected_by INT NOT NULL,
    rejected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES farmer_profiles(farmer_id) ON DELETE CASCADE,
    FOREIGN KEY (rejected_by) REFERENCES users(id),
    INDEX idx_farmer_rejections (farmer_id, rejected_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('11.0', 'V11__Fix_Farmer_Status_ENUM.sql');

COMMIT;