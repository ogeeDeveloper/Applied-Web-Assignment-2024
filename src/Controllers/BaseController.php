<?php
namespace App\Controllers;

class BaseController {
    protected $db;
    protected $logger;

    public function __construct($db = null, $logger = null) {
        $this->db = $db;
        $this->logger = $logger;
    }

    protected function render($view, $data = [], $pageTitle = null) {
        // Extract data to make it available in view
        if (!empty($data)) {
            extract($data);
        }

        // Start output buffering
        ob_start();
        
        // Include the view file
        require_once APP_ROOT . "/src/Views/{$view}.php";
        
        // Get the buffered content
        $content = ob_get_clean();
        
        // Set page title
        $pageTitle = $pageTitle ?? 'AgriKonnect';
        
        // Include the layout with the content
        require_once APP_ROOT . '/src/Views/layouts/main.php';
    }
}