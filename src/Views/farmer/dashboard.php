<?php
$title = "Farmer Dashboard | AgriFarm";
$currentPage = 'dashboard';
?>

<!-- Main Dashboard Content -->
<div class="dashboard__main-content">
    <!-- Farmer Profile Summary -->
    <div class="row">
        <div class="col-lg-7">
            <div class="dashboard__user-profile dashboard-card">
                <div class="dashboard__user-profile-img">
                    <img src="/images/farmer-profile.jpg" alt="Farmer Profile" />
                </div>
                <div class="dashboard__user-profile-info">
                    <h5 class="font-body--xxl-500 name"><?php echo htmlspecialchars($farmer['name']); ?></h5>
                    <p class="font-body--md-400 designation"><?php echo htmlspecialchars($farmer['farm_name']); ?></p>
                    <a href="/farmer/account-settings" class="edit font-body--lg-500">Edit Profile</a>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="dashboard__stats dashboard-card">
                <h2 class="dashboard__stats-title font-body--md-500">Farm Overview</h2>
                <ul class="dashboard__stats-list">
                    <li>
                        <span>Active Crops:</span>
                        <strong><?php echo htmlspecialchars($stats['activeCrops']); ?></strong>
                    </li>
                    <li>
                        <span>Recent Harvest:</span>
                        <strong><?php echo htmlspecialchars($stats['recentHarvest']); ?></strong>
                    </li>
                    <li>
                        <span>Chemicals Used:</span>
                        <strong><?php echo htmlspecialchars($stats['chemicalsUsed']); ?> Records</strong>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="dashboard__recent-activities mt-4">
        <div class="dashboard__recent-activities-title">
            <h2 class="font-body--xxl-500">Recent Activities</h2>
            <a href="/farmer/record-activity" class="font-body--lg-500">View All</a>
        </div>
        <div class="dashboard__recent-activities-table dashboard-card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Activity</th>
                            <th scope="col">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activities as $activity): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($activity['date']))); ?></td>
                                <td><?php echo htmlspecialchars($activity['activity']); ?></td>
                                <td><?php echo htmlspecialchars($activity['details']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Flash Messages -->
<?php if (isset($_SESSION['flash'])): ?>
    <div class="alert alert-<?php echo $_SESSION['flash']['type']; ?> alert-dismissible fade show" role="alert">
        <?php echo $_SESSION['flash']['message']; ?>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>