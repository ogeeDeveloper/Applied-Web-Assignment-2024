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
            // First, select the database
            $this->db->exec("USE " . getenv('MYSQL_DATABASE'));

            $sql = "
                CREATE TABLE IF NOT EXISTS migrations (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    version VARCHAR(255) NOT NULL UNIQUE,
                    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $this->db->exec($sql);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create migrations table: " . $e->getMessage());
        }
    }

    public function migrate(): void {
        try {
            // Get all migration files
            $files = glob($this->migrationsPath . '/V*__*.sql');
            sort($files); // Ensure migrations run in order

            foreach ($files as $file) {
                $version = $this->extractVersionFromFilename($file);
                if (!$this->hasMigrationBeenExecuted($version)) {
                    $this->executeMigration($file, $version);
                } else {
                    echo "Migration {$version} already executed.\n";
                }
            }
        } catch (PDOException $e) {
            throw new RuntimeException("Migration failed: " . $e->getMessage());
        }
    }

    private function extractVersionFromFilename(string $file): string {
        if (preg_match('/V(\d+)__/', basename($file), $matches)) {
            return $matches[1];
        }
        throw new RuntimeException("Invalid migration filename format: " . basename($file));
    }

    private function hasMigrationBeenExecuted(string $version): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM migrations WHERE version = ?");
        $stmt->execute([$version]);
        return (bool) $stmt->fetchColumn();
    }

    private function executeMigration(string $file, string $version): void {
        try {
            $sql = file_get_contents($file);
            if ($sql === false) {
                throw new RuntimeException("Could not read migration file: " . $file);
            }

            $this->db->beginTransaction();
            
            // Split SQL by semicolon to execute multiple statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                'strlen'
            );

            foreach ($statements as $statement) {
                $this->db->exec($statement);
            }

            $this->recordMigration($version);
            $this->db->commit();

            echo "Executed migration: " . basename($file) . "\n";
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new RuntimeException("Failed to execute migration {$version}: " . $e->getMessage());
        }
    }

    private function recordMigration(string $version): void {
        $stmt = $this->db->prepare("INSERT INTO migrations (version) VALUES (?)");
        $stmt->execute([$version]);
    }

    public function getMigrationStatus(): array {
        $status = [];
        $files = glob($this->migrationsPath . '/V*__*.sql');
        sort($files);

        foreach ($files as $file) {
            $version = $this->extractVersionFromFilename($file);
            $executed = $this->hasMigrationBeenExecuted($version);
            $status[] = [
                'version' => $version,
                'file' => basename($file),
                'executed' => $executed,
                'executed_at' => $executed ? $this->getMigrationExecutionDate($version) : null
            ];
        }

        return $status;
    }

    private function getMigrationExecutionDate(string $version): ?string {
        $stmt = $this->db->prepare("SELECT executed_at FROM migrations WHERE version = ?");
        $stmt->execute([$version]);
        return $stmt->fetchColumn();
    }
}