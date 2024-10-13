<?php
class Database {
    private $host = "mysql";
    private $db_name = getenv('MYSQL_DATABASE'); 
    private $username = getenv('MYSQL_USER');
    private $password = getenv('MYSQL_PASSWORD');
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}