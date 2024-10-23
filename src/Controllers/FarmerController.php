<?php

namespace App\Controllers;

use App\Models\Farmer;
use App\Config\Database;

class FarmerController {
    private $db;
    private $farmerModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->farmerModel = new Farmer($this->db);
    }

    // Farmer-specific methods
}
