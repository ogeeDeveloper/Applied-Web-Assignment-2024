<?php

namespace App\Controllers;

use App\Models\Farmer;
use PDO;

class FarmerController extends BaseController {
    private $farmerModel;

    public function __construct(PDO $db, $logger) {
        parent::__construct($db, $logger);
        $this->farmerModel = new Farmer($db, $logger);
    }
}
