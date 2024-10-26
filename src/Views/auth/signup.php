<?php
// src/Views/auth/signup.php
require_once __DIR__ . '/../../../vendor/autoload.php';

$pageTitle = "Sign Up";
require_once __DIR__ . '/../shared/header.php';
?>

<section class="create-account section section--xl">
    <div class="container">
        <div class="form-wrapper">
            <h6 class="font-title--sm">Create Account</h6>
            <form id="signupForm" method="POST">
                <div class="form-input">
                    <input type="text" name="name" placeholder="Full Name" required />
                </div>
                <div class="form-input">
                    <input type="email" name="email" placeholder="Email" required />
                </div>
                <div class="form-input">
                    <input type="password" name="password" placeholder="Password" id="password" required />
                    <button type="button" class="icon icon-eye" onclick="showPassword('password',this)">
                        <svg width="20" height="21" viewBox="0 0 20 21"><!-- SVG Icon --></svg>
                    </button>
                </div>
                <div class="form-input">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" id="confirmPassword" required />
                    <button type="button" class="icon icon-eye" onclick="showPassword('confirmPassword',this)">
                        <svg width="20" height="21" viewBox="0 0 20 21"><!-- SVG Icon --></svg>
                    </button>
                </div>
                <div class="form-input">
                    <input type="text" name="address" placeholder="Address" required />
                </div>
                <div class="form-input">
                    <input type="text" name="phone_number" placeholder="Phone Number" required />
                </div>
                <div class="form-input">
                    <textarea name="preferences" placeholder="Preferences (optional)" class="input-style" 
                        oninput="autoResize(this)"></textarea>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="terms" required />
                    <label class="form-check-label" for="terms">Accept all Terms & Conditions</label>
                </div>
                <div class="form-button">
                    <button type="submit" class="button button--md w-100">Create Account</button>
                </div>
                <div class="form-register">
                    Already have an account? <a href="/login">Login</a>
                </div>
            </form>
        </div>
    </div>
</section>

<style>
    .input-style {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
    line-height: 1.5;
    resize: vertical;
    box-sizing: border-box;
}
</style>


<script>

    function autoResize(textarea) {
        // Reset the height to auto to calculate the new height correctly
        textarea.style.height = 'auto';
        // Set the new height based on the scroll height
        textarea.style.height = textarea.scrollHeight + 'px';
    }

    document.getElementById('signupForm').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission
        const formData = new FormData(this);

        fetch('/api/auth/customers/register', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to login page if registration is successful
                window.location.href = '/login';
            } else {
                // Show error message if registration fails
                displayAlert(data.message);
                // Scroll up to the top of the form to show the error
                scrollToTopOfForm();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            displayAlert('An error occurred. Please try again later.');
            // Scroll up to the top of the form to show the error
            scrollToTopOfForm();
        });
    });

    // Function to show alert message
    function displayAlert(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            <strong>Error:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        // Insert the alert before the form
        const formWrapper = document.querySelector('.form-wrapper');
        formWrapper.insertBefore(alertDiv, formWrapper.firstChild);

        // Automatically remove the alert after a few seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            alertDiv.remove();
        }, 5000);
    }

    // Function to scroll to the top of the form
    function scrollToTopOfForm() {
        const formWrapper = document.querySelector('.form-wrapper');
        formWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }


</script>

<?php require_once __DIR__ . '/../shared/footer.php'; ?>