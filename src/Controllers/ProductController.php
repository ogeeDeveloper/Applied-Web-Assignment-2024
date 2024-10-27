<?php
namespace App\Controllers;

use App\Models\Product;
use Exception;
use PDO;

class ProductController extends BaseController {
    private Product $productModel;

    public function __construct(PDO $db, $logger) {
        parent::__construct($db, $logger);
        $this->productModel = new Product($db, $logger);
    }

    public function create(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $input = $this->validateInput([
                'name' => 'string',
                'category' => 'string',
                'description' => 'string',
                'price' => 'float',
                'unit_type' => 'string',
                'stock_quantity' => 'int',
                'organic_certified' => 'boolean',
                'is_gmo' => 'boolean'
            ]);

            $result = $this->productModel->create(
                array_merge($input, ['farmer_id' => $_SESSION['user_id']]),
                $_FILES ?? null
            );

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error creating product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to create product'
            ], 500);
        }
    }

    public function uploadProductImages(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $input = $this->validateInput([
                'product_id' => 'int'
            ]);

            if (empty($_FILES['images'])) {
                throw new Exception('No images provided');
            }

            $result = $this->productModel->updateProductMedia($input['product_id'], $_FILES);
            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error uploading product images: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to upload images'
            ], 500);
        }
    }

    public function setPrimaryImage(): void {
        try {
            $this->validateAuthenticatedRequest();
            
            $input = $this->validateInput([
                'product_id' => 'int',
                'file_id' => 'int'
            ]);

            $result = $this->productModel->setPrimaryImage(
                $input['product_id'],
                $input['file_id']
            );
            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error setting primary image: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to set primary image'
            ], 500);
        }
    }

    public function getFarmerProducts(): void {
        try {
            $this->validateAuthenticatedRequest();
            $farmerId = $_SESSION['user_id'];
            
            $result = $this->productModel->getActiveFarmerProducts($farmerId);
            $this->jsonResponse([
                'success' => true,
                'products' => $result
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting farmer products: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get products'
            ], 500);
        }
    }

    public function getLowStockProducts(): void {
        try {
            $this->validateAuthenticatedRequest();
            $farmerId = $_SESSION['user_id'];
            
            $result = $this->productModel->getLowStockProducts($farmerId);
            $this->jsonResponse([
                'success' => true,
                'products' => $result
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error getting low stock products: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get low stock products'
            ], 500);
        }
    }

    /**
     * Saves a product for the authenticated user.
     *
     * @throws Exception If the user is not authenticated or if the product ID is not provided.
     *
     * @return void
     */
    public function saveProduct(): void {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'product_id' => 'int'
            ]);

            $result = $this->productModel->saveProduct(
                $_SESSION['user_id'],
                $input['product_id']
            );

            $this->jsonResponse([
                'success' => $result,
                'message' => $result ? 'Product saved successfully' : 'Failed to save product'
            ]);
        } catch (Exception $e) {
            $this->logger->error("Error saving product: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to save product'
            ], 500);
        }
    }


    /**
     * Retrieves a list of recommended products for the authenticated user.
     *
     * @throws Exception If the user is not authenticated.
     *
     * @return void
     */
    public function getRecommendedProducts(): void {
        try {
            $this->validateAuthenticatedRequest();

            // Retrieve recommended products from the product model
            $result = $this->productModel->getRecommendedProducts($_SESSION['user_id']);

            // Return the recommended products in a JSON response
            $this->jsonResponse([
                'success' => true,
                'products' => $result
            ]);
        } catch (Exception $e) {
            // Log the error and return a JSON error response
            $this->logger->error("Error getting recommended products: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get recommended products'
            ], 500);
        }
    }


    /**
     * Retrieves a list of saved products for the authenticated user.
     *
     * @throws Exception If the user is not authenticated.
     *
     * @return void
     */
    public function getSavedProducts(): void {
        try {
            // Validate the authenticated request
            $this->validateAuthenticatedRequest();

            // Retrieve saved products from the product model
            $result = $this->productModel->getSavedProducts($_SESSION['user_id']);

            // Return the saved products in a JSON response
            $this->jsonResponse([
                'success' => true,
                'products' => $result
            ]);
        } catch (Exception $e) {
            // Log the error and return a JSON error response
            $this->logger->error("Error getting saved products: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to get saved products'
            ], 500);
        }
    }

}