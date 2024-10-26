<?php
namespace App\Database;

use PDO;
use PDOException;

class DatabaseInitializer {
    private PDO $db;
    private string $dbName;

    public function __construct(PDO $db, string $dbName) {
        $this->db = $db;
        $this->dbName = $dbName;
    }

    public function initialize(): void {
        try {
            // Create database if it doesn't exist
            $this->db->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbName}`");
            $this->db->exec("USE `{$this->dbName}`");
            
            // Set proper charset and collation
            $this->db->exec("SET NAMES utf8mb4");
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");
            
            echo "Database initialized successfully!\n";
        } catch (PDOException $e) {
            throw new \RuntimeException("Failed to initialize database: " . $e->getMessage());
        }
    }
}