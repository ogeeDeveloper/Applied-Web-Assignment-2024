<?php
$title = "Account Settings | AgriFarm";
?>

<div class="account-settings section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="section-title">Account Settings</h1>

                <!-- Profile Information -->
                <div class="profile-section">
                    <h2>Profile Information</h2>
                    <form method="POST" action="/farmer/update-profile" class="settings-form">
                        <div class="form-group">
                            <label for="farmName">Farm Name</label>
                            <input type="text" id="farmName" name="farm_name" class="form-control"
                                value="<?php echo htmlspecialchars($farmer['farm_name']); ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location">Location</label>
                                    <input type="text" id="location" name="location" class="form-control"
                                        value="<?php echo htmlspecialchars($farmer['location']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phoneNumber">Phone Number</label>
                                    <input type="tel" id="phoneNumber" name="phone_number" class="form-control"
                                        value="<?php echo htmlspecialchars($farmer['phone_number']); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="farmType">Farm Type</label>
                                    <select id="farmType" name="farm_type" class="form-control" required>
                                        <option value="vegetables" <?php echo $farmer['farm_type'] === 'vegetables' ? 'selected' : ''; ?>>Vegetables</option>
                                        <option value="fruits" <?php echo $farmer['farm_type'] === 'fruits' ? 'selected' : ''; ?>>Fruits</option>
                                        <option value="grains" <?php echo $farmer['farm_type'] === 'grains' ? 'selected' : ''; ?>>Grains</option>
                                        <option value="mixed" <?php echo $farmer['farm_type'] === 'mixed' ? 'selected' : ''; ?>>Mixed</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="farmSize">Farm Size</label>
                                    <input type="text" id="farmSize" name="farm_size" class="form-control"
                                        value="<?php echo htmlspecialchars($farmer['farm_size']); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="farmingExperience">Farming Experience (Years)</label>
                                    <input type="number" id="farmingExperience" name="farming_experience" class="form-control"
                                        value="<?php echo htmlspecialchars($farmer['farming_experience']); ?>" required min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="primaryProducts">Primary Products</label>
                                    <input type="text" id="primaryProducts" name="primary_products" class="form-control"
                                        value="<?php echo htmlspecialchars($farmer['primary_products']); ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input type="checkbox" id="organicCertified" name="organic_certified" class="form-check-input"
                                <?php echo $farmer['organic_certified'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="organicCertified">Organic Certified</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>

                <!-- Password Change Section -->
                <div class="dashboard-card mb-4">
                    <h2 class="dashboard__card-title font-body--xl-500">Change Password</h2>
                    <form method="POST" action="/farmer/change-password" class="dashboard__form">
                        <div class="form-group">
                            <label for="currentPassword">Current Password</label>
                            <input type="password" id="currentPassword" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="newPassword">New Password</label>
                            <input type="password" id="newPassword" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmPassword">Confirm New Password</label>
                            <input type="password" id="confirmPassword" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </form>
                </div>

                <!-- Delete Account Section -->
                <div class="dashboard-card border-danger">
                    <h2 class="dashboard__card-title font-body--xl-500 text-danger">Danger Zone</h2>
                    <div class="card-body">
                        <h5 class="card-title text-danger">Delete Account</h5>
                        <p class="card-text">Once you delete your account, there is no going back. Please be certain.</p>
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAccountModal">
                            Delete Account
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Account Deletion</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete your account? This action cannot be undone.</p>
                <form id="deleteAccountForm" method="POST" action="/farmer/delete-account">
                    <div class="form-group">
                        <label for="deleteConfirm">Type "DELETE" to confirm</label>
                        <input type="text" id="deleteConfirm" name="delete_confirm" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger" form="deleteAccountForm">Delete Account</button>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const messageDiv = document.getElementById('updateProfileMessage');
        const form = this;
        const formData = new FormData(form);

        // Add organic_certified if unchecked
        if (!formData.has('organic_certified')) {
            formData.append('organic_certified', '0');
        }

        fetch('/farmer/update-profile', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                messageDiv.style.display = 'block';
                if (data.success) {
                    messageDiv.className = 'alert alert-success';
                    messageDiv.textContent = data.message || 'Profile updated successfully';

                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }

                    // Redirect back to account settings page after successful update
                    setTimeout(() => {
                        window.location.href = '/farmer/account-settings';
                    }, 1500); // Wait 1.5 seconds so user can see success message
                } else {
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = data.message || 'Failed to update profile';
                }
            })
            .catch(error => {
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-danger';
                messageDiv.textContent = 'An error occurred. Please try again.';
                console.error('Error:', error);
            });
    });

    // Form validation
    function validateForm() {
        const farmingExperience = document.getElementById('farmingExperience').value;
        if (isNaN(farmingExperience) || farmingExperience < 0) {
            alert('Please enter a valid number for farming experience');
            return false;
        }
        return true;
    }
</script>