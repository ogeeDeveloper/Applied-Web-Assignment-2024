<?php
namespace App\Controllers;
use App\Models\Product;
use App\Controllers\ProductController;
class HomeController extends BaseController {
    private $productcontroller;
    public function __construct( $db, $logger){
        parent::__construct($db, $logger);
        $this->productcontroller= new ProductController($db, $logger);
    } 
    public function index() 
    {
  
            try {
                // Get popular products
                $popularProducts = $this->productcontroller->getPopularProducts(20);
    
                // Prepare data for the view
                $viewData = [
                    'popularProducts' => $popularProducts['data'],
                    'metaDescription' => 'Connect with local farmers and buy fresh produce directly from the source.'
                ];
    
        // Render the view
        
        
        $this->render('home.view', $viewData, 'Welcome to AgriKonnect');
            }
            catch (Exception $e) {
                $this->logger->error("Error loading home page: " . $e->getMessage());
                $this->render('home', [
                    'error' => 'Unable to load some content. Please try again later.',
                    'pageTitle' => 'Welcome to AgriKonnect'
                ]);
            }
    }

    public function about() {
        // $result = $this->productModel->getProductDetails($id);

        $this->render('about.view');
    }

}
