<?php
namespace App\Controllers;

class HomeController extends BaseController {
    public function index() {
        // Any data you want to pass to the view
        $data = [
            'featuredProducts' => [], // Get your featured products
            'categories' => []  // Get your categories
        ];

        // Render the view
        $this->render('home.view', $data, 'Welcome to AgriKonnect');
    }
}
