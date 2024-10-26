<?php
namespace App\Views\Auth;
use App\Utils\Functions;

// Check if this is a direct access
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(dirname(__DIR__))));
}
?>


<!-- Main Content -->
<section class="section sign-in section--xl">
    <div class="breedcrumb">
        <div class="breedcrumb__img-wrapper">
            <img src="/images/banner/breedcrumb.jpg" alt="Login page banner">

            <div class="container">
                <ul class="breedcrumb__content">
                    <li>
                        <a href="/">
                            <svg width="18" height="19" viewBox="0 0 18 19">
                                <path d="M1 8L9 1L17 8V18H12V14C12 13.2044 11.6839 12.4413 11.1213 11.8787C10.5587 11.3161 9.79565 11 9 11C8.20435 11 7.44129 11.3161 6.87868 11.8787C6.31607 12.4413 6 13.2044 6 14V18H1V8Z" 
                                      stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a> &gt; 
                    </li>
                    <li>Sign in</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="form-wrapper">
            <h6 class="font-title--sm">Sign in</h6>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="form-input">
                    <span class="icon icon-error">
                        <svg width="20" height="20" viewBox="0 0 20 20">
                            <path d="M8.57465 3.21667L1.51632 15C1.37079 15.252 1.29379 15.5378 1.29298 15.8288C1.29216 16.1198 1.36756 16.4059 1.51167 16.6588C1.65579 16.9116 1.86359 17.1223 2.11441 17.2699C2.36523 17.4175 2.65032 17.4968 2.94132 17.5H17.058C17.349 17.4968 17.6341 17.4175 17.8849 17.2699C18.1357 17.1223 18.3435 16.9116 18.4876 16.6588C18.6317 16.4059 18.7071 16.1198 18.7063 15.8288C18.7055 15.5378 18.6285 15.252 18.483 15L11.4247 3.21667C11.2761 2.97176 11.0669 2.76927 10.8173 2.62874C10.5677 2.48821 10.2861 2.41438 9.99965 2.41438C9.71321 2.41438 9.43159 2.48821 9.18199 2.62874C8.93238 2.76927 8.72321 2.97176 8.57465 3.21667V3.21667Z" 
                                  stroke="#EA4B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 7.5V10.8333" stroke="#EA4B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 14.1667H10.0083" stroke="#EA4B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <?php echo Functions::h($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" id="loginForm" class="login-form">
                <div class="form-input">
                    <input type="email" name="email" placeholder="Email Address" required>
                    <span class="icon icon-warning">
                        <svg width="20" height="21" viewBox="0 0 20 21">
                            <path d="M10.0003 18.8333C14.6027 18.8333 18.3337 15.1024 18.3337 10.5C18.3337 5.89762 14.6027 2.16666 10.0003 2.16666C5.39795 2.16666 1.66699 5.89762 1.66699 10.5C1.66699 15.1024 5.39795 18.8333 10.0003 18.8333Z" 
                                  stroke="#FF8A00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 7.16666V10.5" stroke="#FF8A00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 13.8333H10.0083" stroke="#FF8A00" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>

                <div class="form-input">
                    <input type="password" name="password" placeholder="Password" required>
                    <span class="icon icon-eye" id="togglePassword">
                        <svg width="20" height="21" viewBox="0 0 20 21">
                            <path d="M1.66699 10.5C1.66699 10.5 4.69699 4.66666 10.0003 4.66666C15.3037 4.66666 18.3337 10.5 18.3337 10.5C18.3337 10.5 15.3037 16.3333 10.0003 16.3333C4.69699 16.3333 1.66699 10.5 1.66699 10.5Z" 
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M10 13C11.1046 13 12 11.933 12 10.5C12 9.067 11.1046 8 10 8C8.89543 8 8 9.067 8 10.5C8 11.933 8.89543 13 10 13Z" 
                                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                </div>

                <div class="form-wrapper__content">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="/forgot-password">Forget Password</a>
                </div>

                <button type="submit" class="btn btn-success btn-block">Login</button>

                <div class="form-register">
                    Don't have account? <a href="/register">Register</a>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    function togglePassword(button) {
        const input = button.parentNode.querySelector('input');
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        
        // Toggle eye icon
        const icon = button.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
    }
document.addEventListener('DOMContentLoaded', function() {
    // Password visibility toggle
    const togglePassword = document.getElementById('togglePassword');
    const password = document.querySelector('input[name="password"]');
    
    if (togglePassword && password) {
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('active');
        });
    }

    // Form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('/api/auth/login', {
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
                    alertDiv.className = 'form-input';
                    alertDiv.innerHTML = `
                        <span class="icon icon-error">
                            <svg width="20" height="20" viewBox="0 0 20 20">
                                <path d="M8.57465 3.21667L1.51632 15C1.37079 15.252 1.29379 15.5378 1.29298 15.8288C1.29216 16.1198 1.36756 16.4059 1.51167 16.6588C1.65579 16.9116 1.86359 17.1223 2.11441 17.2699C2.36523 17.4175 2.65032 17.4968 2.94132 17.5H17.058C17.349 17.4968 17.6341 17.4175 17.8849 17.2699C18.1357 17.1223 18.3435 16.9116 18.4876 16.6588C18.6317 16.4059 18.7071 16.1198 18.7063 15.8288C18.7055 15.5378 18.6285 15.252 18.483 15L11.4247 3.21667C11.2761 2.97176 11.0669 2.76927 10.8173 2.62874C10.5677 2.48821 10.2861 2.41438 9.99965 2.41438C9.71321 2.41438 9.43159 2.48821 9.18199 2.62874C8.93238 2.76927 8.72321 2.97176 8.57465 3.21667V3.21667Z" 
                                      stroke="#EA4B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 7.5V10.8333" stroke="#EA4B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M10 14.1667H10.0083" stroke="#EA4B48" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
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
    }
});
</script>