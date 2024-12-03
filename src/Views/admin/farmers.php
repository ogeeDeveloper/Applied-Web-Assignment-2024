<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Farmers - AgriKonnect Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-50">
    <?php include APP_ROOT . '/src/Views/admin/partials/sidebar.php'; ?>
    <div class="p-4 sm:ml-64">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Manage Farmers</h1>
                <p class="text-sm text-gray-600">View and manage farmer accounts</p>
            </div>
            <div class="flex gap-2">
                <div class="relative">
                    <input type="text" id="search" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-full p-2.5" placeholder="Search farmers...">
                </div>
                <select id="status-filter" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-green-500 focus:border-green-500 block w-40 p-2.5">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Total Farmers</h3>
                <p class="text-2xl font-semibold text-gray-900"><?= $stats['total'] ?? 0 ?></p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Active Farmers</h3>
                <p class="text-2xl font-semibold text-green-600"><?= $stats['active'] ?? 0 ?></p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Pending Approval</h3>
                <p class="text-2xl font-semibold text-yellow-600"><?= $stats['pending'] ?? 0 ?></p>
            </div>
            <div class="bg-white rounded-lg p-4 shadow-sm">
                <h3 class="text-sm font-medium text-gray-500">Suspended</h3>
                <p class="text-2xl font-semibold text-red-600"><?= $stats['suspended'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Farmers Table -->
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3">Farmer Name</th>
                        <th scope="col" class="px-6 py-3">Farm Details</th>
                        <th scope="col" class="px-6 py-3">Contact</th>
                        <th scope="col" class="px-6 py-3">Status</th>
                        <th scope="col" class="px-6 py-3">Products</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($farmers as $farmer): ?>
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                <?= htmlspecialchars($farmer['name']) ?>
                            </td>
                            <td class="px-6 py-4">
                                <p class="font-medium"><?= htmlspecialchars($farmer['farm_name']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($farmer['location']) ?></p>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars($farmer['farm_type']) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <p><?= htmlspecialchars($farmer['email']) ?></p>
                                <p><?= htmlspecialchars($farmer['phone_number']) ?></p>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-0.5 text-xs font-medium rounded-full
                                <?php
                                switch ($farmer['status']) {
                                    case 'active':
                                        echo 'bg-green-100 text-green-800';
                                        break;
                                    case 'pending':
                                        echo 'bg-yellow-100 text-yellow-800';
                                        break;
                                    case 'suspended':
                                        echo 'bg-red-100 text-red-800';
                                        break;
                                    default:
                                        echo 'bg-gray-100 text-gray-800';
                                }
                                ?>">
                                    <?= ucfirst(htmlspecialchars($farmer['status'])) ?>
                                </span>
                                <?php if ($farmer['status'] === 'suspended'): ?>
                                    <p class="text-xs text-gray-500 mt-1">Until: <?= htmlspecialchars($farmer['suspension_end_date'] ?? 'Indefinite') ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?= $farmer['product_count'] ?> products
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="/admin/farmers/view?id=<?= $farmer['id'] ?>" class="btn btn-primary">View Details</a>
                                    <?php if ($farmer['status'] === 'pending'): ?>
                                        <button onclick="approveFarmer(<?= $farmer['id'] ?>)" class="font-medium text-green-600 hover:text-green-800">Approve</button>
                                    <?php elseif ($farmer['status'] === 'active'): ?>
                                        <button onclick="showSuspendModal(<?= $farmer['id'] ?>)" class="font-medium text-red-600 hover:text-red-800">Suspend</button>
                                    <?php elseif ($farmer['status'] === 'suspended'): ?>
                                        <button onclick="unsuspendFarmer(<?= $farmer['id'] ?>)" class="font-medium text-green-600 hover:text-green-800">Unsuspend</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination section remains the same -->
    </div>

    <!-- View Farmer Details Modal -->
    <div id="viewFarmerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border max-w-4xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Farmer Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-4">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <button onclick="switchTab('profile')" class="tab-button text-green-600 border-green-600 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Profile</button>
                        <button onclick="switchTab('products')" class="tab-button text-gray-500 border-transparent whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Products</button>
                        <button onclick="switchTab('orders')" class="tab-button text-gray-500 border-transparent whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Orders</button>
                        <button onclick="switchTab('activity')" class="tab-button text-gray-500 border-transparent whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">Activity Log</button>
                    </nav>
                </div>
                <div id="profile-tab" class="tab-content mt-4"></div>
                <div id="products-tab" class="tab-content hidden mt-4"></div>
                <div id="orders-tab" class="tab-content hidden mt-4"></div>
                <div id="activity-tab" class="tab-content hidden mt-4"></div>
            </div>
        </div>
    </div>

    <!-- Enhanced Suspend Modal -->
    <div id="suspendModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Suspend Farmer Account</h3>
                <form id="suspendForm" class="mt-4">
                    <input type="hidden" id="suspend_farmer_id" name="farmer_id">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Suspension Duration</label>
                        <select name="duration" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30">30 days</option>
                            <option value="permanent">Permanent</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Reason for Suspension</label>
                        <textarea name="reason" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" required></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" onclick="closeSuspendModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">Suspend Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        // Enhanced farmer management functionality
        async function approveFarmer(farmerId) {
            if (!confirm('Are you sure you want to approve this farmer?')) return;

            try {
                const response = await fetch('/admin/farmers/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        farmer_id: farmerId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to approve farmer: ' + data.message);
                }
            } catch (error) {
                alert('An error occurred while approving the farmer');
            }
        }

        async function unsuspendFarmer(farmerId) {
            if (!confirm('Are you sure you want to unsuspend this farmer?')) return;

            try {
                const response = await fetch('/admin/farmers/unsuspend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        farmer_id: farmerId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to unsuspend farmer: ' + data.message);
                }
            } catch (error) {
                alert('An error occurred while unsuspending the farmer');
            }
        }

        // Enhanced modal management
        let currentFarmerId = null;

        async function showViewModal(farmerId) {
            currentFarmerId = farmerId; // Set the current farmer ID first
            try {
                const response = await fetch(`/admin/farmers/view?id=${farmerId}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || 'Failed to load farmer details');
                }

                // Show the modal first
                document.getElementById('viewFarmerModal').classList.remove('hidden');

                // Then load the profile tab
                await switchTab('profile');
            } catch (error) {
                console.error('Error loading farmer details:', error);
                alert('Failed to load farmer details. Please try again.');
            }
        }

        async function loadFarmerDetails(farmerId) {
            try {
                const response = await fetch(`/admin/api/farmers/${farmerId}`);
                const data = await response.json();
                if (data.success) {
                    populateProfileTab(data.farmer);
                }
            } catch (error) {
                console.error('Error loading farmer details:', error);
                alert('Failed to load farmer details');
            }
        }

        async function switchTab(tabName) {
            if (!currentFarmerId) return;

            // Update active tab styling
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('text-green-600', 'border-green-600');
                button.classList.add('text-gray-500', 'border-transparent');
            });
            event.target.classList.add('text-green-600', 'border-green-600');

            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected tab content
            const tabContent = document.getElementById(`${tabName}-tab`);
            tabContent.classList.remove('hidden');

            try {
                // Show loading state
                tabContent.innerHTML = '<div class="text-center py-4">Loading...</div>';

                const response = await fetch(`/admin/api/farmers/${currentFarmerId}/${tabName}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message || `Failed to load ${tabName} data`);
                }

                // Update tab content based on the tab
                switch (tabName) {
                    case 'profile':
                        populateProfileTab(data.profile);
                        break;
                    case 'products':
                        populateProductsTab(data.products);
                        break;
                    case 'orders':
                        populateOrdersTab(data.orders);
                        break;
                    case 'activity':
                        populateActivityTab(data.activities);
                        break;
                }
            } catch (error) {
                console.error(`Error loading ${tabName} data:`, error);
                tabContent.innerHTML = `
            <div class="text-center text-red-600 py-4">
                Failed to load ${tabName} data. Please try again.
            </div>
        `;
            }
        }

        async function loadFarmerProfile() {
            try {
                const response = await fetch(`/admin/api/farmers/${currentFarmerId}/profile`);
                const data = await response.json();
                if (data.success) {
                    document.getElementById('profile-tab').innerHTML = `
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-900 mb-4">Personal Information</h4>
                                <div class="space-y-3">
                                    <p><span class="text-gray-500">Name:</span> ${data.profile.name}</p>
                                    <p><span class="text-gray-500">Email:</span> ${data.profile.email}</p>
                                    <p><span class="text-gray-500">Phone:</span> ${data.profile.phone_number}</p>
                                    <p><span class="text-gray-500">Joined:</span> ${data.profile.created_at}</p>
                                </div>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900 mb-4">Farm Information</h4>
                                <div class="space-y-3">
                                    <p><span class="text-gray-500">Farm Name:</span> ${data.profile.farm_name}</p>
                                    <p><span class="text-gray-500">Location:</span> ${data.profile.location}</p>
                                    <p><span class="text-gray-500">Farm Type:</span> ${data.profile.farm_type}</p>
                                    <p><span class="text-gray-500">Size:</span> ${data.profile.farm_size}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading farmer profile:', error);
            }
        }

        async function loadFarmerProducts() {
            try {
                const response = await fetch(`/admin/api/farmers/${currentFarmerId}/products`);
                const data = await response.json();
                if (data.success) {
                    const productsList = data.products.map(product => `
                        <tr class="border-b">
                            <td class="px-4 py-3">${product.name}</td>
                            <td class="px-4 py-3">${product.category}</td>
                            <td class="px-4 py-3">$${product.price}</td>
                            <td class="px-4 py-3">${product.stock}</td>
                            <td class="px-4 py-3">${product.status}</td>
                        </tr>
                    `).join('');

                    document.getElementById('products-tab').innerHTML = `
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Product</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Category</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Price</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Stock</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    ${productsList}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading farmer products:', error);
            }
        }

        async function loadFarmerOrders() {
            try {
                const response = await fetch(`/admin/api/farmers/${currentFarmerId}/orders`);
                const data = await response.json();
                if (data.success) {
                    const ordersList = data.orders.map(order => `
                        <tr class="border-b">
                            <td class="px-4 py-3">#${order.order_id}</td>
                            <td class="px-4 py-3">${order.customer_name}</td>
                            <td class="px-4 py-3">$${order.total}</td>
                            <td class="px-4 py-3">${order.status}</td>
                            <td class="px-4 py-3">${order.date}</td>
                        </tr>
                    `).join('');

                    document.getElementById('orders-tab').innerHTML = `
                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Order ID</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Customer</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Total</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Status</th>
                                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-500">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="text-sm">
                                    ${ordersList}
                                </tbody>
                            </table>
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading farmer orders:', error);
            }
        }

        async function loadFarmerActivity() {
            try {
                const response = await fetch(`/admin/api/farmers/${currentFarmerId}/activity`);
                const data = await response.json();
                if (data.success) {
                    const activityList = data.activities.map(activity => `
                        <div class="border-b py-3">
                            <p class="text-sm text-gray-900">${activity.description}</p>
                            <p class="text-xs text-gray-500">${activity.date}</p>
                        </div>
                    `).join('');

                    document.getElementById('activity-tab').innerHTML = `
                        <div class="space-y-2">
                            ${activityList}
                        </div>
                    `;
                }
            } catch (error) {
                console.error('Error loading farmer activity:', error);
            }
        }

        // Form handlers
        document.getElementById('suspendForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);

            try {
                const response = await fetch('/admin/farmers/suspend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        farmer_id: formData.get('farmer_id'),
                        duration: formData.get('duration'),
                        reason: formData.get('reason')
                    })
                });

                const data = await response.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to suspend farmer: ' + data.message);
                }
            } catch (error) {
                alert('An error occurred while suspending the farmer');
            }
        });

        // Search and filter functionality
        document.getElementById('search').addEventListener('input', filterTable);
        document.getElementById('status-filter').addEventListener('change', filterTable);

        function filterTable() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const status = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                const matchesSearch = text.includes(searchTerm);
                const matchesStatus = !statusFilter || status.includes(statusFilter);
                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        function closeViewModal() {
            document.getElementById('viewFarmerModal').classList.add('hidden');
            currentFarmerId = null;
        }
    </script>