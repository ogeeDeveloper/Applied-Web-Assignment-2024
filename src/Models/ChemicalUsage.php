<?php
namespace App\Models;

use PDO;

class ChemicalUsage {
    private PDO $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Records a new chemical usage entry in the database.
     *
     * @param array $data An associative array containing the following keys:
     *                    - planting_id: The ID of the planting to which the chemical usage applies.
     *                    - chemical_name: The name of the chemical used.
     *                    - chemical_type: The type of the chemical used.
     *                    - date_applied: The date when the chemical was applied.
     *                    - purpose: The purpose for which the chemical was applied.
     *                    - amount_used: The amount of the chemical used.
     *                    - unit_of_measurement: The unit of measurement for the amount used.
     *                    - safety_period_days: The number of days after application during which the chemical should not be used.
     *                    - weather_conditions: Additional weather conditions during application.
     *                    - application_method: The method used to apply the chemical.
     *                    - notes: Any additional notes about the chemical usage.
     *
     * @return array An associative array containing the following keys:
     *               - success: A boolean indicating whether the chemical usage was recorded successfully.
     *               - chemical_usage_id: The ID of the newly created chemical usage entry (if successful).
     *               - message: A message describing the outcome of the operation.
     */
    public function create(array $data): array {
        try {
            $sql = "INSERT INTO chemical_usage (
                planting_id, chemical_name, chemical_type,
                date_applied, purpose, amount_used,
                unit_of_measurement, safety_period_days,
                weather_conditions, application_method, notes
            ) VALUES (
                :planting_id, :chemical_name, :chemical_type,
                :date_applied, :purpose, :amount_used,
                :unit_of_measurement, :safety_period_days,
                :weather_conditions, :application_method, :notes
            )";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'planting_id' => $data['planting_id'],
                'chemical_name' => $data['chemical_name'],
                'chemical_type' => $data['chemical_type'],
                'date_applied' => $data['date_applied'],
                'purpose' => $data['purpose'] ?? null,
                'amount_used' => $data['amount_used'],
                'unit_of_measurement' => $data['unit_of_measurement'],
                'safety_period_days' => $data['safety_period_days'],
                'weather_conditions' => $data['weather_conditions'] ?? null,
                'application_method' => $data['application_method'] ?? null,
                'notes' => $data['notes'] ?? null
            ]);

            return [
                'success' => true,
                'chemical_usage_id' => $this->db->lastInsertId(),
                'message' => 'Chemical usage recorded successfully'
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Error recording chemical usage: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record chemical usage'
            ];
        }
    }

    /**
    * Retrieves chemical usage entries for a specific planting.
    *
    * @param int $plantingId The ID of the planting for which to retrieve the chemical usage entries.
    *
    * @return array An array of associative arrays, each representing a chemical usage entry.
    *               Each entry contains the following keys://+
    *               - chemical_usage_id: The ID of the chemical usage entry.
    *               - planting_id: The ID of the planting to which the chemical usage applies.
    *               - chemical_name: The name of the chemical used.
    *               - chemical_type: The type of the chemical used.
    *               - date_applied: The date when the chemical was applied.
    *               - purpose: The purpose for which the chemical was applied.
    *               - amount_used: The amount of the chemical used.
    *               - unit_of_measurement: The unit of measurement for the amount used.
    *               - safety_period_days: The number of days after application during which the chemical should not be used.
    *               - weather_conditions: Additional weather conditions during application.
    *               - application_method: The method used to apply the chemical.
    *               - notes: Any additional notes about the chemical usage.
    *
    *               If an error occurs during the retrieval process, an empty array is returned.
    */
    public function getByPlanting(int $plantingId): array {
        try {
            $sql = "SELECT *
                    FROM chemical_usage
                    WHERE planting_id = :planting_id
                    ORDER BY date_applied DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['planting_id' => $plantingId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting chemical usage: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retrieves the most recent 10 chemical usage entries for a specific farmer.
     *
     * @param int $farmerId The ID of the farmer for whom to retrieve the chemical usage entries.
     *
     * @return array An array of associative arrays, each representing a chemical usage entry.
     *               Each entry contains the following keys://+
     *               - chemical_usage_id: The ID of the chemical usage entry.
     *               - planting_id: The ID of the planting to which the chemical usage applies.
     *               - chemical_name: The name of the chemical used.
     *               - chemical_type: The type of the chemical used.
     *               - date_applied: The date when the chemical was applied.
     *               - purpose: The purpose for which the chemical was applied.
     *               - amount_used: The amount of the chemical used.//+
     *               - unit_of_measurement: The unit of measurement for the amount used.
     *               - safety_period_days: The number of days after application during which the chemical should not be used.
     *               - weather_conditions: Additional weather conditions during application.
     *               - application_method: The method used to apply the chemical.
     *               - notes: Any additional notes about the chemical usage.
     *               - field_location: The location of the field where the planting is taking place.
     *
     *               If an error occurs during the retrieval process, an empty array is returned.
     *///
    public function getRecentUsage(int $farmerId): array {
        try {
            $sql = "SELECT cu.*, p.field_location
                    FROM chemical_usage cu
                    JOIN plantings p ON cu.planting_id = p.planting_id
                    WHERE p.farmer_id = :farmer_id
                    ORDER BY cu.date_applied DESC
                    LIMIT 10";

            $stmt = $this->db->prepare($sql);
            $stmt->execute(['farmer_id' => $farmerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting recent chemical usage: " . $e->getMessage());
            return [];
        }
    }
}


