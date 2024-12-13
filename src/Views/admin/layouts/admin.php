<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Admin Panel' ?> - AgriKonnect</title>

    <!-- Styles -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <?php if (isset($extraStyles)): ?>
        <?php foreach ($extraStyles as $style): ?>
            <link href="<?= $style ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="bg-gray-50">
    <?php if (isset($_SESSION['admin_id'])): ?>
        <!-- Admin Navigation -->
        <nav class="fixed top-0 z-50 w-full bg-white border-b border-gray-200">
            <div class="px-3 py-3 lg:px-5 lg:pl-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center justify-start">
                        <button id="sidebar-toggle" aria-expanded="true" aria-controls="sidebar" class="p-2 text-gray-600 rounded cursor-pointer lg:hidden hover:text-gray-900 hover:bg-gray-100">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"></path>
                            </svg>
                        </button>
                        <a href="/admin" class="flex ml-2 md:mr-24">
                            <img src="/assets/images/logo.png" class="h-8 mr-3" alt="AgriKonnect Logo">
                            <span class="self-center text-xl font-semibold sm:text-2xl whitespace-nowrap">Admin Panel</span>
                        </a>
                    </div>
                    <div class="flex items-center">
                        <div class="flex items-center ml-3">
                            <div class="relative">
                                <button type="button" class="flex text-sm bg-gray-800 rounded-full focus:ring-4 focus:ring-gray-300" id="user-menu-button" aria-expanded="false">
                                    <span class="sr-only">Open user menu</span>
                                    <img class="w-8 h-8 rounded-full" src="/assets/images/admin-avatar.png" alt="admin photo">
                                </button>
                                <!-- Dropdown menu -->
                                <div class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded shadow" id="user-dropdown">
                                    <div class="px-4 py-3">
                                        <p class="text-sm text-gray-900">
                                            <?= h($_SESSION['admin_name'] ?? 'Administrator') ?>
                                        </p>
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            <?= h($_SESSION['admin_email'] ?? '') ?>
                                        </p>
                                    </div>
                                    <ul class="py-1">
                                        <li>
                                            <a href="/admin/profile" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                                        </li>
                                        <li>
                                            <a href="/admin/settings" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                                        </li>
                                        <li>
                                            <form action="/admin/logout" method="POST" class="block">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sign out</button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen pt-20 transition-transform -translate-x-full bg-white border-r border-gray-200 sm:translate-x-0">
            <div class="h-full px-3 pb-4 overflow-y-auto bg-white">
                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="/admin/dashboard" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 <?= getActiveClass('/admin/dashboard') ?>">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 10a8 8 0 018-8v8h8a8 8 0 11-16 0z"></path>
                                <path d="M12 2.252A8.014 8.014 0 0117.748 8H12V2.252z"></path>
                            </svg>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/farmers" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 <?= getActiveClass('/admin/farmers') ?>">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                            </svg>
                            <span class="ml-3">Farmers</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/products" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 <?= getActiveClass('/admin/products') ?>">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z"></path>
                                <path fill-rule="evenodd" d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-3">Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/orders" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 <?= getActiveClass('/admin/orders') ?>">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z"></path>
                            </svg>
                            <span class="ml-3">Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/system-health" class="flex items-center p-2 text-gray-900 rounded-lg hover:bg-gray-100 <?= getActiveClass('/admin/system-health') ?>">
                            <svg class="w-6 h-6 text-gray-500 transition duration-75" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="ml-3">System Health</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="p-4 sm:ml-64 pt-20">
            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="mb-4 p-4 rounded-lg <?= getAlertClass($_SESSION['flash_message']['type']) ?>">
                    <div class="flex items-center">
                        <?php if ($_SESSION['flash_message']['type'] === 'error'): ?>
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        <?php endif; ?>
                        <?= h($_SESSION['flash_message']['message']) ?>
                    </div>
                </div>
                <?php unset($_SESSION['flash_message']); ?>
            <?php endif; ?>

            <!-- Page Content -->
            <?= $content ?? '' ?>
        </div>
    <?php else: ?>
        <!-- Login Content -->
        <?= $content ?? '' ?>
    <?php endif; ?>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebar-toggle')?.addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });

        // User dropdown functionality
        const userMenuButton = document.getElementById('user-menu-button');
        const userDropdown = document.getElementById('user-dropdown');

        userMenuButton?.addEventListener('click', function() {
            userDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userMenuButton?.contains(event.target) && !userDropdown?.contains(event.target)) {
                userDropdown?.classList.add('hidden');
            }
        });
    </script>

    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $script): ?>
            <script src="<?= $script ?>" defer></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>

</html>