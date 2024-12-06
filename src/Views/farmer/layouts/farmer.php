<?php
// Header content here
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Farmer Dashboard'; ?></title>

    <!-- CSS Files -->
    <link rel="stylesheet" href="/lib/css/bootstrap.min.css">
    <link rel="stylesheet" href="/lib/css/venobox.css">
    <link rel="stylesheet" href="/lib/css/noulslider.min.css">
    <link rel="stylesheet" href="/css/style.css">
</head>

<body>
    <div class="dashboard section">
        <div class="container">
            <div class="row dashboard__content">
                <!-- Sidebar Navigation -->
                <div class="col-lg-3">
                    <nav class="dashboard__nav">
                        <h5 class="dashboard__nav-title font-body--xxl-500">Navigation</h5>
                        <ul class="dashboard__nav-item">
                            <li class="dashboard__nav-item-link <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                                <a href="/farmer/dashboard" class="font-body--lg-400">
                                    <span class="icon">📊</span>
                                    <span class="name">Dashboard</span>
                                </a>
                            </li>
                            <li class="dashboard__nav-item-link <?php echo $currentPage === 'manage-crops' ? 'active' : ''; ?>">
                                <a href="/farmer/manage-crops" class="font-body--lg-400">
                                    <span class="icon">🌱</span>
                                    <span class="name">Manage Crops</span>
                                </a>
                            </li>
                            <li class="dashboard__nav-item-link <?php echo $currentPage === 'chemical-usage' ? 'active' : ''; ?>">
                                <a href="/farmer/chemical-usage" class="font-body--lg-400">
                                    <span class="icon">🧪</span>
                                    <span class="name">Chemical Usage</span>
                                </a>
                            </li>
                            <li class="dashboard__nav-item-link <?php echo $currentPage === 'record-activity' ? 'active' : ''; ?>">
                                <a href="/farmer/record-activity" class="font-body--lg-400">
                                    <span class="icon">✍️</span>
                                    <span class="name">Record Activity</span>
                                </a>
                            </li>
                            <li class="dashboard__nav-item-link <?php echo $currentPage === 'account-settings' ? 'active' : ''; ?>">
                                <a href="/farmer/account-settings" class="font-body--lg-400">
                                    <span class="icon">⚙️</span>
                                    <span class="name">Account Settings</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9 section--xl pt-0">
                    <div class="container">
                        <?php echo $content; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Files -->
    <script src="/lib/js/jquery.min.js"></script>
    <script src="/lib/js/bootstrap.bundle.min.js"></script>
    <script src="/lib/js/venobox.min.js"></script>
    <script src="/lib/js/noulslider.min.js"></script>
    <script src="/js/main.js"></script>
</body>

</html>
<?php
// Footer content here
?>