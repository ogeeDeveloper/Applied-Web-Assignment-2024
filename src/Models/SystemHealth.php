<?php

namespace App\Models;

use PDO;

class SystemHealth
{
    private $db;
    private $logger;

    public function __construct(PDO $db, $logger)
    {
        $this->db = $db;
        $this->logger = $logger;
    }

    /**
     * Check database health
     */
    public function checkDatabaseHealth(): array
    {
        try {
            $startTime = microtime(true);

            // Simple query to check database connectivity
            $stmt = $this->db->query("SELECT 1");
            $stmt->fetch();

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            // Get database stats
            $stmt = $this->db->query("SHOW GLOBAL STATUS");
            $stats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            return [
                'status' => 'healthy',
                'response_time' => round($responseTime, 2),
                'connections' => $stats['Threads_connected'] ?? 0,
                'uptime' => $this->formatUptime($stats['Uptime'] ?? 0)
            ];
        } catch (\PDOException $e) {
            $this->logger->error("Database health check failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check storage health
     */
    public function checkStorageHealth(): array
    {
        try {
            $totalSpace = disk_total_space('/');
            $freeSpace = disk_free_space('/');
            $usedSpace = $totalSpace - $freeSpace;
            $usagePercentage = ($usedSpace / $totalSpace) * 100;

            return [
                'status' => $usagePercentage < 90 ? 'healthy' : 'warning',
                'total_space' => $this->formatBytes($totalSpace),
                'free_space' => $this->formatBytes($freeSpace),
                'used_space' => $this->formatBytes($usedSpace),
                'usage_percentage' => round($usagePercentage, 2)
            ];
        } catch (\Exception $e) {
            $this->logger->error("Storage health check failed: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Storage check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    public function checkQueueHealth(): array
    {
        // Implement based on your queue system
        return [
            'status' => 'healthy',
            'pendingJobs' => 0,
            'failedJobs' => 0,
            'processingTime' => 0
        ];
    }

    public function getAverageResponseTime(): float
    {
        // Implement based on your monitoring system
        return 0.1; // Example value in seconds
    }

    public function getErrorRate(): float
    {
        // Implement based on your error logging system
        return 0.01; // Example value as percentage
    }

    public function getMemoryUsage(): array
    {
        return [
            'used' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true)
        ];
    }

    private function getActiveConnections(): int
    {
        $result = $this->db->query("SHOW STATUS WHERE Variable_name = 'Threads_connected'")->fetch();
        return (int)($result['Value'] ?? 0);
    }

    private function getDatabaseUptime(): int
    {
        $result = $this->db->query("SHOW STATUS WHERE Variable_name = 'Uptime'")->fetch();
        return (int)($result['Value'] ?? 0);
    }

    /**
     * Check services health
     */
    public function checkServicesHealth(): array
    {
        $services = [
            'queue' => $this->checkQueueHealth(),
            'cache' => $this->checkCacheHealth(),
            'uploads' => $this->checkUploadsHealth()
        ];

        $allHealthy = array_reduce($services, function ($carry, $service) {
            return $carry && $service['status'] === 'healthy';
        }, true);

        return [
            'status' => $allHealthy ? 'healthy' : 'warning',
            'services' => $services
        ];
    }

    private function checkCacheHealth(): array
    {
        // Implement cache health check
        return [
            'status' => 'healthy',
            'message' => 'Cache system operational'
        ];
    }

    private function checkUploadsHealth(): array
    {
        $uploadPath = APP_ROOT . '/public/uploads';

        if (!is_dir($uploadPath) || !is_writable($uploadPath)) {
            return [
                'status' => 'error',
                'message' => 'Upload directory not writable'
            ];
        }

        return [
            'status' => 'healthy',
            'message' => 'Upload system operational'
        ];
    }

    private function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return sprintf(
            '%d days, %d hours, %d minutes',
            $days,
            $hours,
            $minutes
        );
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function getSystemLogs(string $logType, string $startDate, string $endDate, string $level = 'ERROR'): array
    {
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

    private function checkMailService(): array
    {
        // Add your mail service check logic here
        return [
            'status' => true,
            'message' => 'Mail service is operational'
        ];
    }

    /**
     * Get recent system logs
     * 
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function getRecentLogs(int $limit = 20): array
    {
        try {
            $sql = "SELECT 
                        id,
                        level,
                        message,
                        context,
                        created_at as timestamp
                    FROM system_logs 
                    ORDER BY created_at DESC 
                    LIMIT :limit";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            $this->logger->error("Error getting system logs: " . $e->getMessage());
            return [];
        }
    }
}
