SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

START TRANSACTION;

ALTER TABLE order_items
ADD FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE RESTRICT,
ADD FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE;

-- Add index for performance
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);

-- Record this migration
INSERT INTO migrations (version, name) VALUES ('8.0', 'V8__Fix_Order_Items_Relations.sql');

COMMIT;