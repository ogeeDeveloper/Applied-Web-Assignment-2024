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
                            </td>
                            <td class="px-6 py-4">
                                <?= $farmer['product_count'] ?> products
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-2">
                                    <a href="/admin/farmers/view?id=<?= $farmer['id'] ?>" class="font-medium text-blue-600 hover:text-blue-800">View Details</a>
                                    <?php if ($farmer['status'] === 'pending'): ?>
                                        <form action="/admin/farmers/approve" method="POST" class="inline">
                                            <input type="hidden" name="farmer_id" value="<?= $farmer['id'] ?>">
                                            <button type="submit" class="font-medium text-green-600 hover:text-green-800">Approve</button>
                                        </form>
                                    <?php elseif ($farmer['status'] === 'active'): ?>
                                        <button onclick="showSuspendModal(<?= $farmer['id'] ?>)" class="font-medium text-red-600 hover:text-red-800">Suspend</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6 mt-4 rounded-lg">
                <div class="flex flex-1 justify-between sm:hidden">
                    <?php if ($currentPage > 1): ?>
                        <a href="?page=<?= $currentPage - 1 ?>" class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Previous</a>
                    <?php endif; ?>
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?page=<?= $currentPage + 1 ?>" class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Next</a>
                    <?php endif; ?>
                </div>
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing <span class="font-medium"><?= $startRecord ?></span> to <span class="font-medium"><?= $endRecord ?></span> of <span class="font-medium"><?= $totalRecords ?></span> farmers
                        </p>
                    </div>
                    <div>
                        <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="?page=<?= $i ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?= $i === $currentPage ? 'bg-green-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                        </nav>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Suspend Farmer Modal -->
    <div id="suspendModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Suspend Farmer Account</h3>
                <div class="mt-2 px-7 py-3">
                    <form id="suspendForm" action="/admin/farmers/suspend" method="POST">
                        <input type="hidden" id="suspend_farmer_id" name="farmer_id">
                        <div class="mb-4">
                            <label for="suspension_reason" class="block text-sm font-medium text-gray-700 text-left mb-2">Reason for Suspension</label>
                            <textarea
                                id="suspension_reason"
                                name="reason"
                                rows="3"
                                class="shadow-sm focus:ring-green-500 focus:border-green-500 mt-1 block w-full sm:text-sm border border-gray-300 rounded-md"
                                required></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="suspension_duration" class="block text-sm font-medium text-gray-700 text-left mb-2">Suspension Duration</label>
                            <select
                                id="suspension_duration"
                                name="duration"
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md">
                                <option value="7">7 days</option>
                                <option value="14">14 days</option>
                                <option value="30">30 days</option>
                                <option value="permanent">Permanent</option>
                            </select>
                        </div>
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeSuspendModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                                Suspend
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Farmer Details Modal -->
    <div id="viewFarmerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border max-w-2xl shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-900">Farmer Details</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="farmerDetails" class="mt-4">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
    <script>
        // Search functionality
        document.getElementById('search').addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Status filter functionality
        document.getElementById('status-filter').addEventListener('change', function(e) {
            const status = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                if (!status) {
                    row.style.display = '';
                    return;
                }

                const statusCell = row.querySelector('td:nth-child(4)').textContent.toLowerCase();
                row.style.display = statusCell.includes(status) ? '' : 'none';
            });
        });

        // Modal functions
        function showSuspendModal(farmerId) {
            document.getElementById('suspend_farmer_id').value = farmerId;
            document.getElementById('suspendModal').classList.remove('hidden');
        }

        function closeSuspendModal() {
            document.getElementById('suspendModal').classList.add('hidden');
            document.getElementById('suspendForm').reset();
        }

        function showViewModal(farmerId) {
            // Fetch farmer details via AJAX and populate the modal
            fetch(`/admin/api/farmers/${farmerId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('farmerDetails').innerHTML = `
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <h4 class="font-medium text-gray-900">Personal Information</h4>
                                <p class="text-sm text-gray-600">${data.name}</p>
                                <p class="text-sm text-gray-600">${data.email}</p>
                                <p class="text-sm text-gray-600">${data.phone_number}</p>
                            </div>
                            <div>
                                <h4 class="font-medium text-gray-900">Farm Information</h4>
                                <p class="text-sm text-gray-600">${data.farm_name}</p>
                                <p class="text-sm text-gray-600">${data.location}</p>
                                <p class="text-sm text-gray-600">${data.farm_type}</p>
                            </div>
                        </div>
                    `;
                    document.getElementById('viewFarmerModal').classList.remove('hidden');
                });
        }

        function closeViewModal() {
            document.getElementById('viewFarmerModal').classList.add('hidden');
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            const suspendModal = document.getElementById('suspendModal');
            const viewModal = document.getElementById('viewFarmerModal');
            if (event.target == suspendModal) {
                closeSuspendModal();
            }
            if (event.target == viewModal) {
                closeViewModal();
            }
        }
    </script>
</body>

</html>