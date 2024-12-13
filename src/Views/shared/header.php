<?php

namespace App\Views\Shared;

use App\Utils\Functions;
use App\Utils\AssetsHelper;

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
                    </div>
                    <div class="header__in">
                        <a href="/login">Sign in </a>
                        <span>/</span>
                        <a href="/register">Sign up</a>
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
                            <div class="header__cart-item-content" id="cart-bag">
                                <button class="cart-bag" id="cart-trigger">
                                    <svg width="34" height="35" viewBox="0 0 34 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M11.3333 14.6667H7.08333L4.25 30.25H29.75L26.9167 14.6667H22.6667M11.3333 14.6667V10.4167C11.3333 7.28705 13.8704 4.75 17 4.75V4.75C20.1296 4.75 22.6667 7.28705 22.6667 10.4167V14.6667M11.3333 14.6667H22.6667M11.3333 14.6667V18.9167M22.6667 14.6667V18.9167" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                    </svg>
                                    <span class="item-number" id="cart-count">0</span>
                                </button>
                                <div class="header__cart-item-content-info">
                                    <h5>Shopping cart:</h5>
                                    <span class="price" id="cart-total">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php require_once("navigation.php"); ?>

        <div class="cart-overlay" id="cart-overlay"></div>
        <div class="shopping-cart" id="shopping-cart">
            <div class="shopping-cart-top">
                <div class="shopping-cart-header">
                    <h5 class="font-body--xxl-500">Shopping Cart (<span class="count" id="cart-sidebar-count">0</span>)</h5>
                    <button class="close" id="close-cart">
                        <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="22.5" cy="22.5" r="22.5" fill="white" />
                            <path d="M28.75 16.25L16.25 28.75" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M16.25 16.25L28.75 28.75" stroke="#1A1A1A" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
                </div>
                <div class="shopping-cart__product-content" id="cart-items">
                    <!-- Cart items will be dynamically inserted here -->
                </div>
            </div>
            <div class="shopping-cart-bottom">
                <div class="shopping-cart-product-info">
                    <p class="product-count font-body--lg-400"><span id="cart-sidebar-item-count">0</span> Products</p>
                    <span class="product-price font-body--lg-500" id="cart-sidebar-total">$0.00</span>
                </div>
                <div class="shopping-cart-actions">
                    <a href="/checkout" class="button button--lg w-100 mb-4">Checkout</a>
                    <a href="/cart" class="button button--lg w-100">View Cart</a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const cartTrigger = document.getElementById('cart-trigger');
                const cartSidebar = document.getElementById('shopping-cart');
                const closeCart = document.getElementById('close-cart');
                const cartOverlay = document.getElementById('cart-overlay');
                const cartItems = document.getElementById('cart-items');
                const cartCount = document.getElementById('cart-count');
                const cartTotal = document.getElementById('cart-total');
                const cartSidebarCount = document.getElementById('cart-sidebar-count');
                const cartSidebarItemCount = document.getElementById('cart-sidebar-item-count');
                const cartSidebarTotal = document.getElementById('cart-sidebar-total');

                // Open cart sidebar
                cartTrigger.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent any default behavior
                    event.stopPropagation(); // Stop event from propagating
                    cartSidebar.classList.add('active');
                    cartOverlay.classList.add('active');
                    updateCartSidebar();
                });

                // Close cart sidebar
                closeCart.addEventListener('click', () => {
                    cartSidebar.classList.remove('active');
                    cartOverlay.classList.remove('active');
                });

                cartOverlay.addEventListener('click', () => {
                    cartSidebar.classList.remove('active');
                    cartOverlay.classList.remove('active');
                });

                async function updateCartSidebar() {
                    try {
                        const response = await fetch('/cart/items');
                        const data = await response.json();

                        if (data.success) {
                            // Update cart counts
                            const itemCount = data.items.reduce((sum, item) => sum + item.quantity, 0);
                            cartCount.textContent = itemCount;
                            cartSidebarCount.textContent = itemCount;
                            cartSidebarItemCount.textContent = itemCount;

                            // Update totals
                            const total = data.items.reduce((sum, item) => sum + (item.price_at_time * item.quantity), 0);
                            cartTotal.textContent = `$${total.toFixed(2)}`;
                            cartSidebarTotal.textContent = `$${total.toFixed(2)}`;

                            // Update cart items
                            cartItems.innerHTML = data.items.map(item => `
                <div class="shopping-cart__product-content-item">
                    <div class="img-wrapper">
                        <img src="${item.media_files}" alt="${item.name}" />
                    </div>
                    <div class="text-content">
                        <h5 class="font-body--md-400">${item.name}</h5>
                        <p class="font-body--md-400">${item.quantity} x <span class="font-body--md-500">$${item.price_at_time.toFixed(2)}</span></p>
                    </div>
                    <button class="delete-item" onclick="removeCartItem(${item.product_id})">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 23C18.0748 23 23 18.0748 23 12C23 5.92525 18.0748 1 12 1C5.92525 1 1 5.92525 1 12C1 18.0748 5.92525 23 12 23Z" stroke="#CCCCCC" stroke-miterlimit="10"/>
                            <path d="M16 8L8 16" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M16 16L8 8" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
            `).join('');
                        }
                    } catch (error) {
                        console.error('Error updating cart:', error);
                    }
                }

                // Remove item from cart
                window.removeCartItem = async function(productId) {
                    try {
                        const response = await fetch('/cart/remove', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                cart_id: productId
                            })
                        });

                        const data = await response.json();
                        if (data.success) {
                            updateCartSidebar();
                        }
                    } catch (error) {
                        console.error('Error removing item:', error);
                    }
                }

                // Initial cart update
                updateCartSidebar();
            });
        </script>