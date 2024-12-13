<?php

namespace App\Commands;

use App\Config\Database;
use App\Database\Migrations\MigrationManager;
use PDO;

class MigrationsCommand
{
    private PDO $db;
    private string $migrationsPath;

    public function __construct()
    {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->migrationsPath = dirname(__DIR__, 2) . '/mysql/migrations';
    }

    public function migrate(): void
    {
        try {
            $migrationManager = new MigrationManager($this->db, $this->migrationsPath);
            echo "Starting migrations...\n";
            $migrationManager->migrate();
            echo "Migrations completed successfully!\n";
        } catch (\Exception $e) {
            echo "Error running migrations: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    public function status(): void
    {
        try {
            $migrationManager = new MigrationManager($this->db, $this->migrationsPath);
            $migrations = $this->getMigrationStatus();

            echo "\nMigration Status:\n";
            echo str_repeat('-', 80) . "\n";
            echo sprintf("%-40s %-20s %s\n", 'Migration', 'Status', 'Executed At');
            echo str_repeat('-', 80) . "\n";

            foreach ($migrations as $migration) {
                echo sprintf(
                    "%-40s %-20s %s\n",
                    $migration['name'],
                    $migration['executed'] ? 'Executed' : 'Pending',
                    $migration['executed_at'] ?? 'N/A'
                );
            }
        } catch (\Exception $e) {
            echo "Error getting migration status: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function getMigrationStatus(): array
    {
        $migrations = [];
        $files = glob($this->migrationsPath . '/V*__*.sql');
        sort($files);

        // Get executed migrations from database
        $stmt = $this->db->query("SELECT version, executed_at FROM migrations ORDER BY version");
        $executed = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($files as $file) {
            $version = $this->extractVersionFromFilename($file);
            $migrations[] = [
                'name' => basename($file),
                'executed' => isset($executed[$version]),
                'executed_at' => $executed[$version] ?? null
            ];
        }

        return $migrations;
    }

    private function extractVersionFromFilename(string $file): string
    {
        if (preg_match('/V(\d+)__/', basename($file), $matches)) {
            return $matches[1];
        }
        throw new \RuntimeException("Invalid migration filename format: " . basename($file));
    }
}
