<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AgriKonnect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-50">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0">
            <div class="h-full px-3 py-4 overflow-y-auto bg-gray-800">
                <a href="/admin/dashboard" class="flex items-center mb-5">
                    <img src="/assets/images/logo.png" class="h-8 mr-3" alt="AgriKonnect Logo" />
                    <span class="self-center text-xl font-semibold text-white">AgriKonnect</span>
                </a>
                <ul class="space-y-2 font-medium">
                    <li>
                        <a href="/admin/dashboard" class="flex items-center p-2 text-white rounded-lg bg-green-600">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                                <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z" />
                                <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z" />
                            </svg>
                            <span class="ml-3">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/farmers" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                                <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z" />
                            </svg>
                            <span class="ml-3">Manage Farmers</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/products" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 20">
                                <path d="M17 5.923A1 1 0 0 0 16 5h-3V4a4 4 0 1 0-8 0v1H2a1 1 0 0 0-1 .923L.086 17.846A2 2 0 0 0 2.08 20h13.84a2 2 0 0 0 1.994-2.153L17 5.923ZM7 4a2 2 0 1 1 4 0v1H7V4Zm-.5 5a1.5 1.5 0 1 1 3 0 1.5 1.5 0 0 1-3 0Z" />
                            </svg>
                            <span class="ml-3">Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/orders" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z" />
                            </svg>
                            <span class="ml-3">Orders</span>
                        </a>
                    </li>
                    <li>
                        <a href="/admin/system" class="flex items-center p-2 text-gray-300 rounded-lg hover:bg-gray-700">
                            <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M18 7.5h-.423l-.452-1.09.3-.3a1.5 1.5 0 0 0 0-2.121L16.01 2.575a1.5 1.5 0 0 0-2.121 0l-.3.3-1.09-.452V2A1.5 1.5 0 0 0 11 .5H9A1.5 1.5 0 0 0 7.5 2v.423l-1.09.452-.3-.3a1.5 1.5 0 0 0-2.121 0L2.575 3.99a1.5 1.5 0 0 0 0 2.121l.3.3L2.423 7.5H2A1.5 1.5 0 0 0 .5 9v2A1.5 1.5 0 0 0 2 12.5h.423l.452 1.09-.3.3a1.5 1.5 0 0 0 0 2.121l1.415 1.413a1.5 1.5 0 0 0 2.121 0l.3-.3 1.09.452V18A1.5 1.5 0 0 0 9 19.5h2a1.5 1.5 0 0 0 1.5-1.5v-.423l1.09-.452.3.3a1.5 1.5 0 0 0 2.121 0l1.415-1.414a1.5 1.5 0 0 0 0-2.121l-.3-.3.452-1.09H18a1.5 1.5 0 0 0 1.5-1.5V9A1.5 1.5 0 0 0 18 7.5Zm-8 6a3.5 3.5 0 1 1 0-7 3.5 3.5 0 0 1 0 7Z" />
                            </svg>
                            <span class="ml-3">System Health</span>
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="p-4 sm:ml-64">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard Overview</h1>
                <p class="text-sm text-gray-600">Welcome back, Administrator</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Users Stats -->
                <div class="p-4 bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500">Total Users</h3>
                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Active</span>
                    </div>
                    <div class="flex items-center mt-2">
                        <h2 class="text-3xl font-bold text-gray-900"><?= number_format($stats['users']['total']) ?></h2>
                        <span class="text-green-500 text-sm ml-2">+<?= $stats['users']['new'] ?> new</span>
                    </div>
                </div>

                <!-- Orders Stats -->
                <div class="p-4 bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500">Total Orders</h3>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Today</span>
                    </div>
                    <div class="flex items-center mt-2">
                        <h2 class="text-3xl font-bold text-gray-900"><?= number_format($stats['orders']['total']) ?></h2>
                        <span class="text-yellow-500 text-sm ml-2"><?= $stats['orders']['pending'] ?> pending</span>
                    </div>
                </div>

                <!-- Products Stats -->
                <div class="p-4 bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500">Products</h3>
                        <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Inventory</span>
                    </div>
                    <div class="flex items-center mt-2">
                        <h2 class="text-3xl font-bold text-gray-900"><?= number_format($stats['products']['total']) ?></h2>
                        <span class="text-red-500 text-sm ml-2"><?= $stats['products']['out_of_stock'] ?> out of stock</span>
                    </div>
                </div>

                <!-- Farmers Stats -->
                <div class="p-4 bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between">
                        <h3 class="text-gray-500">Farmers</h3>
                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">Verified</span>
                    </div>
                    <div class="flex items-center mt-2">
                        <h2 class="text-3xl font-bold text-gray-900"><?= number_format($stats['farmers']['total']) ?></h2>
                        <span class="text-orange-500 text-sm ml-2"><?= $stats['farmers']['pending_approval'] ?> pending</span>
                    </div>
                </div>
            </div>

            <!-- System Health -->
            <?php if (isset($systemHealth)): ?>
                <div class="p-4 bg-white rounded-lg shadow mb-6">
                    <h3 class="text-lg font-semibold mb-4">System Health Status</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <?php foreach ($systemHealth as $service => $status): ?>
                            <div class="flex items-center">
                                <div class="w-3 h-3 rounded-full <?= $status === 'healthy' ? 'bg-green-500' : 'bg-red-500' ?> mr-2"></div>
                                <span class="capitalize"><?= $service ?>: <?= ucfirst($status) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Activities -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Pending Approvals -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Pending Farmer Approvals</h3>
                    </div>
                    <div class="p-4">
                        <?php if (!empty($pendingFarmers)): ?>
                            <div class="flow-root">
                                <ul role="list" class="divide-y divide-gray-200">
                                    <?php foreach ($pendingFarmers as $farmer): ?>
                                        <li class="py-3 sm:py-4">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        <?= htmlspecialchars($farmer['name']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        <?= htmlspecialchars($farmer['farm_name']) ?>
                                                    </p>
                                                </div>
                                                <div class="inline-flex items-center">
                                                    <form action="/admin/farmers/approve" method="POST" class="inline">
                                                        <input type="hidden" name="farmer_id" value="<?= $farmer['id'] ?>">
                                                        <button type="submit" class="text-white bg-green-500 hover:bg-green-600 focus:ring-4 focus:ring-green-300 font-medium rounded-lg text-sm px-4 py-2 mr-2">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <button type="button" onclick="showRejectModal(<?= $farmer['id'] ?>)" class="text-white bg-red-500 hover:bg-red-600 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-4 py-2">
                                                        Reject
                                                    </button>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-4">No pending approvals</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <h3 class="text-lg font-semibold">Recent Orders</h3>
                    </div>
                    <div class="p-4">
                        <?php if (!empty($recentOrders)): ?>
                            <div class="flow-root">
                                <ul role="list" class="divide-y divide-gray-200">
                                    <?php foreach ($recentOrders as $order): ?>
                                        <li class="py-3 sm:py-4">
                                            <div class="flex items-center space-x-4">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        Order #<?= htmlspecialchars($order['id']) ?>
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        <?= htmlspecialchars($order['customer_name']) ?>
                                                    </p>
                                                </div>
                                                <div class="inline-flex items-center">
                                                    <span class="text-sm font-semibold text-gray-900">
                                                        ₱<?= number_format($order['total_amount'], 2) ?>
                                                    </span>
                                                    <span class="ml-2 px-2.5 py-0.5 text-xs font-medium rounded-full
                                                    <?php
                                                    switch ($order['status']) {
                                                        case 'pending':
                                                            echo 'bg-yellow-100 text-yellow-800';
                                                            break;
                                                        case 'processing':
                                                            echo 'bg-blue-100 text-blue-800';
                                                            break;
                                                        case 'completed':
                                                            echo 'bg-green-100 text-green-800';
                                                            break;
                                                        default:
                                                            echo 'bg-gray-100 text-gray-800';
                                                    }
                                                    ?>">
                                                        <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-gray-500 text-center py-4">No recent orders</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Farmer Modal -->
    <div id="rejectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Reject Farmer Application</h3>
                <div class="mt-2 px-7 py-3">
                    <form id="rejectForm" action="/admin/farmers/reject" method="POST">
                        <input type="hidden" id="reject_farmer_id" name="farmer_id">
                        <div class="mb-4">
                            <label for="reason" class="block text-sm font-medium text-gray-700 text-left mb-2">Reason for Rejection</label>
                            <textarea
                                id="reason"
                                name="reason"
                                rows="3"
                                class="shadow-sm focus:ring-green-500 focus:border-green-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                                required></textarea>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeRejectModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                                Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        function showRejectModal(farmerId) {
            document.getElementById('reject_farmer_id').value = farmerId;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
            document.getElementById('rejectForm').reset();
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target == modal) {
                closeRejectModal();
            }
        }
    </script>
</body>

</html>