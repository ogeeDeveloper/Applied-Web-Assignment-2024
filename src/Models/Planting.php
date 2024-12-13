<?php
namespace App\Models;

use PDO;

class Planting {
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Creates a new planting record in the database.
     *
     * @param array $data An associative array containing the planting details.
     * The array should have the following keys:
     * - farmer_id: The ID of the farmer.
     * - crop_type_id: The ID of the crop type.
     * - field_location: The location of the field.
     * - area_size: The size of the area in square units.
     * - area_unit (optional): The unit of the area size (default is 'hectares').
     * - planting_date: The date of planting.
     * - expected_harvest_date: The expected date of harvest.
     * - growing_method (optional): The method of growing the crop.
     * - soil_preparation (optional): The preparation done to the soil.
     * - irrigation_method (optional): The method of irrigation.
     * - notes (optional): Additional notes.
     *
     * @return array An associative array containing the result of the operation.
     * The array will have the following keys:
     * - success: A boolean indicating whether the operation was successful.
     * - planting_id (if success is true): The ID of the newly created planting.
     * - message: A message describing the result of the operation.
     */
    public function create(array $data): array {
        try {
            $sql = "INSERT INTO plantings (
                farmer_id, crop_type_id, field_location, area_size,
                area_unit, planting_date, expected_harvest_date,
                growing_method, soil_preparation, irrigation_method,
                status, notes
            ) VALUES (
                :farmer_id, :crop_type_id, :field_location, :area_size,
                :area_unit, :planting_date, :expected_harvest_date,
                :growing_method, :soil_preparation, :irrigation_method,
                'planned', :notes
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'farmer_id' => $data['farmer_id'],
                'crop_type_id' => $data['crop_type_id'],
                'field_location' => $data['field_location'],
                'area_size' => $data['area_size'],
                'area_unit' => $data['area_unit'] ?? 'hectares',
                'planting_date' => $data['planting_date'],
                'expected_harvest_date' => $data['expected_harvest_date'],
                'growing_method' => $data['growing_method'] ?? null,
                'soil_preparation' => $data['soil_preparation'] ?? null,
                'irrigation_method' => $data['irrigation_method'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            return [
                'success' => true,
                'planting_id' => $this->db->lastInsertId(),
                'message' => 'Planting recorded successfully'
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error creating planting: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record planting'
            ];
        }
    }


    /**
     * Retrieves the current plantings for a specific farmer.
     *
     * @param int $farmerId The ID of the farmer.
     *
     * @return array An array of planting records. Each record is an associative array containing the following keys:
     * - id: The ID of the planting.
     * - farmer_id: The ID of the farmer.
     * - crop_type_id: The ID of the crop type.
     * - field_location: The location of the field.
     * - area_size: The size of the area in square units.
     * - area_unit: The unit of the area size.
     * - planting_date: The date of planting.
     * - expected_harvest_date: The expected date of harvest.
     * - growing_method: The method of growing the crop.
     * - soil_preparation: The preparation done to the soil.
     * - irrigation_method: The method of irrigation.
     * - status: The status of the planting ('planted', 'growing').
     * - notes: Additional notes.
     * - crop_name: The name of the crop type.
     * - category: The category of the crop type.
     *
     * If an error occurs during the database operation, an empty array is returned.
     */
    public function getCurrentPlantings(int $farmerId): array {
        try {
            $sql = "SELECT p.*, c.name as crop_name, c.category
                    FROM plantings p
                    JOIN crop_types c ON p.crop_type_id = c.crop_id
                    WHERE p.farmer_id = :farmer_id
                    AND p.status IN ('planted', 'growing')
                    ORDER BY p.planting_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting current plantings: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Retrieves the upcoming harvests for a specific farmer.
     *
     * @param int $farmerId The ID of the farmer.
     *
     * @return array An array of planting records. Each record is an associative array containing the following keys:
     * - id: The ID of the planting.
     * - farmer_id: The ID of the farmer.
     * - crop_type_id: The ID of the crop type.
     * - field_location: The location of the field.
     * - area_size: The size of the area in square units.
     * - area_unit: The unit of the area size.
     * - planting_date: The date of planting.
     * - expected_harvest_date: The expected date of harvest.
     * - growing_method: The method of growing the crop.
     * - soil_preparation: The preparation done to the soil.
     * - irrigation_method: The method of irrigation.
     * - status: The status of the planting ('growing').
     * - notes: Additional notes.
     * - crop_name: The name of the crop type.
     * - category: The category of the crop type.
     *
     * If an error occurs during the database operation, an empty array is returned.
     */
    public function getUpcomingHarvests(int $farmerId): array {
        try {
            $sql = "SELECT p.*, c.name as crop_name, c.category
                    FROM plantings p
                    JOIN crop_types c ON p.crop_type_id = c.crop_id
                    WHERE p.farmer_id = :farmer_id
                    AND p.status = 'growing'
                    AND p.expected_harvest_date > CURRENT_DATE
                    ORDER BY p.expected_harvest_date ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting upcoming harvests: " . $e->getMessage());
            return [];
        }
    }


    /**
     * Retrieves the monthly harvest statistics for a specific farmer.
     *
     * @param int $farmerId The ID of the farmer.
     *
     * @return array An associative array containing the monthly harvest statistics.
     * The array will have the following keys:
     * - total_harvests: The total number of harvests in the current month.
     * - completed_harvests: The number of completed harvests in the current month.
     * - failed_harvests: The number of failed harvests in the current month.
     *
     * If an error occurs during the database operation, the function will return an array with
     * default values for each statistic (total_harvests = 0, completed_harvests = 0, failed_harvests = 0).
     */
    public function getMonthlyHarvestStats(int $farmerId): array {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_harvests,
                        SUM(CASE WHEN status = 'harvested' THEN 1 ELSE 0 END) as completed_harvests,
                        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_harvests
                    FROM plantings
                    WHERE farmer_id = :farmer_id
                    AND (status IN ('harvested', 'failed'))
                    AND MONTH(actual_harvest_date) = MONTH(CURRENT_DATE)
                    AND YEAR(actual_harvest_date) = YEAR(CURRENT_DATE)";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting monthly harvest stats: " . $e->getMessage());
            return [
                'total_harvests' => 0,
                'completed_harvests' => 0,
                'failed_harvests' => 0
            ];
        }
    }

    
    public function recordHarvest(array $data): array {
        try {
            $this->db->beginTransaction();

            // First update the planting status
            $sql = "UPDATE plantings 
                    SET status = 'harvested',
                        actual_harvest_date = :harvest_date,
                        updated_at = NOW()
                    WHERE planting_id = :planting_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'harvest_date' => $data['harvest_date'],
                'planting_id' => $data['planting_id']
            ]);

            // Then create the harvest record
            $sql = "INSERT INTO harvests (
                planting_id, harvest_date, quantity, unit,
                quality_grade, loss_quantity, loss_reason,
                storage_location, storage_conditions, notes
            ) VALUES (
                :planting_id, :harvest_date, :quantity, :unit,
                :quality_grade, :loss_quantity, :loss_reason,
                :storage_location, :storage_conditions, :notes
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'planting_id' => $data['planting_id'],
                'harvest_date' => $data['harvest_date'],
                'quantity' => $data['quantity'],
                'unit' => $data['unit'],
                'quality_grade' => $data['quality_grade'] ?? null,
                'loss_quantity' => $data['loss_quantity'] ?? 0,
                'loss_reason' => $data['loss_reason'] ?? null,
                'storage_location' => $data['storage_location'] ?? null,
                'storage_conditions' => $data['storage_conditions'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            $harvestId = $this->db->lastInsertId();

            // If product creation is requested
            if (!empty($data['create_product'])) {
                $sql = "INSERT INTO products (
                    farmer_id, harvest_id, name, category,
                    description, price_per_unit, unit_type,
                    stock_quantity, organic_certified, status
                ) VALUES (
                    :farmer_id, :harvest_id, :name, :category,
                    :description, :price_per_unit, :unit_type,
                    :stock_quantity, :organic_certified, 'available'
                )";

                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'farmer_id' => $data['farmer_id'],
                    'harvest_id' => $harvestId,
                    'name' => $data['product_name'] ?? null,
                    'category' => $data['product_category'] ?? null,
                    'description' => $data['product_description'] ?? null,
                    'price_per_unit' => $data['price_per_unit'] ?? 0,
                    'unit_type' => $data['unit'],
                    'stock_quantity' => $data['quantity'],
                    'organic_certified' => $data['organic_certified'] ?? false
                ]);
            }

            $this->db->commit();

            return [
                'success' => true,
                'harvest_id' => $harvestId,
                'message' => 'Harvest recorded successfully'
            ];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->logger->error("Error recording harvest: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record harvest'
            ];
        }
    }
}