<?php
namespace App\Models;
use PDO;

class SystemHealth {
    private $db;
    private $logger;

    public function __construct(PDO $db, $logger) {
        $this->db = $db;
        $this->logger = $logger;
    }

    public function checkDatabaseHealth(): array {
        try {
            $startTime = microtime(true);
            $this->db->query("SELECT 1");
            $responseTime = microtime(true) - $startTime;

            return [
                'status' => 'healthy',
                'responseTime' => $responseTime,
                'connections' => $this->getActiveConnections(),
                'uptime' => $this->getDatabaseUptime()
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage()
            ];
        }
    }

    public function checkStorageHealth(): array {
        $uploadDir = __DIR__ . '/../../storage/uploads';
        $totalSpace = disk_total_space($uploadDir);
        $freeSpace = disk_free_space($uploadDir);
        $usedSpace = $totalSpace - $freeSpace;
        $usedPercentage = ($usedSpace / $totalSpace) * 100;

        return [
            'status' => $usedPercentage < 90 ? 'healthy' : 'warning',
            'totalSpace' => $totalSpace,
            'freeSpace' => $freeSpace,
            'usedSpace' => $usedSpace,
            'usedPercentage' => $usedPercentage
        ];
    }

    public function checkQueueHealth(): array {
        // Implement based on your queue system
        return [
            'status' => 'healthy',
            'pendingJobs' => 0,
            'failedJobs' => 0,
            'processingTime' => 0
        ];
    }

    public function getAverageResponseTime(): float {
        // Implement based on your monitoring system
        return 0.1; // Example value in seconds
    }

    public function getErrorRate(): float {
        // Implement based on your error logging system
        return 0.01; // Example value as percentage
    }

    public function getMemoryUsage(): array {
        return [
            'used' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true)
        ];
    }

    private function getActiveConnections(): int {
        $result = $this->db->query("SHOW STATUS WHERE Variable_name = 'Threads_connected'")->fetch();
        return (int)($result['Value'] ?? 0);
    }

    private function getDatabaseUptime(): int {
        $result = $this->db->query("SHOW STATUS WHERE Variable_name = 'Uptime'")->fetch();
        return (int)($result['Value'] ?? 0);
    }

    public function checkServicesHealth(): array {
        try {
            $services = [
                'database' => $this->checkDatabaseHealth(),
                'mail' => $this->checkMailService(),
                'storage' => $this->checkStorageHealth(),
                'queue' => $this->checkQueueHealth()
            ];

            return [
                'status' => !in_array(false, array_column($services, 'status')),
                'services' => $services
            ];
        } catch (\Exception $e) {
            $this->logger->error("Service health check error: " . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Service health check failed'
            ];
        }
    }

    public function getSystemLogs(string $logType, string $startDate, string $endDate, string $level = 'ERROR'): array {
        try {
            $sql = "SELECT * FROM system_logs 
                    WHERE log_type = :log_type 
                    AND level = :level
                    AND created_at BETWEEN :start_date AND :end_date
                    ORDER BY created_at DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'log_type' => $logType,
                'level' => $level,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error retrieving system logs: " . $e->getMessage());
            return [];
        }
    }

    private function checkMailService(): array {
        // Add your mail service check logic here
        return [
            'status' => true,
            'message' => 'Mail service is operational'
        ];
    }
}