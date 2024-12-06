<?php
<<<<<<< HEAD
// PHP Variables for Dynamic Content
$storeLocation = "Lincoln- 344, Illinois, Chicago, USA";
$cartItemCount = 2; // Example cart item count
$cartTotal = 100.00; // Example total cart price
$languageOptions = [
    "Eng" => "English",
    "Bng" => "Bengali",
    "Ch" => "Chinese",
    "Urdu" => "Urdu"
    
    
];
$currencyOptions = [
    "USD" => "US Dollar",
    "Tk" => "Taka",
    "Yen" => "Yen",
    "Rup" => "Rupee"
    
];
?>

<header class="header header--one">
    <div class="header__top">
        <div class="container">
            <div class="header__top-content">
                <div class="header__top-left">
                    <p class="font-body--sm">
                        <span>
                            <svg width="17" height="20" viewBox="0 0 17 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 8.36364C16 14.0909 8.5 19 8.5 19C8.5 19 1 14.0909 1 8.36364C1 6.41068 1.79018 4.53771 3.1967 3.15676C4.60322 1.77581 6.51088 1 8.5 1C10.4891 1 12.3968 1.77581 13.8033 3.15676C15.2098 4.53771 16 6.41068 16 8.36364Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M8.5 10.8182C9.88071 10.8182 11 9.71925 11 8.36364C11 7.00803 9.88071 5.90909 8.5 5.90909C7.11929 5.90909 6 7.00803 6 8.36364C6 9.71925 7.11929 10.8182 8.5 10.8182Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        Store Location: <?php echo $storeLocation; ?>
                    </p>
                </div>
                <div class="header__top-right">
                    <div class="header__dropdown">
                        <select id="selectbox1" class="header__dropdown-menu">
                            <?php foreach ($languageOptions as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="selectbox2" class="header__dropdown-menu">
                            <?php foreach ($currencyOptions as $key => $value): ?>
                                <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                            <?php endforeach; ?>
                        </select>
=======

namespace App\Views\Shared;

use App\Utils\Functions;
use App\Utils\AssetsHelper;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(__DIR__)));
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - AgriKonnect' : 'AgriKonnect'; ?></title>
    <link rel="icon" type="image/png" href="images/favicon/favicon-16x16.png" />

    <?php echo AssetsHelper::loadCSS(
        [
            'bootstrap.min.css',
            'main.css',
            'style.css',
            'swiper-bundle.min.css',
            'nouislider.min.css',
            'venobox.css'
        ]
    ); ?>

    <!-- Library CSS Files -->
    <link rel="stylesheet" href="<?php echo AssetsHelper::lib('css/swiper-bundle.min.css'); ?>" />
    <link rel="stylesheet" href="<?php echo AssetsHelper::lib('css/bvselect.css'); ?>" />
</head>

<body>
    <header>
        <!-- Header Top -->
        <div class="header__top">
            <div class="container">
                <div class="header__top-content">
                    <div class="header__top-left">
                        <span>Location: AgriKonnect, Jamaica</span>
>>>>>>> 4d4fb05ad719ef70d7991bbe5354b53fd4d6f483
                    </div>
                    <div class="header__in">
                        <a href="sign-in.php">Sign in </a>
                        <span>/</span>
                        <a href="create-account.php">Sign up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="header__center">
        <div class="container">
            <div class="header__center-content">
                <div class="header__brand">
                    <button class="header__sidebar-btn">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3 12H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M3 6H21" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            <path d="M3 18H15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </button>
                    <a href="index.php">
                    <img src="/images/banner/logo1.JPG " alt="banner">

                    </a>
                </div>
                <form action="#">
                    <div class="header__input-form">
                        <input type="text" placeholder="Search">
                        <span class="search-icon">
                            <svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.16667 16.3333C12.8486 16.3333 15.8333 13.3486 15.8333 9.66667C15.8333 5.98477 12.8486 3 9.16667 3C5.48477 3 2.5 5.98477 2.5 9.66667C2.5 13.3486 5.48477 16.3333 9.16667 16.3333Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                <path d="M17.4999 18L13.8749 14.375" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                            </svg>
                        </span>
                        <button type="submit" class="search-btn button button--md">
                            Search
                        </button>
                    </div>
                </form>
                <div class="header__cart">
                    <div class="header__cart-item">
                        <a class="fav" href="wishlist.php">
                            <svg width="25" height="23" viewBox="0 0 20 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9.9996 16.5451C-6.66672 7.3333 4.99993 -2.6667 9.9996 3.65668C14.9999 -2.6667 26.6666 7.3333 9.9996 16.5451Z" stroke="#1A1A1A" stroke-width="1.5"></path>
                            </svg>
                        </a>
                    </div>
                    <div class="header__cart-item">
                        <div class="header__cart-item-content" id="cart-bag">
                            <button class="cart-bag">
                                <svg width="34" height="35" viewBox="0 0 34 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M11.3333 14.6667H7.08333L4.25 30.25H29.75L26.9167 14.6667H22.6667M11.3333 14.6667V10.4167C11.3333 7.28705 13.8704 4.75 17 4.75V4.75C20.1296 4.75 22.6667 7.28705 22.6667 10.4167V14.6667M11.3333 14.6667H22.6667M11.3333 14.6667V18.9167M22.6667 14.6667V18.9167" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                                <span class="item-number"><?php echo $cartItemCount; ?></span>
                            </button>
                            <div class="header__cart-item-content-info">
                                <h5>Shopping cart:</h5>
                                <span class="price">$<?php echo number_format($cartTotal, 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
// PHP Arrays for Menu Items

<<<<<<< HEAD
=======
        <!-- Header Center -->
        <div class="header__center">
            <div class="container">
                <div class="header__center-content">
                    <div class="header__brand">
                        <a href="/">
                            <img src="<?php echo AssetsHelper::logo(); ?>" alt="AgriKonnect">
                        </a>
                    </div>
>>>>>>> 4d4fb05ad719ef70d7991bbe5354b53fd4d6f483

$shopPages = [
    'Home' => 'index.html',
    
];

$pages = [
    'User Dashboard' => 'user-dashboard.html',
    'Order History' => 'order-history.html',
    'Order Details' => 'order-details.html',
    'Account Settings' => 'account-setting.html',
    'Product Details' => 'product-details.html',
    'Wishlist' => 'wishlist.html',
    'Shopping Cart' => 'shopping-cart.html',
    'Sign in' => 'sign-in.html',
    'Create Account' => 'create-account.html',
    'FAQ' => 'faq.html',
    'Error 404' => '404.html'
];
?>

<div class="container">
    <div class="header__bottom-content">
        <ul class="header__navigation-menu">
            <!-- Homepages -->
            <li class="header__navigation-menu-link active">
                <a href="#">
                    Home
                    <span class="drop-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </a>
                <ul class="header__navigation-drop-menu">
                    <?php foreach ($homePages as $pageName => $url): ?>
                        <li class="header__navigation-drop-menu-link<?php echo ($pageName === 'Homepage 01') ? ' active' : ''; ?>">
                            <a href="<?php echo $url; ?>"><?php echo $pageName; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Shopepages -->
            <li class="header__navigation-menu-link">
                <a href="#">
                    Shop
                    <span class="drop-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </a>
                <ul class="header__navigation-drop-menu">
                    <?php foreach ($shopPages as $pageName => $url): ?>
                        <li class="header__navigation-drop-menu-link">
                            <a href="<?php echo $url; ?>"><?php echo $pageName; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>

            <!-- Pages -->
            <li class="header__navigation-menu-link">
                <a href="#">
                    Pages
                    <span class="drop-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </span>
                </a>
                <ul class="header__navigation-drop-menu">
                    <?php foreach ($pages as $pageName => $url): ?>
                        <li class="header__navigation-drop-menu-link">
                            <a href="<?php echo $url; ?>"><?php echo $pageName; ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </li>
        </ul>
    </div>
</div>

<!-- Telephone Number Section -->
 <?




