<?php

use App\Utils\Functions;

$currentPath = Functions::getCurrentPath();

// Define main navigation structure
$mainNav = [
    'Home' => [
        'url' => '/',
        'submenu' => null
    ],
    'Shop' => [
        'url' => '/shop',
        'submenu' => [
            'Vegetables' => '/shop/fruits?id=1',
            'Fruits' => '/shop/fruits?id=2',
            'Rice & Grains' => '/shop/rice-grains?id=3',
            'Fresh Products' => '/shop/fresh-products?id=4'
        ]
    ],
    'Farmers' => [
        'url' => '/farmers',
        'submenu' => [
            'Meet Our Farmers' => '/farmers/meet',
            'Become a Seller' => '/api/auth/farmers/register',
            'Farm Directory' => '/farmers/directory'
        ]
    ],
    'About' => [
        'url' => '/about',
        'submenu' => null
    ],
    'Contact' => [
        'url' => '/contact',
        'submenu' => null
    ],
    'Products' => [
        'url' => '/product_details',
        'submenu' => null
    ]


];
?>

<div class="header__bottom header__bottom--white">
    <div class="container">
        <nav class="header__bottom-content">
            <!-- Main Navigation -->
            <ul class="main-nav">
                <?php foreach ($mainNav as $label => $item): ?>
                    <li class="<?php echo ($currentPath === $item['url']) ? 'active' : ''; ?>">
                        <a href="<?php echo Functions::h($item['url']); ?>">
                            <?php echo Functions::h($label); ?>
                            <?php if ($item['submenu']): ?>
                                <svg class="drop-icon" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3.33332 5.66667L7.99999 10.3333L12.6667 5.66667"
                                        stroke="currentColor" stroke-width="1.5"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            <?php endif; ?>
                        </a>
                        <?php if ($item['submenu']): ?>
                            <ul class="submenu">
                                <?php foreach ($item['submenu'] as $subLabel => $subUrl): ?>
                                    <li>
                                        <a href="<?php echo Functions::h($subUrl); ?>">
                                            <?php echo Functions::h($subLabel); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- Contact Number -->
            <a href="tel:+639123456789" class="header__telephone-number dark">
                <svg width="23" height="23" viewBox="0 0 23 23" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M14.4359 2.375C15.9193 2.77396 17.2718 3.55567 18.358 4.64184C19.4441 5.72801 20.2258 7.08051 20.6248 8.56388"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M7.115 11.6517C8.02238 13.5074 9.5263 15.0049 11.3859 15.9042C11.522 15.9688 11.6727 15.9966 11.8229 15.9851C11.9731 15.9736 12.1178 15.9231 12.2425 15.8386L14.9812 14.0134C15.1022 13.9326 15.2414 13.8833 15.3862 13.8698C15.5311 13.8564 15.677 13.8793 15.8107 13.9364L20.9339 16.1326C21.1079 16.2065 21.2532 16.335 21.3479 16.4987C21.4426 16.6623 21.4815 16.8523 21.4589 17.04C21.2967 18.307 20.6784 19.4714 19.7196 20.3154C18.7608 21.1593 17.5273 21.6249 16.25 21.625C12.3049 21.625 8.52139 20.0578 5.73179 17.2682C2.94218 14.4786 1.375 10.6951 1.375 6.75C1.37512 5.47279 1.84074 4.23941 2.68471 3.28077C3.52867 2.32213 4.6931 1.70396 5.96 1.542"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                +876 123-4567
            </a>
        </nav>
    </div>
</div>

<!-- Mobile Navigation -->
<div class="mobile-nav d-lg-none">
    <button class="mobile-nav__toggle">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="mobile-nav__content">
        <div class="mobile-nav__header">
            <div class="logo">
                <a href="/">
                    <img src="/images/logo.png" alt="AgriKonnect">
                </a>
            </div>
            <button class="mobile-nav__close">&times;</button>
        </div>

        <ul class="mobile-nav__list">
            <?php foreach ($mainNav as $label => $item): ?>
                <li class="<?php echo ($currentPath === $item['url']) ? 'active' : ''; ?>">
                    <?php if ($item['submenu']): ?>
                        <span class="mobile-nav__toggle-btn">
                            <?php echo Functions::h($label); ?>
                            <svg class="icon" width="12" height="12" viewBox="0 0 12 12">
                                <path d="M2.5 4.5L6 8L9.5 4.5" stroke="currentColor"
                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <ul class="mobile-nav__submenu">
                            <?php foreach ($item['submenu'] as $subLabel => $subUrl): ?>
                                <li>
                                    <a href="<?php echo Functions::h($subUrl); ?>">
                                        <?php echo Functions::h($subLabel); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <a href="<?php echo Functions::h($item['url']); ?>">
                            <?php echo Functions::h($label); ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="mobile-nav__contact">
            <a href="tel:+639123456789" class="mobile-nav__phone">
                <svg width="20" height="20" viewBox="0 0 20 20">
                    <!-- Phone icon SVG path here -->
                </svg>
                +63 912 345 6789
            </a>
        </div>
    </div>
</div>

<!-- Required CSS -->
<style>
    .main-nav {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .main-nav>li {
        position: relative;
        margin-right: 2rem;
    }

    .main-nav>li>a {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        color: #1A1A1A;
        text-decoration: none;
        transition: color 0.3s;
    }

    .main-nav>li>a:hover {
        color: #00B307;
    }

    .submenu {
        position: absolute;
        top: 100%;
        left: 0;
        min-width: 200px;
        background: #fff;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        border-radius: 4px;
        padding: 0.5rem 0;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: all 0.3s;
        z-index: 100;
    }

    .main-nav>li:hover .submenu {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .submenu li a {
        display: block;
        padding: 0.5rem 1rem;
        color: #1A1A1A;
        text-decoration: none;
        transition: color 0.3s;
    }

    .submenu li a:hover {
        color: #00B307;
        background: #f8f9fa;
    }

    /* Mobile Navigation Styles */
    @media (max-width: 991px) {
        .mobile-nav__content {
            position: fixed;
            top: 0;
            left: -280px;
            width: 280px;
            height: 100vh;
            background: #fff;
            z-index: 999;
            overflow-y: auto;
            transition: left 0.3s;
        }

        .mobile-nav.active .mobile-nav__content {
            left: 0;
        }

        .mobile-nav__list {
            padding: 1rem;
        }

        .mobile-nav__submenu {
            display: none;
            padding-left: 1rem;
        }

        .mobile-nav__toggle-btn.active+.mobile-nav__submenu {
            display: block;
        }
    }
</style>

<!-- Required JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile Navigation Toggle
        const mobileNavToggle = document.querySelector('.mobile-nav__toggle');
        const mobileNav = document.querySelector('.mobile-nav');
        const mobileNavClose = document.querySelector('.mobile-nav__close');

        if (mobileNavToggle && mobileNav) {
            mobileNavToggle.addEventListener('click', () => {
                mobileNav.classList.add('active');
            });

            mobileNavClose.addEventListener('click', () => {
                mobileNav.classList.remove('active');
            });
        }

        // Submenu Toggles
        const submenuToggles = document.querySelectorAll('.mobile-nav__toggle-btn');
        submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', () => {
                toggle.classList.toggle('active');
            });
        });
    });
</script>