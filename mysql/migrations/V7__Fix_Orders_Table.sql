SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

ALTER TABLE orders 
ADD COLUMN total_price DECIMAL(10,2) GENERATED ALWAYS AS (total_amount) STORED;

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('7.0', 'V7__Fix_Orders_Table.sql');

COMMIT;