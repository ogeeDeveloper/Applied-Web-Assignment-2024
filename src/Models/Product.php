<?php

namespace App\Models;

use PDO;

class Product
{
    private PDO $db;
    private $logger;
    private MediaManager $mediaManager;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
        $this->mediaManager = new MediaManager($db, $logger);
    }

    /**
     * Creates a new product in the database.
     *
     * @param array $data An associative array containing the product data.
     * @param array|null $files An associative array containing file data (optional).
     *
     * @return array An associative array containing the result of the operation.
     *               The array will have the following keys:
     *               - 'success': A boolean indicating whether the operation was successful.
     *               - 'product_id': The ID of the newly created product (if successful).
     *               - 'message': A message describing the outcome of the operation.
     *
     * @throws \Exception If an error occurs during the file upload process.
     */

    
    

    public function create(array $data, array $files = null): array
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO products (
                farmer_id, name, category, description, 
                price_per_unit, unit_type, stock_quantity, availability,
                organic_certified, is_gmo, status, delivery_options
            ) VALUES (
                :farmer_id, :name, :category, :description, 
                :price_per_unit, :unit_type, :stock_quantity, :availability,
                :organic_certified, :is_gmo, :status, :delivery_options
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'farmer_id' => $data['farmer_id'],
                'name' => $data['name'],
                'category' => $data['category'],
                'description' => $data['description'] ?? null,
                'price_per_unit' => $data['price'],
                'unit_type' => $data['unit_type'] ?? 'kg',
                'stock_quantity' => $data['stock_quantity'] ?? 0,
                'availability' => $data['availability'] ?? true,
                'organic_certified' => $data['organic_certified'] ?? false,
                'is_gmo' => $data['is_gmo'] ?? false,
                'status' => $data['status'] ?? 'available',
                'delivery_options' => json_encode($data['delivery_options'] ?? ['pickup'])
            ]);

            $productId = (int) $this->db->lastInsertId();

            // Handle file uploads if any
            if ($files && !empty($files['images'])) {
                foreach ($files['images'] as $image) {
                    $uploadResult = $this->mediaManager->uploadFile($image, 'product', $productId);
                    if (!$uploadResult['success']) {
                        throw new \Exception("Failed to upload product image: " . $uploadResult['message']);
                    }
                }
            }

            $this->db->commit();

            return [
                'success' => true,
                'product_id' => $productId,
                'message' => 'Product created successfully'
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error creating product: " . $e->getMessage());
            return [
                'success' => false,
                'message' => "Failed to create product: " . $e->getMessage()
            ];
        }
    }

    public function getProductWithMedia(int $productId): ?array
    {
        try {
            // Get product details
            $sql = "SELECT p.*, f.farm_name, f.name as farmer_name 
                    FROM products p
                    JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                    WHERE p.product_id = :product_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                return null;
            }

            // Get product images
            $product['media'] = $this->mediaManager->getEntityFiles('product', $productId);

            return $product;
        } catch (\Exception $e) {
            $this->logger->error("Error getting product with media: " . $e->getMessage());
            return null;
        }
    }

    public function updateProductMedia(int $productId, array $files): array
    {
        try {
            $uploadedFiles = [];
            foreach ($files['images'] as $image) {
                $uploadResult = $this->mediaManager->uploadFile($image, 'product', $productId);
                if ($uploadResult['success']) {
                    $uploadedFiles[] = $uploadResult;
                }
            }

            return [
                'success' => true,
                'uploaded_files' => $uploadedFiles
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error updating product media: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function setPrimaryImage(int $productId, int $fileId): array
    {
        try {
            $result = $this->mediaManager->setPrimaryFile($fileId, 'product', $productId);
            return [
                'success' => $result,
                'message' => $result ? 'Primary image updated successfully' : 'Failed to update primary image'
            ];
        } catch (\Exception $e) {
            $this->logger->error("Error setting primary image: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getActiveFarmerProducts(int $farmerId): array
    {
        try {
            $sql = "SELECT * FROM products 
                    WHERE farmer_trn = :farmer_id 
                    AND availability = TRUE 
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting farmer products: " . $e->getMessage());
            return [];
        }
    }

    public function getLowStockProducts(int $farmerId, int $threshold = 10): array
    {
        try {
            $sql = "SELECT * FROM products 
                    WHERE farmer_trn = :farmer_id 
                    AND stock_quantity <= :threshold
                    AND availability = TRUE";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'farmer_id' => $farmerId,
                'threshold' => $threshold
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting low stock products: " . $e->getMessage());
            return [];
        }
    }

    public function getProducts(int $page = 1, int $limit = 10, ?string $category = null, string $status = 'available'): array
    {
        try {
            $offset = ($page - 1) * $limit;
            $params = ['status' => $status];

            $sql = "SELECT p.*, f.name as farmer_name, f.farm_name 
                    FROM products p 
                    JOIN farmers f ON p.farmer_trn = f.farmer_trn 
                    WHERE p.status = :status";

            if ($category) {
                $sql .= " AND p.category = :category";
                $params['category'] = $category;
            }

            $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue('offset', $offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting products: " . $e->getMessage());
            return [];
        }
    }

    public function getTotalProducts(?string $category = null, string $status = 'available'): int
    {
        try {
            $sql = "SELECT COUNT(*) FROM products WHERE status = :status";
            $params = ['status' => $status];

            if ($category) {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting total products: " . $e->getMessage());
            return 0;
        }
    }

    public function getSavedProducts(int $customerId): array
    {
        try {
            $sql = "SELECT p.*, f.farm_name, f.name as farmer_name, sp.saved_at
                    FROM saved_products sp
                    JOIN products p ON sp.product_id = p.product_id
                    JOIN farmers f ON p.farmer_trn = f.farmer_trn
                    WHERE sp.customer_trn = :customer_id
                    AND p.availability = TRUE
                    ORDER BY sp.saved_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['customer_id' => $customerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting saved products: " . $e->getMessage());
            return [];
        }
    }

    public function getRecommendedProducts(int $customerId): array
    {
        try {
            // Get products based on customer's previous orders and preferences
            $sql = "WITH CustomerCategories AS (
                        SELECT DISTINCT p.category
                        FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE o.customer_trn = :customer_id
                    ),
                    CustomerFarmers AS (
                        SELECT DISTINCT p.farmer_trn
                        FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        JOIN products p ON oi.product_id = p.product_id
                        WHERE o.customer_trn = :customer_id
                    )
                    SELECT DISTINCT p.*, f.farm_name, f.name as farmer_name,
                           CASE 
                               WHEN p.category IN (SELECT category FROM CustomerCategories) THEN 2
                               WHEN p.farmer_trn IN (SELECT farmer_trn FROM CustomerFarmers) THEN 1
                               ELSE 0
                           END as relevance_score
                    FROM products p
                    JOIN farmers f ON p.farmer_trn = f.farmer_trn
                    WHERE p.availability = TRUE
                    AND p.status = 'available'
                    AND p.product_id NOT IN (
                        SELECT oi.product_id 
                        FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        WHERE o.customer_trn = :customer_id
                    )
                    ORDER BY relevance_score DESC, p.created_at DESC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['customer_id' => $customerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recommended products: " . $e->getMessage());
            return [];
        }
    }

    public function saveProduct(int $customerId, int $productId): bool
    {
        try {
            $sql = "INSERT INTO saved_products (customer_trn, product_id, saved_at)
                    VALUES (:customer_id, :product_id, CURRENT_TIMESTAMP)
                    ON DUPLICATE KEY UPDATE saved_at = CURRENT_TIMESTAMP";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'customer_id' => $customerId,
                'product_id' => $productId
            ]);
        } catch (\PDOException $e) {
            $this->logger->error("Error saving product: " . $e->getMessage());
            return false;
        }
    }

    // Add this method to remove a saved product
    public function removeSavedProduct(int $customerId, int $productId): bool
    {
        try {
            $sql = "DELETE FROM saved_products 
                    WHERE customer_trn = :customer_id 
                    AND product_id = :product_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'customer_id' => $customerId,
                'product_id' => $productId
            ]);
        } catch (\PDOException $e) {
            $this->logger->error("Error removing saved product: " . $e->getMessage());
            return false;
        }
    }

    public function getMonthlyTopProducts(int $farmerId): array
    {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.name,
                        COUNT(oi.order_item_id) as order_count,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.total_price) as total_revenue
                    FROM products p
                    JOIN order_items oi ON p.product_id = oi.product_id
                    JOIN orders o ON oi.order_id = o.order_id
                    WHERE p.farmer_trn = :farmer_id
                    AND MONTH(o.ordered_date) = MONTH(CURRENT_DATE)
                    AND YEAR(o.ordered_date) = YEAR(CURRENT_DATE)
                    GROUP BY p.product_id, p.name
                    ORDER BY total_revenue DESC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting monthly top products: " . $e->getMessage());
            return [];
        }
    }

    public function getTopProducts(int $limit = 10): array
    {
        try {
            $sql = "SELECT 
                        p.product_id,
                        p.name,
                        p.category,
                        f.farm_name,
                        COUNT(DISTINCT o.order_id) as order_count,
                        SUM(oi.quantity) as total_quantity,
                        SUM(oi.total_price) as total_revenue
                    FROM products p
                    JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                    JOIN order_items oi ON p.product_id = oi.product_id
                    JOIN orders o ON oi.order_id = o.order_id
                    WHERE o.ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                    GROUP BY p.product_id, p.name, p.category, f.farm_name
                    ORDER BY total_revenue DESC
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting top products: " . $e->getMessage());
            return [];
        }
    }

    public function getSavedProductsCount(int $customerId): int
    {
        try {
            $sql = "SELECT COUNT(*) 
                    FROM saved_products 
                    WHERE customer_trn = :customer_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['customer_id' => $customerId]);

            return (int)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error getting saved products count: " . $e->getMessage());
            return 0;
        }
    }

    // Additional helper method to check if a product is saved
    public function isProductSaved(int $customerId, int $productId): bool
    {
        try {
            $sql = "SELECT COUNT(*) 
                    FROM saved_products 
                    WHERE customer_trn = :customer_id 
                    AND product_id = :product_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'customer_id' => $customerId,
                'product_id' => $productId
            ]);

            return (bool)$stmt->fetchColumn();
        } catch (\PDOException $e) {
            $this->logger->error("Error checking saved product: " . $e->getMessage());
            return false;
        }
    }

    public function getAllProducts(array $filters = [], int $limit = null, int $offset = null): array
    {
        try {
            $conditions = [];
            $params = [];

            $sql = "SELECT p.*, 
                    f.farm_name,
                    f.name as farmer_name,
                    COUNT(DISTINCT oi.order_id) as total_orders,
                    COALESCE(SUM(oi.quantity), 0) as total_units_sold
                    FROM products p
                    JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
                    LEFT JOIN order_items oi ON p.product_id = oi.product_id";

            // Apply filters
            if (!empty($filters['category'])) {
                $conditions[] = "p.category = :category";
                $params['category'] = $filters['category'];
            }

            if (!empty($filters['status'])) {
                $conditions[] = "p.status = :status";
                $params['status'] = $filters['status'];
            }

            if (!empty($filters['farmer_id'])) {
                $conditions[] = "p.farmer_id = :farmer_id";
                $params['farmer_id'] = $filters['farmer_id'];
            }

            if (isset($filters['organic_certified'])) {
                $conditions[] = "p.organic_certified = :organic_certified";
                $params['organic_certified'] = $filters['organic_certified'];
            }

            if (isset($filters['is_gmo'])) {
                $conditions[] = "p.is_gmo = :is_gmo";
                $params['is_gmo'] = $filters['is_gmo'];
            }

            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sql .= " GROUP BY p.product_id ORDER BY p.created_at DESC";

            if ($limit !== null) {
                $sql .= " LIMIT :limit";
                if ($offset !== null) {
                    $sql .= " OFFSET :offset";
                }
            }

            $stmt = $this->db->prepare($sql);

            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }

            if ($limit !== null) {
                $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
                if ($offset !== null) {
                    $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
                }
            }

            $stmt->execute();
            $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Get media for each product
            foreach ($products as &$product) {
                $product['media'] = $this->mediaManager->getEntityFiles('product', $product['product_id']);
            }

            return $products;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting all products: " . $e->getMessage());
            return [];
        }
    }
    public function getPopularProducts(int $limit = 10, ?string $category = null): array
{
    try {
        $this->logger->info("Fetching popular products", ['limit' => $limit, 'category' => $category]);

        $sql = "
            SELECT 
                p.*,
                fp.farm_name,
                fp.location,
                COALESCE(oi.total_orders, 0) as order_count,
                COALESCE(oi.total_quantity, 0) as total_quantity_sold
            FROM products p
            JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
            LEFT JOIN (
                SELECT 
                    product_id,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(quantity) as total_quantity
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.order_status != 'cancelled'
                GROUP BY product_id
            ) oi ON p.product_id = oi.product_id
            WHERE p.status = 'available'
            AND p.stock_quantity > 0
            " . ($category ? "AND p.category = :category" : "") . "
            ORDER BY order_count DESC, total_quantity_sold DESC
            LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        
        if ($category) {
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add additional product information
        foreach ($products as &$product) {
            // Check if product is organic
            $product['is_organic'] = (bool)$product['organic_certified'];
            
            // Add stock status
            $product['stock_status'] = $this->getStockStatus($product['stock_quantity'], $product['low_stock_alert_threshold']);
            
            // Format price
            $product['formatted_price'] = number_format($product['price_per_unit'], 2);
        }

        $this->logger->info("Successfully fetched popular products", ['count' => count($products)]);
        return $products;

    } catch (PDOException $e) {
        $this->logger->error("Error fetching popular products: " . $e->getMessage());
        throw new Exception("Failed to fetch popular products");
    }
}

/**
 * Helper method to determine stock status
 */
private function getStockStatus(int $currentStock, int $threshold): string
{
    if ($currentStock <= 0) {
        return 'out_of_stock';
    } elseif ($currentStock <= $threshold) {
        return 'low_stock';
    }
    return 'in_stock';
}

/**
 * Get popular products by category
 */
public function getPopularProductsByCategory(string $category, int $limit = 5): array
{
    return $this->getPopularProducts($limit, $category);
}

/**
 * Get popular products across all categories with category grouping
 */
public function getPopularProductsByCategories(int $limit_per_category = 5): array
{
    try {
        $sql = "
            SELECT DISTINCT category 
            FROM products 
            WHERE status = 'available'
            ORDER BY category";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $result = [];
        foreach ($categories as $category) {
            $result[$category] = $this->getPopularProductsByCategory($category, $limit_per_category);
        }

        return $result;

    } catch (PDOException $e) {
        $this->logger->error("Error fetching products by categories: " . $e->getMessage());
        throw new Exception("Failed to fetch products by categories");
    }
}
}
