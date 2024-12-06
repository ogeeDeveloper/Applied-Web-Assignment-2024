<?php
$title = "Farmer Dashboard | AgriFarm";
?>

<div class="dashboard section">
    <div class="container">
        <div class="row dashboard__content">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3">
                <nav class="dashboard__nav">
                    <h5 class="dashboard__nav-title font-body--xxl-500">Navigation</h5>
                    <ul class="dashboard__nav-item">
                        <li class="dashboard__nav-item-link active">
                            <a href="farmer-dashboard.php" class="font-body--lg-400">
                                <span class="icon">📊</span>
                                <span class="name">Dashboard</span>
                            </a>
                        </li>
                        <li class="dashboard__nav-item-link">
                            <a href="manage-crops" class="font-body--lg-400">
                                <span class="icon">🌱</span>
                                <span class="name">Manage Crops</span>
                            </a>
                        </li>
                        <li class="dashboard__nav-item-link">
                            <a href="/farmer/chemical-usage" class="font-body--lg-400">
                                <span class="icon">🧪</span>
                                <span class="name">Chemical Usage</span>
                            </a>
                        </li>
                        <li class="dashboard__nav-item-link">
                            <a href="/farmer/record-activity" class="font-body--lg-400">
                                <span class="icon">✍️</span>
                                <span class="name">Record Activity</span>
                            </a>
                        </li>
                        <li class="dashboard__nav-item-link">
                            <a href="/farmer/account-settings" class="font-body--lg-400">
                                <span class="icon">⚙️</span>
                                <span class="name">Account Settings</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Main Dashboard Content -->
            <div class="col-lg-9 section--xl pt-0">
                <div class="container">
                    <!-- Farmer Profile Summary -->
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="dashboard__user-profile dashboard-card">
                                <div class="dashboard__user-profile-img">
                                    <img src="images/farmer-profile.jpg" alt="Farmer Profile" />
                                </div>
                                <div class="dashboard__user-profile-info">
                                    <h5 class="font-body--xxl-500 name">John Doe</h5>
                                    <p class="font-body--md-400 designation">Farmer</p>
                                    <a href="account-settings.php" class="edit font-body--lg-500">Edit Profile</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="dashboard__stats dashboard-card">
                                <h2 class="dashboard__stats-title font-body--md-500">Farm Overview</h2>
                                <ul class="dashboard__stats-list">
                                    <li>
                                        <span>Active Crops:</span>
                                        <strong>12</strong>
                                    </li>
                                    <li>
                                        <span>Recent Harvest:</span>
                                        <strong>3 Tons</strong>
                                    </li>
                                    <li>
                                        <span>Chemicals Used:</span>
                                        <strong>5 Records</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="dashboard__recent-activities" style="margin-top: 24px">
                        <div class="dashboard__recent-activities-title">
                            <h2 class="font-body--xxl-500">Recent Activities</h2>
                            <a href="activity-log.php" class="font-body--lg-500">View All</a>
                        </div>
                        <div class="dashboard__recent-activities-table">
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
                                        <tr>
                                            <td>2024-12-01</td>
                                            <td>Harvest Recorded</td>
                                            <td>5 Tons of Wheat</td>
                                        </tr>
                                        <tr>
                                            <td>2024-11-28</td>
                                            <td>Chemical Applied</td>
                                            <td>Pesticides on Corn Field</td>
                                        </tr>
                                        <tr>
                                            <td>2024-11-25</td>
                                            <td>New Crop Planted</td>
                                            <td>Tomatoes in Greenhouse</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>