START TRANSACTION;

-- Check and add last_login column
SELECT COUNT(*) INTO @exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'last_login' AND TABLE_SCHEMA = DATABASE();

SET @sql = IF(@exists = 0, 
    'ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL DEFAULT NULL AFTER updated_at',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add password_reset_token column
SELECT COUNT(*) INTO @exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'password_reset_token' AND TABLE_SCHEMA = DATABASE();

SET @sql = IF(@exists = 0,
    'ALTER TABLE users ADD COLUMN password_reset_token VARCHAR(64) NULL DEFAULT NULL AFTER last_login',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add token_expiry column
SELECT COUNT(*) INTO @exists 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'users' AND COLUMN_NAME = 'token_expiry' AND TABLE_SCHEMA = DATABASE();

SET @sql = IF(@exists = 0,
    'ALTER TABLE users ADD COLUMN token_expiry TIMESTAMP NULL DEFAULT NULL AFTER password_reset_token',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Check and add indexes (only if they don't exist)
SELECT COUNT(*) INTO @exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'idx_password_reset_token' AND TABLE_SCHEMA = DATABASE();

SET @sql = IF(@exists = 0,
    'ALTER TABLE users ADD INDEX idx_password_reset_token (password_reset_token)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT COUNT(*) INTO @exists 
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_NAME = 'users' AND INDEX_NAME = 'idx_last_login' AND TABLE_SCHEMA = DATABASE();

SET @sql = IF(@exists = 0,
    'ALTER TABLE users ADD INDEX idx_last_login (last_login)',
    'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update existing records to have NULL last_login
UPDATE users SET last_login = NULL WHERE last_login IS NOT NULL;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('5.0', 'add_last_login_and_reset_token');

COMMIT;