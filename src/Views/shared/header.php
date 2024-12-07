<?php

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
   
   / <link rel="icon" type="image/png" href="images/favicon/favicon-16x16.png" /> 

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
                        <span>Location: Kingston 10, Jamaica</span>
                    </div>
                    <div class="header__top-right">
                        <div class="header__in">
                            <?php if (Functions::isLoggedIn()): ?>
                                <a href="/account">My Account</a>
                            <?php else: ?>
                                <a href="/login">Sign in</a> / <a href="/register">Sign up</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Header Center -->
        <div class="header__center">
            <div class="container">
                <div class="header__center-content">
                    <div class="header__brand">
                        <a href="/">
                            <img src="<?php echo AssetsHelper::logo(); ?>" alt="AgriKonnect">
                        </a>
                    </div>

                    <!-- Search Form -->
                    <form class="header__input-form" action="/search" method="GET">
                        <input type="text" name="q" placeholder="Search products...">
                        <button type="submit">Search</button>
                    </form>

                    <!-- Cart -->
                    <div class="header__cart">
                        <?php include APP_ROOT . '/src/Views/shared/cart-mini.php'; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Include only the navigation component -->
        <?php require_once APP_ROOT . '/src/Views/shared/navigation.php'; ?>
    </header>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
        <div class="container mt-3">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo Functions::h($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo Functions::h($_SESSION['success']); ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>