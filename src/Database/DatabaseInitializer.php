<?php

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

class DatabaseInitializer
{
    private PDO $db;
    private string $dbName;

    public function __construct(string $dbName)
    {
        $this->dbName = $dbName;
        $this->db = $this->createConnection();
    }

    private function createConnection(): PDO
    {
        try {
            $host = getenv('MYSQL_HOST');
            $user = getenv('MYSQL_USER');
            $pass = getenv('MYSQL_PASSWORD');

            // Connect without database first
            $dsn = "mysql:host={$host};charset=utf8mb4";

            return new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create database connection: " . $e->getMessage());
        }
    }

    public function initialize(): void
    {
        try {
            // Create database if it doesn't exist
            $this->db->exec("CREATE DATABASE IF NOT EXISTS `{$this->dbName}`");
            $this->db->exec("USE `{$this->dbName}`");

            // Set proper charset and collation
            $this->db->exec("SET NAMES utf8mb4");
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

            echo "Database initialized successfully!\n";
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to initialize database: " . $e->getMessage());
        }
    }

    /**
     * Create a new connection specifically for migrations
     */
    public function createMigrationConnection(): PDO
    {
        try {
            $host = getenv('MYSQL_HOST');
            $user = getenv('MYSQL_USER');
            $pass = getenv('MYSQL_PASSWORD');

            $dsn = "mysql:host={$host};dbname={$this->dbName};charset=utf8mb4";

            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ]);

            // Test transaction support
            if (!$pdo->beginTransaction()) {
                throw new RuntimeException("Migration connection does not support transactions");
            }
            $pdo->rollBack();

            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException("Failed to create migration connection: " . $e->getMessage());
        }
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }

    public function getDatabaseName(): string
    {
        return $this->dbName;
    }
}
