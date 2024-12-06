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

            // Get farmer profile info
            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT u.*, fp.*
            FROM users u
            JOIN farmer_profiles fp ON u.id = fp.user_id
            WHERE u.id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get dashboard data from DashboardController
            $dashboardController = new DashboardController($this->db, $this->logger);
            $dashboardData = $dashboardController->farmerDashboard();

            if ($dashboardData['success']) {
                // Combine farmer profile with dashboard data
                $data = array_merge(
                    $dashboardData['data'],
                    [
                        'farmer' => $farmer,
                        'stats' => [
                            'activeCrops' => count($dashboardData['data']['currentCrops']),
                            'recentHarvest' => $this->getRecentHarvestSummary($dashboardData['data']['upcomingHarvests']),
                            'chemicalsUsed' => count($dashboardData['data']['activeProducts'])
                        ],
                        'activities' => $this->getFormattedActivities($dashboardData['data']),
                        'currentPage' => 'dashboard'
                    ]
                );

                $this->render('farmer/dashboard', $data, 'Farmer Dashboard', 'farmer/layouts/farmer');
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

    private function getRecentHarvestSummary(array $harvests): string
    {
        try {
            $stmt = $this->db->prepare("
            SELECT SUM(h.quantity) as total, h.unit 
            FROM harvests h
            JOIN plantings p ON h.planting_id = p.planting_id
            WHERE p.farmer_id = (
                SELECT farmer_id FROM farmer_profiles 
                WHERE user_id = :user_id
            )
            AND h.harvest_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
            GROUP BY h.unit
            ORDER BY total DESC
            LIMIT 1
        ");

            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result && $result['total']) {
                return $result['total'] . ' ' . $result['unit'];
            }

            return '0 Tons';
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recent harvest summary: " . $e->getMessage());
            return '0 Tons';
        }
    }


    private function getFormattedActivities(array $dashboardData): array
    {
        $activities = [];

        // Add crop activities
        foreach ($dashboardData['currentCrops'] as $crop) {
            $activities[] = [
                'date' => $crop['planting_date'],
                'activity' => 'New Crop Planted',
                'details' => $crop['product_name'] . ' in ' . $crop['field_location']
            ];
        }

        // Add order activities
        foreach ($dashboardData['recentOrders'] as $order) {
            $activities[] = [
                'date' => $order['ordered_date'],
                'activity' => 'Order Received',
                'details' => 'Order #' . $order['order_id']
            ];
        }

        // Sort activities by date, most recent first
        usort($activities, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });

        // Return only the 5 most recent activities
        return array_slice($activities, 0, 5);
    }

    private function getActiveCropsCount(int $farmerId): int
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM plantings 
        WHERE farmer_id = :farmer_id 
        AND status IN ('planted', 'growing')
    ");
        $stmt->execute(['farmer_id' => $farmerId]);
        return (int) $stmt->fetchColumn();
    }

    private function getRecentHarvestTotal(int $farmerId): string
    {
        $stmt = $this->db->prepare("
        SELECT COALESCE(SUM(h.quantity), 0) as total, h.unit 
        FROM harvests h
        JOIN plantings p ON h.planting_id = p.planting_id
        WHERE p.farmer_id = :farmer_id
        AND h.harvest_date >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
        GROUP BY h.unit
        ORDER BY total DESC
        LIMIT 1
    ");
        $stmt->execute(['farmer_id' => $farmerId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['total'] . ' ' . $result['unit'] : '0';
    }

    private function getChemicalsUsedCount(int $farmerId): int
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM chemical_usage cu
        JOIN plantings p ON cu.planting_id = p.planting_id
        WHERE p.farmer_id = :farmer_id
        AND cu.date_applied >= DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
    ");
        $stmt->execute(['farmer_id' => $farmerId]);
        return (int) $stmt->fetchColumn();
    }

    private function getRecentActivities(int $farmerId): array
    {
        $stmt = $this->db->prepare("
        (SELECT 
            h.harvest_date as date,
            'Harvest Recorded' as activity,
            CONCAT(h.quantity, ' ', h.unit, ' of ', ct.name) as details
        FROM harvests h
        JOIN plantings p ON h.planting_id = p.planting_id
        JOIN crop_types ct ON p.crop_type_id = ct.crop_id
        WHERE p.farmer_id = :farmer_id)
        
        UNION ALL
        
        (SELECT 
            cu.date_applied as date,
            'Chemical Applied' as activity,
            CONCAT(cu.chemical_name, ' on ', ct.name) as details
        FROM chemical_usage cu
        JOIN plantings p ON cu.planting_id = p.planting_id
        JOIN crop_types ct ON p.crop_type_id = ct.crop_id
        WHERE p.farmer_id = :farmer_id)
        
        UNION ALL
        
        (SELECT 
            p.planting_date as date,
            'New Crop Planted' as activity,
            CONCAT(ct.name, ' in ', p.field_location) as details
        FROM plantings p
        JOIN crop_types ct ON p.crop_type_id = ct.crop_id
        WHERE p.farmer_id = :farmer_id)
        
        ORDER BY date DESC
        LIMIT 5
    ");
        $stmt->execute(['farmer_id' => $farmerId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

            // Get farmer_id
            $userId = $_SESSION['user_id'];
            $stmt = $this->db->prepare("SELECT farmer_id FROM farmer_profiles WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$farmer) {
                throw new Exception("Farmer profile not found");
            }

            $input = $this->validateInput([
                'planting_id' => 'int',
                'chemical_name' => 'string',
                'chemical_type' => 'string',
                'date_applied' => 'date',
                'amount_used' => 'float',
                'unit_of_measurement' => 'string',
                'safety_period_days' => 'int',
                'purpose' => 'string',
                'weather_conditions' => 'string'
            ]);

            $sql = "INSERT INTO chemical_usage (
            planting_id, chemical_name, chemical_type,
            date_applied, purpose, amount_used,
            unit_of_measurement, safety_period_days,
            weather_conditions
        ) VALUES (
            :planting_id, :chemical_name, :chemical_type,
            :date_applied, :purpose, :amount_used,
            :unit_of_measurement, :safety_period_days,
            :weather_conditions
        )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($input);

            $this->setFlashMessage('Chemical usage recorded successfully', 'success');
            $this->redirect('/farmer/chemical-usage');
        } catch (Exception $e) {
            $this->logger->error("Error recording chemical usage: " . $e->getMessage());
            $this->setFlashMessage('Failed to record chemical usage', 'error');
            $this->redirectWithInput('/farmer/chemical-usage', $_POST);
        }
    }

    // public function recordHarvest(): void
    // {
    //     try {
    //         $this->validateAuthenticatedRequest();

    //         $input = $this->validateInput([
    //             'planting_id' => 'int',
    //             'harvest_date' => 'date',
    //             'quantity' => 'float',
    //             'unit' => 'string',
    //             'quality_grade' => 'string',
    //             'storage_location' => 'string'
    //         ]);

    //         $result = $this->plantingModel->recordHarvest($input);
    //         $this->jsonResponse($result);
    //     } catch (Exception $e) {
    //         $this->logger->error("Error recording harvest: " . $e->getMessage());
    //         $this->jsonResponse([
    //             'success' => false,
    //             'message' => 'Failed to record harvest'
    //         ], 500);
    //     }
    // }

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
            $userId = $_SESSION['user_id'];

            // Get farmer profile data
            $sql = "SELECT u.*, fp.* 
                FROM users u 
                JOIN farmer_profiles fp ON u.id = fp.user_id 
                WHERE u.id = :user_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$farmer) {
                throw new Exception("Farmer profile not found");
            }

            $this->render('farmer/account-settings', [
                'farmer' => $farmer,
                'currentPage' => 'account-settings'
            ], 'Account Settings', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading account settings: " . $e->getMessage());
            $this->setFlashMessage('Error loading account settings', 'error');
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
            $farmerProfile = $this->farmerModel->getFarmerProfile($farmerId);

            // Get all crop types for the dropdown
            $cropTypes = $this->getAllCropTypes();

            // Get current plantings
            $plantings = $this->plantingModel->getCurrentPlantings($farmerId);

            $this->render('farmer/manage-crops', [
                'cropTypes' => $cropTypes,
                'plantings' => $plantings,
                'farmer' => $farmerProfile,
                'currentPage' => 'manage-crops'
            ], 'Manage Crops', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading crop management: " . $e->getMessage());
            $this->setFlashMessage('Error loading crops', 'error');
            $this->redirect('/farmer/dashboard');
        }
    }

    public function addCrop(): void
    {
        try {
            $this->validateAuthenticatedRequest();
            $userId = $_SESSION['user_id'];

            // Get farmer_id from farmer_profiles
            $stmt = $this->db->prepare("SELECT farmer_id FROM farmer_profiles WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$farmer) {
                throw new Exception("Farmer profile not found");
            }

            $input = $this->validateInput([
                'crop_type_id' => 'int',
                'field_location' => 'string',
                'area_size' => 'float',
                'area_unit' => 'string',
                'planting_date' => 'date',
                'expected_harvest_date' => 'date',
                'growing_method' => 'string',
                'soil_preparation' => 'string',
                'notes' => 'string'
            ]);

            $data = array_merge($input, [
                'farmer_id' => $farmer['farmer_id'],
                'status' => 'planted'
            ]);

            $stmt = $this->db->prepare("INSERT INTO plantings (
            farmer_id, crop_type_id, field_location, area_size,
            area_unit, planting_date, expected_harvest_date,
            growing_method, soil_preparation, status, notes
        ) VALUES (
            :farmer_id, :crop_type_id, :field_location, :area_size,
            :area_unit, :planting_date, :expected_harvest_date,
            :growing_method, :soil_preparation, :status, :notes
        )");

            $stmt->execute($data);

            $this->setFlashMessage('Crop added successfully', 'success');
            $this->redirect('/farmer/manage-crops');
        } catch (Exception $e) {
            $this->logger->error("Error adding crop: " . $e->getMessage());
            $this->setFlashMessage('Failed to add crop', 'error');
            $this->redirectWithInput('/farmer/manage-crops', $_POST);
        }
    }

    private function getAllCropTypes(): array
    {
        try {
            $sql = "SELECT crop_id, name, category FROM crop_types";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error fetching crop types: " . $e->getMessage());
            return [];
        }
    }

    public function showEditCropForm(): void
    {
        try {
            $this->validateAuthenticatedRequest();
            $plantingId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

            if (!$plantingId) {
                throw new Exception("Invalid planting ID");
            }

            // Get planting details
            $stmt = $this->db->prepare("
            SELECT p.*, ct.name as crop_name, ct.category
            FROM plantings p
            JOIN crop_types ct ON p.crop_type_id = ct.crop_id
            WHERE p.planting_id = :planting_id
            AND p.farmer_id = (
                SELECT farmer_id FROM farmer_profiles WHERE user_id = :user_id
            )
        ");
            $stmt->execute([
                'planting_id' => $plantingId,
                'user_id' => $_SESSION['user_id']
            ]);

            $planting = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$planting) {
                throw new Exception("Planting not found");
            }

            // Get crop types for dropdown
            $cropTypes = $this->getAllCropTypes();

            $this->render('farmer/edit-crop', [
                'planting' => $planting,
                'cropTypes' => $cropTypes,
                'currentPage' => 'manage-crops'
            ], 'Edit Crop', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading edit crop form: " . $e->getMessage());
            $this->setFlashMessage('Error loading crop details', 'error');
            $this->redirect('/farmer/manage-crops');
        }
    }

    public function updateCrop(): void
    {
        try {
            $this->validateAuthenticatedRequest();

            $input = $this->validateInput([
                'planting_id' => 'int',
                'crop_type_id' => 'int',
                'field_location' => 'string',
                'area_size' => 'float',
                'area_unit' => 'string',
                'planting_date' => 'date',
                'expected_harvest_date' => 'date',
                'growing_method' => 'string',
                'soil_preparation' => 'string',
                'notes' => 'string'
            ]);

            $stmt = $this->db->prepare("UPDATE plantings SET
            crop_type_id = :crop_type_id,
            field_location = :field_location,
            area_size = :area_size,
            area_unit = :area_unit,
            planting_date = :planting_date,
            expected_harvest_date = :expected_harvest_date,
            growing_method = :growing_method,
            soil_preparation = :soil_preparation,
            notes = :notes
            WHERE planting_id = :planting_id
            AND farmer_id = (SELECT farmer_id FROM farmer_profiles WHERE user_id = :user_id)
        ");

            $stmt->execute(array_merge($input, ['user_id' => $_SESSION['user_id']]));

            $this->setFlashMessage('Crop updated successfully', 'success');
            $this->redirect('/farmer/manage-crops');
        } catch (Exception $e) {
            $this->logger->error("Error updating crop: " . $e->getMessage());
            $this->setFlashMessage('Failed to update crop', 'error');
            $this->redirectWithInput('/farmer/edit-crop?id=' . $_POST['planting_id'], $_POST);
        }
    }

    public function showHarvestForm(): void
    {
        try {
            SessionManager::initialize();
            $this->validateAuthenticatedRequest();
            $this->validateRole('farmer');

            $plantingId = filter_input(INPUT_GET, 'planting_id', FILTER_VALIDATE_INT);
            if (!$plantingId) {
                throw new Exception("Invalid planting ID");
            }

            // Get user's farmer_id
            $stmt = $this->db->prepare("SELECT farmer_id FROM farmer_profiles WHERE user_id = :user_id");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log farmer details
            $this->logger->info("Farmer details", [
                'user_id' => $_SESSION['user_id'],
                'farmer' => $farmer
            ]);

            if (!$farmer) {
                throw new Exception("Farmer profile not found");
            }

            // Get planting details with crop name
            $query = "
            SELECT p.*, ct.name as crop_name, ct.category
            FROM plantings p
            JOIN crop_types ct ON p.crop_type_id = ct.crop_id
            WHERE p.planting_id = :planting_id
            AND p.farmer_id = :farmer_id
        ";

            $stmt = $this->db->prepare($query);
            $params = [
                'planting_id' => $plantingId,
                'farmer_id' => $farmer['farmer_id']
            ];

            // Log query details
            $this->logger->info("Executing planting query", [
                'query' => $query,
                'params' => $params
            ]);

            $stmt->execute($params);
            $planting = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log planting result
            $this->logger->info("Planting query result", [
                'planting' => $planting
            ]);

            if (!$planting) {
                // Check if planting exists at all
                $stmt = $this->db->prepare("SELECT * FROM plantings WHERE planting_id = :planting_id");
                $stmt->execute(['planting_id' => $plantingId]);
                $anyPlanting = $stmt->fetch(PDO::FETCH_ASSOC);

                $this->logger->error("Planting not found", [
                    'planting_id' => $plantingId,
                    'farmer_id' => $farmer['farmer_id'],
                    'exists_for_other_farmer' => !empty($anyPlanting)
                ]);

                throw new Exception("Planting not found");
            }

            $data = [
                'planting' => $planting,
                'currentPage' => 'manage-crops'
            ];

            $this->render('farmer/record-harvest', $data, 'Record Harvest', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading harvest form: " . $e->getMessage());
            $this->setFlashMessage('Error loading harvest form: ' . $e->getMessage(), 'error');
            $this->redirect('/farmer/manage-crops');
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
                'storage_location' => 'string',
                'create_product' => 'boolean',
                'price_per_unit' => 'float'
            ]);

            $this->db->beginTransaction();

            // Record harvest
            $stmt = $this->db->prepare("INSERT INTO harvests (
            planting_id, harvest_date, quantity, unit,
            quality_grade, storage_location
        ) VALUES (
            :planting_id, :harvest_date, :quantity, :unit,
            :quality_grade, :storage_location
        )");

            $stmt->execute([
                'planting_id' => $input['planting_id'],
                'harvest_date' => $input['harvest_date'],
                'quantity' => $input['quantity'],
                'unit' => $input['unit'],
                'quality_grade' => $input['quality_grade'],
                'storage_location' => $input['storage_location']
            ]);

            $harvestId = $this->db->lastInsertId();

            // Update planting status
            $stmt = $this->db->prepare("UPDATE plantings SET status = 'harvested' WHERE planting_id = :planting_id");
            $stmt->execute(['planting_id' => $input['planting_id']]);

            // Create product if requested
            if (!empty($input['create_product'])) {
                $stmt = $this->db->prepare("
                INSERT INTO products (
                    farmer_id, harvest_id, name, category, price_per_unit,
                    unit_type, stock_quantity, status
                )
                SELECT 
                    p.farmer_id,
                    :harvest_id,
                    ct.name,
                    ct.category,
                    :price_per_unit,
                    h.unit,
                    h.quantity,
                    'available'
                FROM plantings p
                JOIN crop_types ct ON p.crop_type_id = ct.crop_id
                JOIN harvests h ON h.planting_id = p.planting_id
                WHERE p.planting_id = :planting_id
            ");

                $stmt->execute([
                    'harvest_id' => $harvestId,
                    'price_per_unit' => $input['price_per_unit'],
                    'planting_id' => $input['planting_id']
                ]);
            }

            $this->db->commit();
            $this->setFlashMessage('Harvest recorded successfully' .
                ($input['create_product'] ? ' and product created' : ''), 'success');
            $this->redirect('/farmer/manage-crops');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error recording harvest: " . $e->getMessage());
            $this->setFlashMessage('Failed to record harvest', 'error');
            $this->redirectWithInput('/farmer/record-harvest?planting_id=' . $_POST['planting_id'], $_POST);
        }
    }
}
