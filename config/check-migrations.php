<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Config\Database;

class MigrationChecker {
    private $db;
    private $migrations = [];

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->loadMigrations();
    }

    private function loadMigrations() {
        $path = __DIR__ . '/../mysql/migrations';
        $files = glob($path . '/V*__*.sql');
        foreach ($files as $file) {
            $this->migrations[] = basename($file);
        }
    }

    public function check() {
        echo "Checking migrations...\n";

        // Create migrations table if it doesn't exist
        $this->createMigrationsTable();

        // Get executed migrations
        $executed = $this->getExecutedMigrations();

        // Compare with files
        foreach ($this->migrations as $migration) {
            $version = $this->extractVersion($migration);
            if (in_array($version, $executed)) {
                echo "✓ {$migration} (executed)\n";
            } else {
                echo "✗ {$migration} (pending)\n";
            }
        }
    }

    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            version VARCHAR(255) NOT NULL UNIQUE,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);
    }

    private function getExecutedMigrations() {
        $stmt = $this->db->query("SELECT version FROM migrations ORDER BY id");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function extractVersion($filename) {
        if (preg_match('/V(\d+)__/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }
}

// Run the checker
$checker = new MigrationChecker();
$checker->check();