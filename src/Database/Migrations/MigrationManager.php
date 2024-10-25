<?php
namespace App\Database\Migrations;

use PDO;
use PDOException;
use RuntimeException;

class MigrationManager {
    private PDO $db;
    private string $migrationsPath;
    private array $migrations = [];

    public function __construct(PDO $db, string $migrationsPath) {
        $this->db = $db;
        $this->migrationsPath = $migrationsPath;
        $this->initMigrationsTable();
    }

    private function initMigrationsTable(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    version VARCHAR(255) NOT NULL UNIQUE,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create migrations table: " . $e->getMessage());
        }
    }

    public function migrate(): void {
        $files = glob($this->migrationsPath . '/V*__*.sql');
        sort($files); // Ensure migrations run in order

        foreach ($files as $file) {
            $version = $this->extractVersionFromFilename($file);
            if (!$this->hasMigrationBeenExecuted($version)) {
                $this->executeMigration($file, $version);
            }
        }
    }

    private function extractVersionFromFilename(string $file): string {
        preg_match('/V(\d+)__/', basename($file), $matches);
        return $matches[1] ?? throw new RuntimeException("Invalid migration filename format");
    }

    private function hasMigrationBeenExecuted(string $version): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM migrations WHERE version = ?");
        $stmt->execute([$version]);
        return (bool) $stmt->fetchColumn();
    }

    private function executeMigration(string $file, string $version): void {
        try {
            $sql = file_get_contents($file);
            $this->db->beginTransaction();
            $this->db->exec($sql);
            $this->recordMigration($version);
            $this->db->commit();
            echo "Executed migration: " . basename($file) . PHP_EOL;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to execute migration {$version}: " . $e->getMessage());
        }
    }

    private function recordMigration(string $version): void {
        $stmt = $this->db->prepare("INSERT INTO migrations (version) VALUES (?)");
        $stmt->execute([$version]);
    }
}