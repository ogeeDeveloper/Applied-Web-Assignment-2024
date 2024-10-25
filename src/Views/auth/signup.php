<?php
// src/Views/auth/signup.php
require_once __DIR__ . '/../../../vendor/autoload.php';

$pageTitle = "Sign Up";
require_once __DIR__ . '/../shared/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body">
                    <h2 class="card-title text-center mb-4">Create Account</h2>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php 
                                echo htmlspecialchars($_SESSION['error']); 
                                unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/signup" id="signupForm">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Account Type</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="customer">Customer</option>
                                <option value="farmer">Farmer</option>
                            </select>
                        </div>
                        <div id="farmerFields" style="display: none;">
                            <div class="mb-3">
                                <label for="farm_name" class="form-label">Farm Name</label>
                                <input type="text" class="form-control" id="farm_name" name="farm_name">
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Farm Location</label>
                                <input type="text" class="form-control" id="location" name="location">
                            </div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Sign Up</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="/login">Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('role').addEventListener('change', function() {
    const farmerFields = document.getElementById('farmerFields');
    farmerFields.style.display = this.value === 'farmer' ? 'block' : 'none';
});

document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('/api/auth/signup', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertDiv.innerHTML = `
                ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            this.insertBefore(alertDiv, this.firstChild);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>