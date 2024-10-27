<?php
namespace App\Views\Auth;
use App\Utils\Functions;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(dirname(__DIR__))));
}
?>

<section class="section register section--xl">
    <div class="breadcrumb">
        <div class="breadcrumb__img-wrapper">
            <img src="/images/banner/farmer-register.jpg" alt="Become a Farmer">

            <div class="container">
                <ul class="breedcrumb__content">
                    <li><a href="/">Home</a> &gt;</li>
                    <li>Become a Farmer</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="form-wrapper">
            <h6 class="font-title--sm">Become a Farmer</h6>
            <p class="text-muted mb-4">Join our community of farmers and reach more customers</p>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="form-input error">
                    <span class="icon icon-error">
                        <!-- Error icon SVG -->
                    </span>
                    <?php echo Functions::h($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="/farmer/register" method="POST" id="farmerRegisterForm" class="farmer-register-form">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Personal Information</h3>
                    
                    <div class="form-input">
                        <input type="text" name="name" placeholder="Full Name" required>
                    </div>

                    <div class="form-input">
                        <input type="email" name="email" placeholder="Email Address" required>
                    </div>

                    <div class="form-input">
                        <input type="password" name="password" placeholder="Password" required>
                        <span class="icon icon-eye" onclick="togglePassword(this)">
                            <!-- Eye icon SVG -->
                        </span>
                    </div>

                    <div class="form-input">
                        <input type="tel" name="phone_number" placeholder="Phone Number" required>
                    </div>
                </div>

                <!-- Farm Information -->
                <div class="form-section">
                    <h3 class="form-section-title">Farm Information</h3>
                    
                    <div class="form-input">
                        <input type="text" name="farm_name" placeholder="Farm Name" required>
                    </div>

                    <div class="form-input">
                        <input type="text" name="location" placeholder="Farm Location" required>
                    </div>

                    <div class="form-input">
                        <select name="farm_type" required>
                            <option value="">Select Farm Type</option>
                            <option value="vegetables">Vegetables</option>
                            <option value="fruits">Fruits</option>
                            <option value="rice">Rice</option>
                            <option value="mixed">Mixed Farming</option>
                        </select>
                    </div>

                    <div class="form-input">
                        <select name="farm_size" required>
                            <option value="">Select Farm Size</option>
                            <option value="small">Small (1-5 hectares)</option>
                            <option value="medium">Medium (6-20 hectares)</option>
                            <option value="large">Large (20+ hectares)</option>
                        </select>
                    </div>

                    <div class="form-input">
                        <input type="text" name="primary_products" placeholder="Primary Products (comma separated)" required>
                    </div>

                    <div class="form-input">
                        <select name="farming_experience" required>
                            <option value="">Years of Experience</option>
                            <option value="0-2">0-2 years</option>
                            <option value="3-5">3-5 years</option>
                            <option value="5-10">5-10 years</option>
                            <option value="10+">10+ years</option>
                        </select>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" name="organic_certified" id="organic_certified">
                        <label for="organic_certified">Organic Certified</label>
                    </div>

                    <div class="form-input">
                        <textarea name="additional_info" placeholder="Additional Information (Optional)"></textarea>
                    </div>
                </div>

                <div class="form-check terms">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms">
                        I agree to the <a href="/terms">Terms and Conditions</a> and <a href="/privacy">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="button button--lg w-100">Register as Farmer</button>

                <div class="form-register">
                    Already have an account? <a href="/login">Sign in</a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
.farmer-register-form {
    max-width: 600px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid #eee;
}

.form-section-title {
    font-size: 1.1rem;
    font-weight: 500;
    margin-bottom: 1.5rem;
    color: #333;
}

.form-input select,
.form-input textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-bottom: 1rem;
}

.form-input textarea {
    height: 100px;
    resize: vertical;
}

.terms {
    margin: 1.5rem 0;
}

.terms a {
    color: #00B207;
    text-decoration: none;
}

.form-check {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    margin-bottom: 1rem;
}

.form-check input[type="checkbox"] {
    margin-top: 0.25rem;
}
</style>

<script>
function togglePassword(button) {
    const input = button.parentNode.querySelector('input');
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
    button.classList.toggle('active');
}

document.getElementById('farmerRegisterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('/farmer/register', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/login?registered=1';
        } else {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'form-input error';
            alertDiv.innerHTML = `
                <span class="icon icon-error">
                    <!-- Error icon SVG -->
                </span>
                ${data.message}
            `;
            this.insertBefore(alertDiv, this.firstChild);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
});
</script>