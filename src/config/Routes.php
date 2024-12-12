<?php

return [
    'public' => [
        'GET /' => ['HomeController', 'index'],

        'GET /products' => ['ProductController', 'listProducts'],
        'GET /product-details/view' => ['ProductController', 'getProductDetail'],
        'GET /about' => ['HomeController', 'about'],
        'GET /contact' => ['HomeController', 'contact'],
        'GET /shop' => ['ProductController', 'renderShopPage'],
        'GET /cart' => ['CartController', 'index'],
        'POST /cart/add' => ['CartController', 'addToCart'],
        'POST /cart/update' => ['CartController', 'updateCart'],
        'POST /cart/remove' => ['CartController', 'removeFromCart'],
        // 'POST /checkout' => ['CartController', 'checkoutItems'],
        'GET /checkout' => ['CheckoutController', 'index'],
        'POST /checkout/place-order' => ['CheckoutController', 'placeOrder'],
        'GET /order/confirmation/{orderId}' => ['CheckoutController', 'showConfirmation'],
        'GET /cart/items' => ['CartController', 'getCartItems'],
    ],
    'auth' => [
        // Customer auth routes
        'GET /login' => ['AuthController', 'loginForm'],
        'POST /login' => ['AuthController', 'login'],
        'GET /register' => ['AuthController', 'customerRegistrationForm'],
        'POST /api/auth/customers/register' => ['AuthController', 'register'],
        'POST /logout' => ['AuthController', 'logout'],

        // Farmer auth routes
        'GET /register/farmer' => ['AuthController', 'farmerRegistrationForm'],
        // 'POST /farmer/register' => ['AuthController', 'farmerRegister'],
        'GET /api/auth/farmers/register' => ['AuthController', 'farmerRegistrationForm'],
        'POST /farmer/register' => ['AuthController', 'register'],
        'GET /farmer/login' => ['AuthController', 'farmerLoginForm'],
        'POST /api/auth/login' => ['AuthController', 'apiLogin'],
        // 'POST /farmer/login' => ['AuthController', 'login'],

        // Add admin auth routes here
        'GET /admin/login' => ['AdminAuthController', 'showLoginForm'],
        'POST /admin/login' => ['AdminAuthController', 'login'],
        'GET /admin/forgot-password' => ['AdminAuthController', 'showForgotPasswordForm'],
        'POST /admin/forgot-password' => ['AdminAuthController', 'forgotPassword'],
        'POST /admin/logout' => ['AdminAuthController', 'logout'],
    ],
    'admin' => [
        // Dashboard
        'GET /admin/dashboard' => ['AdminController', 'dashboard'],

        // Product Management
        'GET /admin/products' => ['AdminController', 'productManagement'],
        'POST /admin/products/create' => ['AdminController', 'createProduct'],
        'GET /admin/products/{id}' => ['AdminController', 'getProduct'],
        'POST /admin/products/update/{id}' => ['AdminController', 'updateProduct'],
        'POST /admin/products/delete/{id}' => ['AdminController', 'deleteProduct'],
        'POST /admin/products/status/{id}' => ['AdminController', 'updateProductStatus'],

        // Order Management
        'GET /admin/orders' => ['AdminController', 'orderManagement'],
        'GET /admin/orders/details/{id}' => ['AdminController', 'getOrderDetails'],
        'POST /admin/orders/update-status' => ['AdminController', 'updateOrderStatus'],
        'GET /admin/orders/export' => ['AdminController', 'exportOrders'],
        'GET /admin/orders/print/{id}' => ['AdminController', 'printOrder'],

        // Farmer Management
        'GET /admin/farmers' => ['AdminController', 'manageFarmers'],
        'GET /admin/farmers/view' => ['AdminController', 'viewFarmer'],
        'POST /admin/farmers/approve' => ['AdminController', 'approveFarmer'],
        'POST /admin/farmers/reject' => ['AdminController', 'rejectFarmer'],
        'POST /admin/farmers/suspend' => ['AdminController', 'suspendFarmer'],
        'GET /admin/api/farmers/{id}' => ['AdminController', 'getFarmerDetails'],

        // System
        'GET /admin/system' => ['AdminController', 'systemHealth'],
        'GET /admin/system/logs' => ['AdminController', 'systemLogs'],
        'GET /admin/system/metrics' => ['AdminController', 'systemMetrics'],

        // API Endpoints
        // 'GET /admin/api/farmers/{id}' => ['AdminController', 'getFarmerDetails'],
    ],
    'farmer' => [
        'GET /farmer/dashboard' => ['FarmerController', 'index'],
        'GET /farmer/manage-crops' => ['FarmerController', 'manageCrops'],
        'GET /farmer/chemical-usage' => ['FarmerController', 'chemicalUsage'],
        'GET /farmer/record-activity' => ['FarmerController', 'recordActivity'],
        'GET /farmer/account-settings' => ['FarmerController', 'accountSettings'],
        'POST /farmer/update-profile' => ['FarmerController', 'updateProfile'],
        'POST /farmer/logout' => ['FarmerController', 'logout'],
        'POST /farmer/add-crop' => ['FarmerController', 'addCrop'],
        'POST /farmer/record-chemical' => ['FarmerController', 'recordChemicalUsage'],
        'GET /farmer/edit-crop' => ['FarmerController', 'showEditCropForm'],
        'POST /farmer/edit-crop' => ['FarmerController', 'updateCrop'],
        'GET /farmer/record-harvest' => ['FarmerController', 'showHarvestForm'],
        'POST /farmer/record-harvest' => ['FarmerController', 'recordHarvest'],
    ],
    'protected' => [
        'GET /customer/dashboard' => ['CustomerController', 'index'],
        'GET /farmer/dashboard' => ['FarmerController', 'dashboard'],
    ]
];
