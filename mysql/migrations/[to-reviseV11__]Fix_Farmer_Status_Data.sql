ALTER TABLE farmer_profiles
ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN approved_by INT NULL,
ADD COLUMN suspended_at TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN suspended_by INT NULL,
ADD COLUMN suspension_reason TEXT NULL,
ADD COLUMN suspension_end_date TIMESTAMP NULL DEFAULT NULL,
ADD COLUMN rejection_reason TEXT NULL,
ADD COLUMN rejected_by INT NULL,
ADD COLUMN rejected_at TIMESTAMP NULL DEFAULT NULL,
ADD FOREIGN KEY (approved_by) REFERENCES users(id),
ADD FOREIGN KEY (suspended_by) REFERENCES users(id),
ADD FOREIGN KEY (rejected_by) REFERENCES users(id);

-- Update the status ENUM to include rejected status
ALTER TABLE farmer_profiles 
MODIFY COLUMN status ENUM('pending', 'active', 'suspended', 'rejected') NOT NULL DEFAULT 'pending';

-- Make sure all active farmers have an approved_at date
UPDATE farmer_profiles 
SET approved_at = created_at,
    approved_by = (SELECT id FROM users WHERE role = 'admin' LIMIT 1)
WHERE status = 'active' AND approved_at IS NULL;

-- Make sure all suspended farmers have suspension details
UPDATE farmer_profiles 
SET suspended_at = updated_at,
    suspended_by = (SELECT id FROM users WHERE role = 'admin' LIMIT 1)
WHERE status = 'suspended' AND suspended_at IS NULL;

-- Make sure all rejected farmers have rejection details
UPDATE farmer_profiles 
SET rejected_at = updated_at,
    rejected_by = (SELECT id FROM users WHERE role = 'admin' LIMIT 1)
WHERE status = 'rejected' AND rejected_at IS NULL;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('11.0', 'V11__Fix_Farmer_Status_Data.sql');

COMMIT;
