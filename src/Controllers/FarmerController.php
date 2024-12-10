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
use App\Models\MediaManager;
use App\Services\MailService;

class FarmerController extends BaseController
{
    private $farmerModel;
    private $productModel;
    private $orderModel;
    private $plantingModel;
    private $chemicalUsageModel;
    private $cropModel;
    private $userModel;
    private MediaManager $mediaManager;
    private MailService $mailService;

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
        $this->mediaManager = new MediaManager($db, $logger);
        $this->mailService = new MailService($logger);
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

            // Validate input
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

            // Get current profile data for comparison
            $stmt = $this->db->prepare("
            SELECT fp.*, u.name, u.email 
            FROM farmer_profiles fp
            JOIN users u ON fp.user_id = u.id
            WHERE fp.user_id = :user_id
        ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $currentProfile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$currentProfile) {
                throw new Exception("Farmer profile not found");
            }

            // Track changes
            $changes = [];
            $fieldLabels = [
                'farm_name' => 'Farm Name',
                'location' => 'Location',
                'farm_type' => 'Farm Type',
                'farm_size' => 'Farm Size',
                'farming_experience' => 'Farming Experience',
                'primary_products' => 'Primary Products',
                'organic_certified' => 'Organic Certification',
                'phone_number' => 'Phone Number'
            ];

            foreach ($input as $field => $newValue) {
                $oldValue = $currentProfile[$field];
                if ($field === 'organic_certified') {
                    $oldValue = (bool)$oldValue;
                    $newValue = (bool)$newValue;
                }

                if ($oldValue !== $newValue) {
                    $changes[$fieldLabels[$field]] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }

            // Update profile
            $result = $this->farmerModel->updateProfile(
                $_SESSION['user_id'],
                $input
            );

            // Send email notification if there are changes
            if (!empty($changes)) {
                $emailData = [
                    'farmer_name' => $currentProfile['name'],
                    'email' => $currentProfile['email'],
                    'changes' => $changes,
                    'update_time' => date('Y-m-d H:i:s'),
                    'ip_address' => $_SERVER['REMOTE_ADDR']
                ];

                $this->mailService->sendTemplateEmail(
                    $currentProfile['email'],
                    'Profile Update Notification',
                    'farmer-profile-update',
                    $emailData
                );

                // Log the changes
                $this->logger->info("Farmer profile updated", [
                    'user_id' => $_SESSION['user_id'],
                    'changes' => $changes,
                    'ip' => $_SERVER['REMOTE_ADDR']
                ]);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Profile updated successfully',
                'changes' => $changes
            ]);
        } catch (Exception $e) {
            $this->logger->error("Profile update error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->jsonResponse([
                'success' => false,
                'message' => 'Failed to update profile: ' . $e->getMessage()
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
            // Get the current user's farmer_id
            $stmt = $this->db->prepare("
            SELECT fp.farmer_id, fp.farm_name, fp.farm_type
            FROM farmer_profiles fp
            WHERE fp.user_id = :user_id
        ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $farmerProfile = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$farmerProfile) {
                throw new Exception("Farmer profile not found");
            }

            // Get crop types for the dropdown
            $cropTypes = $this->getAllCropTypes();

            // Get current plantings with detailed information
            $plantingsQuery = "
            SELECT p.*, 
                   ct.name as crop_name,
                   ct.category,
                   DATEDIFF(p.expected_harvest_date, CURRENT_DATE) as days_until_harvest,
                   CASE 
                       WHEN p.status = 'growing' THEN 
                           ROUND((DATEDIFF(CURRENT_DATE, p.planting_date) * 100.0) / 
                                NULLIF(DATEDIFF(p.expected_harvest_date, p.planting_date), 0), 1)
                       ELSE NULL 
                   END as growth_percentage
            FROM plantings p
            JOIN crop_types ct ON p.crop_type_id = ct.crop_id
            WHERE p.farmer_id = :farmer_id
            ORDER BY 
                CASE p.status
                    WHEN 'growing' THEN 1
                    WHEN 'planted' THEN 2
                    WHEN 'planned' THEN 3
                    ELSE 4
                END,
                p.expected_harvest_date ASC";

            $stmt = $this->db->prepare($plantingsQuery);
            $stmt->execute(['farmer_id' => $farmerProfile['farmer_id']]);
            $plantings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Log the results for debugging
            $this->logger->info("Retrieved plantings for farmer", [
                'farmer_id' => $farmerProfile['farmer_id'],
                'planting_count' => count($plantings),
                'user_id' => $_SESSION['user_id']
            ]);

            // Add readable status labels and format dates
            foreach ($plantings as &$planting) {
                $planting['status_label'] = ucfirst($planting['status']);
                $planting['planting_date'] = date('Y-m-d', strtotime($planting['planting_date']));
                $planting['expected_harvest_date'] = date('Y-m-d', strtotime($planting['expected_harvest_date']));

                // Add CSS classes for status badges
                $planting['status_class'] = match ($planting['status']) {
                    'growing' => 'success',
                    'planted' => 'primary',
                    'planned' => 'info',
                    'harvested' => 'secondary',
                    'failed' => 'danger',
                    default => 'secondary'
                };
            }

            $this->render('farmer/manage-crops', [
                'cropTypes' => $cropTypes,
                'plantings' => $plantings,
                'farmer' => $farmerProfile,
                'currentPage' => 'manage-crops'
            ], 'Manage Crops', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading crop management: " . $e->getMessage(), [
                'user_id' => $_SESSION['user_id'],
                'trace' => $e->getTraceAsString()
            ]);
            $this->setFlashMessage('Error loading crops: ' . $e->getMessage(), 'error');
            $this->redirect('/farmer/dashboard');
        }
    }

    public function addCrop(): void
    {
        try {
            $this->validateAuthenticatedRequest();
            $userId = $_SESSION['user_id'];

            // Start transaction
            $this->db->beginTransaction();

            // Get farmer details
            $stmt = $this->db->prepare("
            SELECT fp.farmer_id, fp.farm_name, u.name, u.email 
            FROM farmer_profiles fp
            JOIN users u ON fp.user_id = u.id
            WHERE fp.user_id = :user_id
        ");
            $stmt->execute(['user_id' => $userId]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$farmer) {
                throw new Exception("Farmer profile not found");
            }

            // Validate input
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

            // Get crop type details
            $stmt = $this->db->prepare("
            SELECT name, category 
            FROM crop_types 
            WHERE crop_id = :crop_id
        ");
            $stmt->execute(['crop_id' => $input['crop_type_id']]);
            $cropType = $stmt->fetch(PDO::FETCH_ASSOC);

            // Prepare planting data
            $data = array_merge($input, [
                'farmer_id' => $farmer['farmer_id'],
                'status' => 'planted'
            ]);

            // Insert planting
            $stmt = $this->db->prepare("
            INSERT INTO plantings (
                farmer_id, crop_type_id, field_location, area_size,
                area_unit, planting_date, expected_harvest_date,
                growing_method, soil_preparation, status, notes
            ) VALUES (
                :farmer_id, :crop_type_id, :field_location, :area_size,
                :area_unit, :planting_date, :expected_harvest_date,
                :growing_method, :soil_preparation, :status, :notes
            )
        ");
            $stmt->execute($data);
            $plantingId = $this->db->lastInsertId();

            // Prepare email data
            $emailData = [
                'farmer_name' => $farmer['name'],
                'email' => $farmer['email'],
                'farm_name' => $farmer['farm_name'],
                'crop_name' => $cropType['name'],
                'crop_category' => $cropType['category'],
                'field_location' => $input['field_location'],
                'area_size' => $input['area_size'],
                'area_unit' => $input['area_unit'],
                'planting_date' => $input['planting_date'],
                'expected_harvest_date' => $input['expected_harvest_date'],
                'growing_method' => $input['growing_method'],
                'planting_id' => $plantingId
            ];

            // Send email notification
            $emailSent = $this->mailService->sendTemplateEmail(
                $farmer['email'],
                'New Crop Added to Your Farm',
                'new-crop-notification',
                $emailData
            );

            // Log the action
            $this->logger->info("New crop added", [
                'farmer_id' => $farmer['farmer_id'],
                'planting_id' => $plantingId,
                'crop_type' => $cropType['name'],
                'email_sent' => $emailSent
            ]);

            $this->db->commit();

            $this->setFlashMessage('Crop added successfully', 'success');
            $this->redirect('/farmer/manage-crops');
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->logger->error("Error adding crop: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            $this->setFlashMessage('Failed to add crop: ' . $e->getMessage(), 'error');
            $this->redirectWithInput('/farmer/manage-crops', $_POST);
        }
    }

    private function getAllCropTypes(): array
    {
        try {
            $sql = "SELECT crop_id, name, category, 
                       typical_growth_duration,
                       growing_season
                FROM crop_types
                ORDER BY category, name";
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

            // Get farmer profile with detailed logging
            $stmt = $this->db->prepare("
            SELECT fp.farmer_id, fp.farm_name 
            FROM farmer_profiles fp
            WHERE fp.user_id = :user_id
        ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            $this->logger->info("Farmer profile lookup result", [
                'user_id' => $_SESSION['user_id'],
                'farmer_data' => $farmer
            ]);

            if (!$farmer) {
                $this->logger->error("Farmer profile not found", [
                    'user_id' => $_SESSION['user_id']
                ]);
                throw new Exception("Farmer profile not found");
            }

            // Query planting with detailed logging
            $query = "
            SELECT p.*, 
                   ct.name as crop_name, 
                   ct.category,
                   fp.farm_name
            FROM plantings p
            JOIN crop_types ct ON p.crop_type_id = ct.crop_id
            JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
            WHERE p.planting_id = :planting_id
            AND p.farmer_id = :farmer_id
            AND p.status IN ('planted', 'growing')
        ";

            $params = [
                'planting_id' => $plantingId,
                'farmer_id' => $farmer['farmer_id']
            ];

            // Log the query and parameters
            $this->logger->info("Executing planting query", [
                'query' => $query,
                'params' => $params
            ]);

            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $planting = $stmt->fetch(PDO::FETCH_ASSOC);

            // Log the query result
            $this->logger->info("Planting query result", [
                'planting' => $planting,
                'has_result' => !empty($planting)
            ]);

            if (!$planting) {
                // Check if planting exists at all
                $checkStmt = $this->db->prepare("
                SELECT p.*, fp.farmer_id as owner_id, u.name as owner_name
                FROM plantings p
                JOIN farmer_profiles fp ON p.farmer_id = fp.farmer_id
                JOIN users u ON fp.user_id = u.id
                WHERE p.planting_id = :planting_id
            ");
                $checkStmt->execute(['planting_id' => $plantingId]);
                $existingPlanting = $checkStmt->fetch(PDO::FETCH_ASSOC);

                $this->logger->error("Unauthorized access or invalid planting", [
                    'planting_id' => $plantingId,
                    'farmer_id' => $farmer['farmer_id'],
                    'exists_for_other_farmer' => !empty($existingPlanting),
                    'planting_details' => $existingPlanting ? [
                        'owner_id' => $existingPlanting['owner_id'],
                        'owner_name' => $existingPlanting['owner_name'],
                        'status' => $existingPlanting['status']
                    ] : null
                ]);

                throw new Exception(
                    !empty($existingPlanting) ?
                        "Unauthorized access: This planting belongs to another farmer" :
                        "Planting not found"
                );
            }

            $data = [
                'planting' => $planting,
                'farmer' => $farmer,
                'currentPage' => 'manage-crops'
            ];

            $this->render('farmer/record-harvest', $data, 'Record Harvest', 'farmer/layouts/farmer');
        } catch (Exception $e) {
            $this->logger->error("Error loading harvest form: " . $e->getMessage());
            $this->setFlashMessage('Error: ' . $e->getMessage(), 'error');
            $this->redirect('/farmer/manage-crops');
        }
    }

    /**
     * Handle product image upload during harvest recording
     */
    private function handleProductImages(int $productId, array $files): array
    {
        try {
            $this->logger->info("Starting product image handling", [
                'product_id' => $productId,
                'files' => $files
            ]);

            if (empty($files['product_images']) || empty($files['product_images']['tmp_name'][0])) {
                throw new Exception("No images were uploaded");
            }

            $results = [];
            foreach ($files['product_images']['tmp_name'] as $key => $tmpName) {
                if ($files['product_images']['error'][$key] !== UPLOAD_ERR_OK) {
                    throw new Exception("Upload error for image {$key}: " .
                        $this->getUploadErrorMessage($files['product_images']['error'][$key]));
                }

                $imageFile = [
                    'name' => $files['product_images']['name'][$key],
                    'type' => $files['product_images']['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $files['product_images']['error'][$key],
                    'size' => $files['product_images']['size'][$key]
                ];

                $this->logger->info("Processing image", ['image' => $imageFile]);

                $uploadResult = $this->mediaManager->uploadFile($imageFile, 'product', $productId);
                if (!$uploadResult['success']) {
                    throw new Exception("Failed to upload image {$key}: " . ($uploadResult['message'] ?? 'Unknown error'));
                }

                $results[] = $uploadResult;
            }

            return [
                'success' => true,
                'results' => $results
            ];
        } catch (Exception $e) {
            $this->logger->error("Product image handling error: " . $e->getMessage(), [
                'product_id' => $productId,
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    private function getUploadErrorMessage(int $errorCode): string
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive",
            UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive",
            UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded",
            UPLOAD_ERR_NO_FILE => "No file was uploaded",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload",
            default => "Unknown upload error: " . $errorCode
        };
    }

    public function recordHarvest(): void
    {
        try {
            $this->validateAuthenticatedRequest();

            // Debug logging of incoming request
            $this->logger->info("Form submission received", [
                'post' => $_POST,
                'files' => $_FILES,
                'request' => $_SERVER['REQUEST_METHOD']
            ]);

            // Validate input
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

            // Get farmer_id for the logged-in user
            $stmt = $this->db->prepare("
            SELECT fp.farmer_id, fp.farm_name 
            FROM farmer_profiles fp 
            WHERE fp.user_id = :user_id
        ");
            $stmt->execute(['user_id' => $_SESSION['user_id']]);
            $farmer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$farmer) {
                throw new Exception("Farmer profile not found");
            }

            // Verify planting ownership
            $stmt = $this->db->prepare("
            SELECT p.*, ct.name as crop_name, ct.category
            FROM plantings p
            JOIN crop_types ct ON p.crop_type_id = ct.crop_id
            WHERE p.planting_id = :planting_id 
            AND p.farmer_id = :farmer_id
            AND p.status IN ('planted', 'growing')
        ");
            $stmt->execute([
                'planting_id' => $input['planting_id'],
                'farmer_id' => $farmer['farmer_id']
            ]);

            $planting = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$planting) {
                throw new Exception("Invalid planting or unauthorized access");
            }

            // Record harvest
            $stmt = $this->db->prepare("
            INSERT INTO harvests (
                planting_id, 
                harvest_date, 
                quantity, 
                unit,
                quality_grade, 
                storage_location
            ) VALUES (
                :planting_id, 
                :harvest_date, 
                :quantity, 
                :unit,
                :quality_grade, 
                :storage_location
            )
        ");

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
            $stmt = $this->db->prepare("
            UPDATE plantings 
            SET 
                status = 'harvested',
                actual_harvest_date = :harvest_date
            WHERE planting_id = :planting_id 
            AND farmer_id = :farmer_id
        ");

            $stmt->execute([
                'harvest_date' => $input['harvest_date'],
                'planting_id' => $input['planting_id'],
                'farmer_id' => $farmer['farmer_id']
            ]);

            $productId = null;
            // Create product if requested
            if (!empty($input['create_product'])) {
                $stmt = $this->db->prepare("
                INSERT INTO products (
                    farmer_id,
                    harvest_id,
                    name,
                    category,
                    description,
                    price_per_unit,
                    unit_type,
                    stock_quantity,
                    status
                ) VALUES (
                    :farmer_id,
                    :harvest_id,
                    :name,
                    :category,
                    :description,
                    :price_per_unit,
                    :unit_type,
                    :stock_quantity,
                    'available'
                )
            ");

                $stmt->execute([
                    'farmer_id' => $farmer['farmer_id'],
                    'harvest_id' => $harvestId,
                    'name' => $planting['crop_name'],
                    'category' => $planting['category'],
                    'description' => "Fresh harvest from " . $farmer['farm_name'],
                    'price_per_unit' => $input['price_per_unit'],
                    'unit_type' => $input['unit'],
                    'stock_quantity' => $input['quantity']
                ]);

                $productId = $this->db->lastInsertId();

                // Handle product images if any were uploaded
                if (!empty($_FILES['product_images']) && !empty($_FILES['product_images']['tmp_name'][0])) {
                    $this->logger->info("Processing product images", [
                        'product_id' => $productId,
                        'files' => $_FILES['product_images']
                    ]);

                    $imageResult = $this->handleProductImages($productId, $_FILES);
                    if (!$imageResult['success']) {
                        throw new Exception("Failed to process product images: " . $imageResult['message']);
                    }
                }
            }

            $this->db->commit();

            // Build success message
            $successMessage = 'Harvest recorded successfully';
            if ($productId) {
                $successMessage .= ' and product listing created';
            }

            $this->setFlashMessage($successMessage, 'success');
            $this->redirect('/farmer/manage-crops');
        } catch (Exception $e) {
            $this->db->rollBack();

            $this->logger->error("Error recording harvest", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'post_data' => $_POST,
                'files' => $_FILES
            ]);

            $this->setFlashMessage('Failed to record harvest: ' . $e->getMessage(), 'error');
            $this->redirectWithInput(
                '/farmer/record-harvest?planting_id=' . $_POST['planting_id'],
                $_POST
            );
        }
    }
}
