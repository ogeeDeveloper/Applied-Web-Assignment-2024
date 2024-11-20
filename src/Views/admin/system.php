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
    <title>System Health - AgriKonnect Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.css" rel="stylesheet" />
</head>

<body class="bg-gray-50">
    <div class="flex">
        <?php include APP_ROOT . '/src/Views/admin/partials/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="p-4 sm:ml-64">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900">System Health</h1>
                <p class="text-sm text-gray-600">Monitor system performance and health metrics</p>
            </div>

            <!-- System Status Overview -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php foreach ($healthMetrics as $metric => $status): ?>
                    <div class="p-4 bg-white rounded-lg shadow">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full <?= $status['status'] === 'healthy' ? 'bg-green-500' : 'bg-red-500' ?> mr-2"></div>
                            <h3 class="text-gray-500 capitalize"><?= htmlspecialchars($metric) ?></h3>
                        </div>
                        <div class="mt-2">
                            <p class="text-2xl font-bold text-gray-900">
                                <?= $status['uptime'] ?? '100%' ?>
                            </p>
                            <p class="text-sm text-gray-600">
                                <?= htmlspecialchars($status['message'] ?? 'No issues detected') ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Resource Usage -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <!-- Memory Usage -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-lg font-semibold mb-4">Memory Usage</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-blue-600 bg-blue-200">
                                    <?= DH::formatNumber($memoryUsage['percentage'], 1) ?>% Used
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-blue-600">
                                    <?= DH::formatSize($memoryUsage['used']) ?> / <?= DH::formatSize($memoryUsage['total']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-blue-200">
                            <div style="width:<?= $memoryUsage['percentage'] ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-blue-500"></div>
                        </div>
                    </div>
                </div>

                <!-- Storage Usage -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-lg font-semibold mb-4">Storage Usage</h3>
                    <div class="relative pt-1">
                        <div class="flex mb-2 items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold inline-block py-1 px-2 uppercase rounded-full text-green-600 bg-green-200">
                                    <?= DH::formatNumber($storageUsage['percentage'], 1) ?>% Used
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-xs font-semibold inline-block text-green-600">
                                    <?= DH::formatSize($storageUsage['used']) ?> / <?= DH::formatSize($storageUsage['total']) ?>
                                </span>
                            </div>
                        </div>
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-green-200">
                            <div style="width:<?= $storageUsage['percentage'] ?>%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent System Logs -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-4 border-b">
                    <h3 class="text-lg font-semibold">Recent System Logs</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Context</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($systemLogs as $log): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?= htmlspecialchars(DH::get($log, 'timestamp')) ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?= DH::getLogLevelClass(DH::get($log, 'level')) ?>">
                                                <?= htmlspecialchars(DH::get($log, 'level')) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?= htmlspecialchars(DH::get($log, 'message')) ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            <pre class="text-xs"><?= htmlspecialchars(json_encode(DH::get($log, 'context', []), JSON_PRETTY_PRINT)) ?></pre>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.2.0/flowbite.min.js"></script>
</body>

</html>