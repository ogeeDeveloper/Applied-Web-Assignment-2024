<?php

namespace App\Database\Migrations;

use PDO;
use PDOException;
use RuntimeException;

class MigrationManager
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct(PDO $db, string $migrationsPath)
    {
        // Enable buffered queries
        $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

        $this->db = $db;
        // $this->migrationsPath = $migrationsPath;
        $this->migrationsPath = rtrim($migrationsPath, '/');
        $this->initMigrationsTable();
    }

    private function initMigrationsTable(): void
    {
        try {
            $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(255) NOT NULL UNIQUE,
                name VARCHAR(255) NOT NULL COMMENT 'Migration file name',
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
            $this->db->exec($sql);

            // Check if the 'name' column exists, and add it if not
            $stmt = $this->db->query("SHOW COLUMNS FROM migrations LIKE 'name'");
            if ($stmt->rowCount() === 0) {
                $this->db->exec("
                ALTER TABLE migrations
                ADD COLUMN name VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Migration file name' AFTER version
            ");
            }
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to initialize migrations table: " . $e->getMessage());
        }
    }


    public function migrate(): void
    {
        try {
            $files = glob($this->migrationsPath . '/V*__*.sql');
            sort($files); // Ensure migrations run in order

            foreach ($files as $file) {
                $version = $this->extractVersionFromFilename($file);
                if (!$this->hasMigrationBeenExecuted($version)) {
                    // Retry logic for migrations
                    $retryCount = 3;
                    for ($attempt = 1; $attempt <= $retryCount; $attempt++) {
                        try {
                            $this->executeMigration($file, $version);
                            break; // Exit loop on success
                        } catch (PDOException $e) {
                            echo "Migration attempt {$attempt} failed: " . $e->getMessage() . "\n";
                            if ($attempt === $retryCount) {
                                throw new RuntimeException("Migration {$version} failed after {$retryCount} attempts.");
                            }
                        }
                    }
                } else {
                    echo "Migration {$version} already executed.\n";
                }
            }
        } catch (PDOException $e) {
            throw new RuntimeException("Migration failed: " . $e->getMessage());
        }
    }

    private function extractVersionFromFilename(string $file): string
    {
        if (preg_match('/V(\d+)__/', basename($file), $matches)) {
            return $matches[1];
        }
        throw new RuntimeException("Invalid migration filename format: " . basename($file));
    }

    private function hasMigrationBeenExecuted(string $version): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM migrations WHERE version = ?");
        $stmt->execute([$version]);
        return (bool) $stmt->fetchColumn();
    }

    private function executeMigration(string $file, string $version): void
    {
        echo "Reading migration file: " . basename($file) . "\n";

        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new RuntimeException("Could not read migration file: " . $file);
        }

        echo "Checking if transaction is required for migration {$version}...\n";

        // Check if the migration script contains non-transactional DDL
        $isTransactional = $this->isMigrationTransactional($sql);
        if ($isTransactional) {
            echo "Transaction supported for migration {$version}. Starting transaction...\n";
            $this->db->beginTransaction();
        } else {
            echo "Migration {$version} includes non-transactional statements. Executing without transaction...\n";
        }

        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            'strlen'
        );

        try {
            foreach ($statements as $index => $statement) {
                if (!empty($statement)) {
                    echo "Executing statement " . ($index + 1) . " for version {$version}:\n";
                    echo $statement . "\n";
                    $this->db->exec($statement);
                }
            }

            // Record the migration as executed
            $stmt = $this->db->prepare("INSERT INTO migrations (version, name) VALUES (?, ?)");
            $stmt->execute([$version, basename($file)]);
            echo "Migration {$version} recorded successfully.\n";

            // Commit the transaction if it was started
            if ($isTransactional && $this->db->inTransaction()) {
                $this->db->commit();
                echo "Transaction committed for migration {$version}.\n";
            }
        } catch (PDOException $e) {
            // Rollback if transaction was started
            if ($isTransactional && $this->db->inTransaction()) {
                echo "Error during migration {$version}. Rolling back transaction...\n";
                $this->db->rollBack();
            }
            throw new RuntimeException("Failed to execute migration {$version}: " . $e->getMessage());
        }
    }

    /**
     * Determine if the migration script is transactional.
     * @param string $sql
     * @return bool
     */
    private function isMigrationTransactional(string $sql): bool
    {
        // List of non-transactional statements in MySQL
        $nonTransactionalKeywords = ['CREATE', 'ALTER', 'DROP', 'TRUNCATE', 'RENAME'];

        foreach ($nonTransactionalKeywords as $keyword) {
            if (stripos($sql, $keyword) !== false) {
                return false;
            }
        }
        return true;
    }


    private function recordMigration(string $version): void
    {
        $stmt = $this->db->prepare("INSERT INTO migrations (version) VALUES (?)");
        $stmt->execute([$version]);
    }

    /**
     * Get status of all migrations
     * @return void
     */
    public function getMigrationStatus(): void
    {
        try {
            // $files = glob($this->migrationsPath . '/V*__*.sql');
            $files = glob($this->migrationsPath . '/V*__*.sql');
            sort($files);

            // Get executed migrations from the database
            $stmt = $this->db->query("SELECT version, name, executed_at FROM migrations ORDER BY version");
            $executed = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Map executed migrations for easy access
            $executedMigrations = [];
            foreach ($executed as $row) {
                $executedMigrations[$row['version']] = $row;
            }

            // Print header
            echo "\nMigration Status:\n";
            echo str_repeat("-", 120) . "\n";
            echo sprintf("%-20s %-50s %-15s %s\n", "Version", "Name", "Status", "Executed At");
            echo str_repeat("-", 120) . "\n";

            foreach ($files as $file) {
                $version = $this->extractVersionFromFilename($file);
                $filename = basename($file);
                $status = isset($executedMigrations[$version]) ? 'Executed' : 'Pending';
                $executedAt = isset($executedMigrations[$version]) ? $executedMigrations[$version]['executed_at'] : 'N/A';
                $name = isset($executedMigrations[$version]) ? $executedMigrations[$version]['name'] : $filename;

                echo sprintf(
                    "%-20s %-50s %-15s %s\n",
                    $version,
                    $name,
                    $status,
                    $executedAt
                );
            }
            echo str_repeat("-", 120) . "\n";
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to get migration status: " . $e->getMessage());
        }
    }


    public function cleanup(): void
    {
        try {
            $cleanupFile = $this->migrationsPath . '/V0__cleanup.sql';
            echo "Looking for cleanup file at: " . $cleanupFile . "\n";

            if (!file_exists($cleanupFile)) {
                throw new RuntimeException("Cleanup file not found at: " . $cleanupFile);
            }

            $sql = file_get_contents($cleanupFile);
            if ($sql === false) {
                throw new RuntimeException("Could not read cleanup file: " . $cleanupFile);
            }

            echo "Executing cleanup script...\n";

            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                'strlen'
            );

            foreach ($statements as $index => $statement) {
                echo "Executing cleanup statement " . ($index + 1) . "...\n";
                $this->db->exec($statement);
            }

            echo "Database cleaned successfully\n";
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to cleanup database: " . $e->getMessage());
        }
    }

    public function rollbackMigration(string $version): void
    {
        try {
            $this->db->beginTransaction();

            // Delete the migration record
            $stmt = $this->db->prepare("DELETE FROM migrations WHERE version = ?");
            $stmt->execute([$version]);

            $this->db->commit();
            echo "Rolled back migration version {$version}\n";
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw new RuntimeException("Failed to rollback migration {$version}: " . $e->getMessage());
        }
    }

    /**
     * Get list of pending migrations
     * @return array
     */
    private function getPendingMigrations(): array
    {
        $files = glob($this->migrationsPath . '/V*__*.sql');
        sort($files);

        $pending = [];
        foreach ($files as $file) {
            $version = $this->extractVersionFromFilename($file);
            if (!$this->hasMigrationBeenExecuted($version)) {
                $pending[] = $file;
            }
        }

        return $pending;
    }
}
