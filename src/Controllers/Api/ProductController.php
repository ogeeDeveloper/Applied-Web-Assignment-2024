<?php
namespace App\Controllers\Api;

use PDO;
use App\Models\Product;
use App\Models\Crop;

class ProductController {
    private $db;
    private $logger;
    private $productModel;
    private $cropModel;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
        $this->productModel = new Product($db, $logger);
        $this->cropModel = new Crop($db, $logger);
    }

    public function create(): void {
        try {
            if ($_SESSION['user_role'] !== 'farmer') {
                throw new \Exception("Unauthorized access");
            }

            $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
            $data['farmer_id'] = $_SESSION['user_id'];

            // Validate product data
            $this->validateProductData($data);

            // Handle image upload if present
            if (isset($_FILES['image'])) {
                $data['image'] = $this->handleImageUpload($_FILES['image']);
            }

            $productId = $this->productModel->create($data);

            // If this is a crop product, create crop record
            if (isset($data['is_crop']) && $data['is_crop']) {
                $this->cropModel->create([
                    'product_id' => $productId,
                    'planting_date' => $data['planting_date'],
                    'expected_harvest' => $data['expected_harvest']
                ]);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Product created successfully',
                'product_id' => $productId
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Product creation error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function list(): void {
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = (int)($_GET['limit'] ?? 10);
            $category = $_GET['category'] ?? null;
            $status = $_GET['status'] ?? 'active';

            $products = $this->productModel->getProducts($page, $limit, $category, $status);
            $total = $this->productModel->getTotalProducts($category, $status);

            echo json_encode([
                'success' => true,
                'data' => $products,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($total / $limit),
                    'total_items' => $total
                ]
            ]);

        } catch (\Exception $e) {
            $this->logger->error("Product list error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error retrieving products'
            ]);
        }
    }

    private function validateProductData(array $data): void {
        $required = ['name', 'category', 'price', 'unit_type'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new \Exception("Missing required field: {$field}");
            }
        }

        if ($data['price'] <= 0) {
            throw new \Exception("Price must be greater than zero");
        }
    }

    private function handleImageUpload(array $file): string {
        // Image upload implementation
        // Return the path to the uploaded image
        return 'path/to/image.jpg';
    }
}