<?php

use App\Utils\DataHelper as DH;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Details - AgriKonnect Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-50">
    <?php include APP_ROOT . '/src/Views/admin/partials/sidebar.php'; ?>

    <div class="p-4 sm:ml-64">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Farmer Details</h1>
                    <p class="text-sm text-gray-600">View detailed information about <?= htmlspecialchars($farmer['name']) ?></p>
                </div>
                <a href="/admin/farmers" class="text-gray-600 hover:text-gray-900">
                    &larr; Back to Farmers List
                </a>
            </div>
        </div>

        <!-- Farmer Information -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- Personal Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Personal Information</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="text-base text-gray-900"><?= htmlspecialchars($farmer['name']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Email</dt>
                        <dd class="text-base text-gray-900"><?= htmlspecialchars($farmer['email']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Phone</dt>
                        <dd class="text-base text-gray-900"><?= htmlspecialchars($farmer['phone_number']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd>
                            <span class="px-2.5 py-0.5 text-xs font-medium rounded-full <?= DH::getFarmerStatusClass($farmer['status']) ?>">
                                <?= ucfirst(htmlspecialchars($farmer['status'])) ?>
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Farm Information -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Farm Information</h2>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Farm Name</dt>
                        <dd class="text-base text-gray-900"><?= htmlspecialchars($farmer['farm_name']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Location</dt>
                        <dd class="text-base text-gray-900"><?= htmlspecialchars($farmer['location']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Farm Type</dt>
                        <dd class="text-base text-gray-900"><?= ucfirst(htmlspecialchars($farmer['farm_type'])) ?></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Farm Size</dt>
                        <dd class="text-base text-gray-900"><?= htmlspecialchars($farmer['farm_size']) ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Status History -->
        <?php if (!empty($statusHistory)): ?>
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Status History</h2>
                <div class="flow-root">
                    <ul role="list" class="divide-y divide-gray-200">
                        <?php foreach ($statusHistory as $change): ?>
                            <li class="py-4">
                                <div class="flex space-x-3">
                                    <div class="flex-1 space-y-1">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium text-gray-900">
                                                Status changed to: <span class="px-2.5 py-0.5 text-xs font-medium rounded-full <?= DH::getFarmerStatusClass($change['status']) ?>">
                                                    <?= ucfirst(htmlspecialchars($change['status'])) ?>
                                                </span>
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                <?= DH::formatDate($change['created_at']) ?>
                                            </p>
                                        </div>
                                        <?php if (!empty($change['reason'])): ?>
                                            <p class="text-sm text-gray-600">Reason: <?= htmlspecialchars($change['reason']) ?></p>
                                        <?php endif; ?>
                                        <p class="text-sm text-gray-500">Changed by: <?= htmlspecialchars($change['changed_by_name']) ?></p>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="flex space-x-4">
            <?php if ($farmer['status'] === 'pending'): ?>
                <form action="/admin/farmers/approve" method="POST" class="inline">
                    <input type="hidden" name="farmer_id" value="<?= $farmer['farmer_id'] ?>">
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                        Approve Farmer
                    </button>
                </form>
                <button onclick="showRejectModal(<?= $farmer['farmer_id'] ?>)" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Reject Application
                </button>
            <?php elseif ($farmer['status'] === 'active'): ?>
                <button onclick="showSuspendModal(<?= $farmer['farmer_id'] ?>)" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">
                    Suspend Account
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Include your modals here -->
    <?php include APP_ROOT . '/src/Views/admin/partials/farmer-modals.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
</body>

</html>