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

    /**
     * Get product images
     */
    private function getProductImages(int $productId): array
    {
        try {
            $sql = "
            SELECT 
                file_id,
                file_path,
                file_name,
                file_type,
                mime_type,
                is_primary,
                status,
                upload_date,
                metadata
            FROM media_files
            WHERE entity_type = 'product'
            AND entity_id = :product_id
            AND status = 'active'
            ORDER BY is_primary DESC, created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);

            $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Format image data
            return array_map(function ($image) {
                return [
                    'id' => $image['file_id'],
                    'path' => $image['file_path'],
                    'name' => $image['file_name'],
                    'type' => $image['file_type'],
                    'mime_type' => $image['mime_type'],
                    'is_primary' => (bool)$image['is_primary'],
                    'status' => $image['status'],
                    'upload_date' => $image['upload_date'],
                    'metadata' => json_decode($image['metadata'], true)
                ];
            }, $images);
        } catch (PDOException $e) {
            $this->logger->error("Error fetching product images: " . $e->getMessage(), [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    public function getProductDetails(int $productId)
    {
        try {
            // Validate input
            if (!$productId || !is_numeric($productId)) {
                throw new InvalidArgumentException('Invalid product ID');
            }

            // Build main product query
            $sql = "
            SELECT 
                -- Product basic info
                p.product_id,
                p.name,
                p.category,
                p.description,
                p.price_per_unit,
                p.unit_type,
                p.stock_quantity,
                p.status,
                p.availability,
                p.low_stock_alert_threshold,
                p.organic_certified,
                p.is_gmo,
                p.created_at,
                p.updated_at,
                
                -- Farmer info
                f.farmer_id,
                f.farm_name,
                f.location as farm_location,
                f.farm_type,
                f.organic_certified as farm_organic_certified,
                u.name as farmer_name,
                
                -- Harvest info
                h.harvest_id,
                h.harvest_date,
                h.quantity as harvest_quantity,
                h.quality_grade,
                h.storage_conditions,
                h.storage_location,
                h.loss_quantity,
                h.loss_reason,
                
                -- Planting info
                pl.planting_id,
                pl.planting_date,
                pl.growing_method,
                pl.soil_preparation,
                pl.irrigation_method,
                pl.field_location,
                pl.weather_conditions as planting_weather,
                
                -- Crop type info
                ct.name as crop_name,
                ct.category as crop_category,
                ct.growing_season,
                ct.typical_growth_duration
            FROM products p
            LEFT JOIN farmer_profiles f ON p.farmer_id = f.farmer_id
            LEFT JOIN users u ON f.user_id = u.id
            LEFT JOIN harvests h ON p.harvest_id = h.harvest_id
            LEFT JOIN plantings pl ON h.planting_id = pl.planting_id
            LEFT JOIN crop_types ct ON pl.crop_type_id = ct.crop_id
            WHERE p.product_id = :product_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);

            $product = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$product) {
                $this->logger->info("Product not found: " . $productId);
                return null;
            }

            // Get product images with complete information
            $images = $this->getProductImages($productId);

            // Get chemical usage if planting exists
            $chemicalUsage = [];
            if ($product['planting_id']) {
                $chemicalUsage = $this->getChemicalUsage($product['planting_id']);
            }

            // Calculate status information
            $statusInfo = $this->calculateProductStatus($product);

            // Format and return the response
            return [
                'basic_info' => [
                    'product_id' => $product['product_id'],
                    'name' => $product['name'],
                    'category' => $product['category'],
                    'description' => $product['description'],
                    'price_per_unit' => floatval($product['price_per_unit']),
                    'unit_type' => $product['unit_type'],
                    'organic_certified' => (bool)$product['organic_certified'],
                    'is_gmo' => (bool)$product['is_gmo'],
                    'created_at' => $product['created_at'],
                    'updated_at' => $product['updated_at'],
                    'images' => $images
                ],
                'media' => [
                    'images' => $images,
                    'primary_image' => array_filter($images, fn($img) => $img['is_primary'])[0] ?? null
                ],
                'status' => $statusInfo,
                'farm_info' => [
                    'farmer_id' => $product['farmer_id'],
                    'farmer_name' => $product['farmer_name'],
                    'farm_name' => $product['farm_name'],
                    'location' => $product['farm_location'],
                    'farm_type' => $product['farm_type'],
                    'organic_certified' => (bool)$product['farm_organic_certified']
                ],
                'production_info' => [
                    'crop_name' => $product['crop_name'],
                    'crop_category' => $product['crop_category'],
                    'growing_season' => $product['growing_season'],
                    'typical_growth_duration' => $product['typical_growth_duration'],
                    'planting' => [
                        'planting_id' => $product['planting_id'],
                        'planting_date' => $product['planting_date'],
                        'growing_method' => $product['growing_method'],
                        'soil_preparation' => $product['soil_preparation'],
                        'irrigation_method' => $product['irrigation_method'],
                        'field_location' => $product['field_location'],
                        'weather_conditions' => $product['planting_weather'],
                        'chemical_usage' => $chemicalUsage
                    ],
                    'harvest' => [
                        'harvest_id' => $product['harvest_id'],
                        'harvest_date' => $product['harvest_date'],
                        'quantity' => floatval($product['harvest_quantity']),
                        'quality_grade' => $product['quality_grade'],
                        'storage_conditions' => $product['storage_conditions'],
                        'storage_location' => $product['storage_location'],
                        'loss_quantity' => floatval($product['loss_quantity']),
                        'loss_reason' => $product['loss_reason']
                    ]
                ]
            ];
        } catch (Exception $e) {
            $this->logger->error("Error fetching product details: " . $e->getMessage(), [
                'product_id' => $productId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get chemical usage records for a planting
     */
    private function getChemicalUsage(int $plantingId): array
    {
        try {
            $sql = "
            SELECT 
                chemical_name,
                chemical_type,
                date_applied,
                purpose,
                amount_used,
                unit_of_measurement,
                safety_period_days,
                application_method,
                weather_conditions,
                notes
            FROM chemical_usage
            WHERE planting_id = :planting_id
            ORDER BY date_applied DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['planting_id' => $plantingId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->logger->error("Error fetching chemical usage: " . $e->getMessage());
            return [];
        }
    }

    public function getFilteredProducts(array $filters): array
    {
        try {
            $this->logger->info("Fetching filtered products", $filters);

            $params = [];

            // Base SQL query
            $sql = "
            SELECT 
                p.*,
                fp.farm_name,
                fp.location as farm_location,
                COALESCE(order_stats.total_orders, 0) as order_count,
                COALESCE(order_stats.total_quantity, 0) as total_quantity_sold,
                COALESCE(order_stats.total_revenue, 0) as total_revenue,
                (
                    SELECT mf.file_path 
                    FROM media_files mf 
                    WHERE mf.entity_type = 'product' 
                    AND mf.entity_id = p.product_id 
                    AND mf.is_primary = 1 
                    AND mf.status = 'active'
                    LIMIT 1
                ) as primary_image_path
            FROM products p
            JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
            LEFT JOIN (
                -- Order statistics subquery
                SELECT 
                    oi.product_id,
                    COUNT(DISTINCT o.order_id) as total_orders,
                    SUM(oi.quantity) as total_quantity,
                    SUM(oi.total_price) as total_revenue
                FROM order_items oi
                JOIN orders o ON oi.order_id = o.order_id
                WHERE o.ordered_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
                AND o.order_status != 'cancelled'
                GROUP BY oi.product_id
            ) order_stats ON p.product_id = order_stats.product_id
            WHERE p.status = 'available'
            AND p.stock_quantity > 0
        ";

            // Add category filter
            if (!empty($filters['category'])) {
                $sql .= " AND p.category = :category";
                $params[':category'] = $filters['category'];
            }

            // Add price range filters
            if (isset($filters['min_price']) && $filters['min_price'] !== null) {
                $sql .= " AND p.price_per_unit >= :min_price";
                $params[':min_price'] = $filters['min_price'];
            }
            if (isset($filters['max_price']) && $filters['max_price'] !== null) {
                $sql .= " AND p.price_per_unit <= :max_price";
                $params[':max_price'] = $filters['max_price'];
            }

            // Add sorting
            $sql .= match ($filters['sort_by'] ?? 'latest') {
                'low_high' => " ORDER BY p.price_per_unit ASC",
                'high_low' => " ORDER BY p.price_per_unit DESC",
                'popular' => " ORDER BY order_stats.total_orders DESC, order_stats.total_revenue DESC",
                'oldest' => " ORDER BY p.created_at ASC",
                default => " ORDER BY p.created_at DESC" // 'latest'
            };

            // Add limit
            if (isset($filters['limit'])) {
                $sql .= " LIMIT :limit";
                $params[':limit'] = (int)$filters['limit'];
            }

            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(
                    $key,
                    $value,
                    is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
                );
            }

            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Enhance products with additional data
            foreach ($products as &$product) {
                // Format numbers
                $product['price_per_unit'] = floatval($product['price_per_unit']);
                $product['formatted_price'] = number_format($product['price_per_unit'], 2);
                $product['stock_quantity'] = intval($product['stock_quantity']);

                // Get stock status
                $product['stock_status'] = $this->getStockStatus(
                    $product['stock_quantity'],
                    $product['low_stock_alert_threshold']
                );

                // Get product images
                $product['media'] = $this->mediaManager->getEntityFiles('product', $product['product_id']);

                // Set primary image
                if (!$product['primary_image_path'] && !empty($product['media'])) {
                    $primary = array_filter($product['media'], fn($img) => $img['is_primary']);
                    $product['primary_image_path'] = !empty($primary) ?
                        reset($primary)['file_path'] :
                        $product['media'][0]['file_path'];
                }

                // Ensure there's always an image path
                if (empty($product['primary_image_path'])) {
                    $product['primary_image_path'] = '/images/default-product.jpg';
                }
            }

            $this->logger->info("Filtered products retrieved", [
                'count' => count($products)
            ]);

            return $products;
        } catch (\PDOException $e) {
            $this->logger->error("Database error in getFilteredProducts: " . $e->getMessage());
            return [];
        } catch (\Exception $e) {
            $this->logger->error("Error in getFilteredProducts: " . $e->getMessage());
            return [];
        }
    }

    private function getStockStatus(int $currentStock, int $threshold): array
    {
        if ($currentStock <= 0) {
            return [
                'status' => 'out_of_stock',
                'label' => 'Out of Stock',
                'class' => 'badge badge-danger'
            ];
        }
        if ($currentStock <= $threshold) {
            return [
                'status' => 'low_stock',
                'label' => 'Low Stock',
                'class' => 'badge badge-warning'
            ];
        }
        return [
            'status' => 'in_stock',
            'label' => 'In Stock',
            'class' => 'badge badge-success'
        ];
    }
}
