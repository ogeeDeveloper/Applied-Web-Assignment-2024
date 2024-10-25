<?php
namespace App\Models;

use PDO;

class Crop {
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function create(array $data): int {
        try {
            $sql = "INSERT INTO crops (
                product_id, planting_date, expected_harvest,
                growth_duration, current_status
            ) VALUES (
                :product_id, :planting_date, :expected_harvest,
                :growth_duration, :current_status
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'product_id' => $data['product_id'],
                'planting_date' => $data['planting_date'],
                'expected_harvest' => $data['expected_harvest'],
                'growth_duration' => $data['growth_duration'] ?? null,
                'current_status' => $data['current_status'] ?? 'planted'
            ]);

            return (int) $this->db->lastInsertId();
        } catch (\PDOException $e) {
            $this->logger->error("Error creating crop: " . $e->getMessage());
            throw new \Exception("Failed to create crop");
        }
    }

    public function getCurrentCrops(int $farmerId): array {
        try {
            $sql = "SELECT c.*, p.name as product_name
                    FROM crops c
                    JOIN products p ON c.product_id = p.product_id
                    WHERE p.farmer_trn = :farmer_id
                    AND c.current_status IN ('planted', 'growing')
                    ORDER BY c.planting_date DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting current crops: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upcoming harvests
     * @param int|null $farmerId Optional farmer ID to filter results
     * @return array Array of upcoming harvests
     */
    public function getUpcomingHarvests(?int $farmerId = null): array {
        try {
            $sql = "SELECT c.*, 
                           p.name as product_name,
                           f.farm_name,
                           f.name as farmer_name,
                           f.location as farm_location,
                           DATEDIFF(c.expected_harvest, CURRENT_DATE) as days_until_harvest,
                           CASE 
                               WHEN DATEDIFF(c.expected_harvest, c.planting_date) = 0 THEN 100
                               ELSE (DATEDIFF(CURRENT_DATE, c.planting_date) * 100.0 / 
                                    DATEDIFF(c.expected_harvest, c.planting_date))
                           END as growth_percentage
                    FROM crops c
                    JOIN products p ON c.product_id = p.product_id
                    JOIN farmers f ON p.farmer_trn = f.farmer_trn
                    WHERE c.current_status = 'growing'
                    AND c.expected_harvest >= CURRENT_DATE";

            $params = [];
            
            // Add farmer filter if farmerId is provided
            if ($farmerId !== null) {
                $sql .= " AND p.farmer_trn = :farmer_id";
                $params['farmer_id'] = $farmerId;
            }

            $sql .= " ORDER BY c.expected_harvest ASC LIMIT 10";
            
            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                $stmt->execute($params);
            } else {
                $stmt->execute();
            }

            $harvests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Process the results
            foreach ($harvests as &$harvest) {
                $harvest['days_until_harvest'] = (int)$harvest['days_until_harvest'];
                $harvest['growth_percentage'] = min(100, max(0, (float)$harvest['growth_percentage']));
                
                // Add chemical usage information
                $harvest['last_chemical_usage'] = $this->getLastChemicalUsage($harvest['crop_id']);
                
                // Calculate safe to harvest date based on last chemical usage
                $harvest['safe_to_harvest_date'] = $this->calculateSafeToHarvestDate($harvest['crop_id']);
                
                // Add additional status information
                $harvest['is_safe_to_harvest'] = $this->isSafeToHarvest($harvest['crop_id']);
                $harvest['estimated_yield'] = $this->calculateEstimatedYield($harvest['crop_id']);
            }

            return $harvests;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting upcoming harvests: " . $e->getMessage());
            return [];
        }
    }

    private function isSafeToHarvest(int $cropId): bool {
        $safeDate = $this->calculateSafeToHarvestDate($cropId);
        if (!$safeDate) {
            return true; // If no chemicals were used, it's safe to harvest
        }
        return strtotime($safeDate) <= strtotime('today');
    }

    private function calculateEstimatedYield(int $cropId): ?float {
        try {
            $sql = "SELECT 
                        c.*, 
                        p.stock_quantity as expected_yield
                    FROM crops c
                    JOIN products p ON c.product_id = p.product_id
                    WHERE c.crop_id = :crop_id";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['crop_id' => $cropId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (float)$result['expected_yield'] : null;
        } catch (\PDOException $e) {
            $this->logger->error("Error calculating estimated yield: " . $e->getMessage());
            return null;
        }
    }

    private function getLastChemicalUsage(int $cropId): ?array {
        try {
            $sql = "SELECT *
                    FROM chemical_usage
                    WHERE crop_id = :crop_id
                    ORDER BY date_applied DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['crop_id' => $cropId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            $this->logger->error("Error getting last chemical usage: " . $e->getMessage());
            return null;
        }
    }

    private function calculateSafeToHarvestDate(int $cropId): ?string {
        try {
            $sql = "SELECT date_applied, safety_period_days
                    FROM chemical_usage
                    WHERE crop_id = :crop_id
                    ORDER BY DATE_ADD(date_applied, INTERVAL safety_period_days DAY) DESC
                    LIMIT 1";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['crop_id' => $cropId]);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $safeDate = date('Y-m-d', strtotime($result['date_applied'] . ' + ' . $result['safety_period_days'] . ' days'));
                return $safeDate;
            }
            
            return null;
        } catch (\PDOException $e) {
            $this->logger->error("Error calculating safe harvest date: " . $e->getMessage());
            return null;
        }
    }

    public function updateCropStatus(int $cropId, string $status): bool {
        try {
            $sql = "UPDATE crops 
                    SET current_status = :status, 
                        updated_at = CURRENT_TIMESTAMP
                    WHERE crop_id = :crop_id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'status' => $status,
                'crop_id' => $cropId
            ]);
        } catch (\PDOException $e) {
            $this->logger->error("Error updating crop status: " . $e->getMessage());
            return false;
        }
    }

    public function addChemicalUsage(array $data): bool {
        try {
            $sql = "INSERT INTO chemical_usage (
                crop_id, chemical_name, chemical_type,
                date_applied, purpose, amount_used,
                unit_of_measurement, safety_period_days
            ) VALUES (
                :crop_id, :chemical_name, :chemical_type,
                :date_applied, :purpose, :amount_used,
                :unit_of_measurement, :safety_period_days
            )";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'crop_id' => $data['crop_id'],
                'chemical_name' => $data['chemical_name'],
                'chemical_type' => $data['chemical_type'],
                'date_applied' => $data['date_applied'],
                'purpose' => $data['purpose'],
                'amount_used' => $data['amount_used'],
                'unit_of_measurement' => $data['unit_of_measurement'],
                'safety_period_days' => $data['safety_period_days'] ?? null
            ]);
        } catch (\PDOException $e) {
            $this->logger->error("Error adding chemical usage: " . $e->getMessage());
            return false;
        }
    }
}