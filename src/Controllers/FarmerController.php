<?php

namespace App\Controllers;

use App\Models\Farmer;
use App\Models\Product;
use App\Models\Order;
use App\Models\Planting;
use App\Models\ChemicalUsage;
use App\Models\Crop;
use PDO;
use Exception;
use App\Utils\SessionManager;
use App\Models\User;

class FarmerController extends BaseController
{
    private $farmerModel;
    private $productModel;
    private $orderModel;
    private $plantingModel;
    private $chemicalUsageModel;
    private $cropModel;
    private $userModel;

    public function __construct(PDO $db, $logger)
    {
        parent::__construct($db, $logger);
        $this->farmerModel = new Farmer($db, $logger);
        $this->productModel = new Product($db, $logger);
        $this->orderModel = new Order($db, $logger);
        $this->plantingModel = new Planting($db, $logger);
        $this->chemicalUsageModel = new ChemicalUsage($db, $logger);
        $this->cropModel = new Crop($db, $logger);
        $this->userModel = new User($db, $logger);
    }

    public function index(): void
    {
        try {
            SessionManager::initialize(); // Ensure session is active
            $this->validateAuthenticatedRequest();
            $this->validateRole('farmer');

            $dashboardController = new DashboardController($this->db, $this->logger);
            $dashboardData = $dashboardController->farmerDashboard();

            if ($dashboardData['success']) {
                $this->render('farmer/dashboard', $dashboardData['data']);
            } else {
                throw new Exception($dashboardData['message']);
            }
        } catch (Exception $e) {
            $this->logger->error("Error loading farmer dashboard: " . $e->getMessage(), [
                'session' => $_SESSION,
                'uri' => $_SERVER['REQUEST_URI']
            ]);
            $this->render('error', ['message' => 'Failed to load dashboard.']);
        }
    }

    public function updateProfile(): void
    {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'farm_name' => 'string',
                'location' => 'string',
                'farm_type' => 'string',
                'farm_size' => 'string',
                'farming_experience' => 'int',
                'primary_products' => 'string',
                'organic_certified' => 'boolean',
                'phone_number' => 'string'
            ]);

            $result = $this->farmerModel->updateProfile(
                $_SESSION['user_id'],
                $input
            );

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Profile update error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update profile'
            ], 500);
        }
    }

    public function addPlanting(): void
    {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'crop_type_id' => 'int',
                'field_location' => 'string',
                'area_size' => 'float',
                'area_unit' => 'string',
                'planting_date' => 'date',
                'expected_harvest_date' => 'date',
                'growing_method' => 'string'
            ]);

            $result = $this->plantingModel->create(
                array_merge($input, ['farmer_id' => $_SESSION['user_id']])
            );

            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error adding planting: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to add planting'
            ], 500);
        }
    }

    public function recordChemicalUsage(): void
    {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'planting_id' => 'int',
                'chemical_name' => 'string',
                'chemical_type' => 'string',
                'date_applied' => 'date',
                'amount_used' => 'float',
                'unit_of_measurement' => 'string',
                'safety_period_days' => 'int'
            ]);

            $result = $this->chemicalUsageModel->create($input);
            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error recording chemical usage: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to record chemical usage'
            ], 500);
        }
    }

    public function recordHarvest(): void
    {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'planting_id' => 'int',
                'harvest_date' => 'date',
                'quantity' => 'float',
                'unit' => 'string',
                'quality_grade' => 'string',
                'storage_location' => 'string'
            ]);

            $result = $this->plantingModel->recordHarvest($input);
            $this->jsonResponse($result);
        } catch (Exception $e) {
            $this->logger->error("Error recording harvest: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to record harvest'
            ], 500);
        }
    }

    private function getMonthlyStats($farmerId): array
    {
        return [
            'sales' => $this->orderModel->getMonthlyTotal($farmerId),
            'orders' => $this->orderModel->getMonthlyOrderCount($farmerId),
            'products' => $this->productModel->getMonthlyTopProducts($farmerId),
            'harvests' => $this->plantingModel->getMonthlyHarvestStats($farmerId)
        ];
    }

    public function getCrops(): array
    {
        try {
            $this->validateAuthenticatedRequest();
            return $this->cropModel->getCropsByFarmer($_SESSION['user_id']);
        } catch (\Exception $e) {
            $this->logger->error("Error fetching crops: " . $e->getMessage());
            return [];
        }
    }

    public function logout(): void
    {
        SessionManager::destroy();
        $this->redirect('/login', 'You have been logged out successfully');
    }

    public function accountSettings(): void
    {
        try {
            $farmerId = $_SESSION['user_id'];
            $farmer = $this->userModel->findById($farmerId);

            $this->render('farmer/account-settings', [
                'farmer' => $farmer
            ], 'Account Settings', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading account settings: " . $e->getMessage());
            $this->redirect('/farmer/dashboard');
        }
    }

    public function recordActivity(): void
    {
        $this->render('farmer/record-activity', [], 'Record Activity', 'farmer/layouts/farmer');
    }

    public function chemicalUsage(): void
    {
        try {
            $farmerId = $_SESSION['user_id'];

            // Get current plantings for dropdown
            $plantings = $this->plantingModel->getCurrentPlantings($farmerId);

            // Get chemical records
            $chemicalRecords = $this->chemicalUsageModel->getRecentUsage($farmerId);

            $this->render('farmer/chemical-usage', [
                'plantings' => $plantings,
                'records' => $chemicalRecords,
                'currentPage' => 'chemical-usage'
            ], 'Chemical Usage Records', 'farmer/layouts/farmer');
        } catch (\Exception $e) {
            $this->logger->error("Error loading chemical usage: " . $e->getMessage());
            $this->setFlashMessage('Error loading chemical records', 'error');
            $this->redirect('/farmer/dashboard');
        }
    }

    public function manageCrops(): void
    {
        try {
            $farmerId = $_SESSION['user_id'];
            $crops = $this->cropModel->getCurrentCrops($farmerId);

            $this->render('farmer/manage-crops', [
                'crops' => $crops
            ], 'Manage Crops', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading crop management: " . $e->getMessage());
            $this->redirect('/farmer/dashboard');
        }
    }
}
