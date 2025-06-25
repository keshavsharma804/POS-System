<?php
require 'config.php';

// Check for remember me cookie
if (checkRememberCookie('user')) {
    header('Location: admin.php');
    exit;
}

$csrf_token = getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrf_token); ?>">
    <title>POS Login & Register | QuickStock</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-light: #ADD8E6;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --text-color: #2b2d42;
            --light-gray: #f8f9fa;
            --white: #ffffff;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            background-color: var(--white);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar */
        .navbar {
            background-color: var(--white);
            box-shadow: var(--shadow);
            padding: 1rem 2rem;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: var(--primary-color);
            display: flex;
            align-items: center;
        }
        
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
        
        .auth-buttons .btn {
            margin-left: 10px;
            padding: 8px 20px;
            font-weight: 500;
            border-radius: 8px;
            transition: var(--transition);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }
        
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(173, 216, 230, 0.1) 0%, rgba(255, 255, 255, 1) 100%);
            padding: 8rem 0 4rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .hero-text h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: var(--text-color);
            line-height: 1.2;
        }
        
        .hero-text p {
            font-size: 1.25rem;
            color: #6c757d;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 16px;
            box-shadow: var(--shadow);
        }
        
        .cta-button {
            padding: 12px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition);
        }
        
        .cta-button:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }
        
        /* Auth Forms */
        .auth-container {
            background-color: var(--white);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 2.5rem;
            max-width: 500px;
            margin: 0 auto;
            transform: translateY(0);
            transition: var(--transition);
        }
        
        .auth-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .auth-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .auth-header h2 {
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }
        
        .auth-header p {
            color: #6c757d;
        }
        
        .form-control {
            padding: 12px 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            transition: var(--transition);
        }
        
        .form-control:focus {
            border-color: var(--primary-light);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.1);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .auth-toggle {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .auth-toggle:hover {
            color: var(--secondary-color);
            text-decoration: underline;
        }
        
        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }
        
        .success-message {
            color: #28a745;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .hero-text h1 {
                font-size: 2.5rem;
            }
            
            .hero-text p {
                font-size: 1.1rem;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 1rem;
            }
            
            .hero-section {
                padding-top: 6rem;
                text-align: center;
            }
            
            .hero-text h1 {
                font-size: 2rem;
            }
            
            .hero-text p {
                margin-left: auto;
                margin-right: auto;
            }
            
            .auth-buttons .btn {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
        }
        
        @media (max-width: 576px) {
            .auth-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="/POS/uploads/image4.png" alt="">
                QuickStock
            </a>
            <div class="auth-buttons">
                <button id="navbar-login-btn" class="btn btn-primary">Login</button>
                <button id="navbar-register-btn" class="btn btn-outline-primary">Register</button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-text">
                        <h1>Welcome to <span style="color: var(--primary-color);">QuickStock</span></h1>
                        <p>Experience simplicity and power with our platform. Manage your business efficiently with our cutting-edge POS solution.</p>
                        <button id="get-started-btn" class="btn btn-primary cta-button">Get Started</button>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-image">
                        <img src="/POS/uploads/image.jpg" alt="POS System Illustration">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Auth Modals -->
    <div class="modal fade" id="authModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0">
                <div class="modal-body p-0">
                    <div class="auth-container">
                        <!-- Login Form -->
                        <div id="login-section">
                            <div class="auth-header">
                                <h2>Welcome Back</h2>
                                <p>Login to access your account</p>
                            </div>
                            <p id="error-message" class="error-message"></p>
                            <form id="login-form" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" name="remember" id="remember" class="form-check-input">
                                    <label for="remember" class="form-check-label">Remember me</label>
                                    <a href="#" id="toggle-to-forgot" class="float-end auth-toggle">Forgot password?</a>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Login</button>
                                <p class="text-center">Don't have an account? <a href="#" id="toggle-to-register" class="auth-toggle">Register</a></p>
                            </form>
                        </div>

                        <!-- Register Form -->
                        <div id="register-section" style="display: none;">
                            <div class="auth-header">
                                <h2>Create Account</h2>
                                <p>Get started with our platform</p>
                            </div>
                            <p id="register-error-message" class="error-message"></p>
                            <form id="register-form" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <div class="mb-3">
                                    <label for="reg-username" class="form-label">Username</label>
                                    <input type="text" id="reg-username" name="username" class="form-control" placeholder="Choose a username" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg-email" class="form-label">Email</label>
                                    <input type="email" id="reg-email" name="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="reg-password" class="form-label">Password</label>
                                    <input type="password" id="reg-password" name="password" class="form-control" placeholder="Create a password" required>
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">
                                    <span class="submit-text">Register</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                                <p class="text-center">Already have an account? <a href="#" id="toggle-to-login" class="auth-toggle">Login</a></p>
                            </form>
                        </div>

                        <!-- Forgot Password Form -->
                        <div id="forgot-section" style="display: none;">
                            <div class="auth-header">
                                <h2>Reset Password</h2>
                                <p>We'll send you a link to reset your password</p>
                            </div>
                            <p id="forgot-error-message" class="error-message"></p>
                            <form id="forgot-form" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                <div class="mb-3">
                                    <label for="forgot-email" class="form-label">Email</label>
                                    <input type="email" id="forgot-email" name="email" class="form-control" placeholder="Enter your email" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Send OTP</button>
                                <p class="text-center">Remember your password? <a href="#" id="toggle-to-login-from-forgot" class="auth-toggle">Login</a></p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const authModal = new bootstrap.Modal(document.getElementById('authModal'));
        const loginSection = document.getElementById('login-section');
        const registerSection = document.getElementById('register-section');
        const forgotSection = document.getElementById('forgot-section');
        const navbarLoginBtn = document.getElementById('navbar-login-btn');
        const navbarRegisterBtn = document.getElementById('navbar-register-btn');
        const getStartedBtn = document.getElementById('get-started-btn');
        let csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Store original forgot form HTML
        const originalForgotForm = document.getElementById('forgot-form').innerHTML;

        // Show login modal when clicking navbar login button
        navbarLoginBtn.addEventListener('click', () => {
            loginSection.style.display = 'block';
            registerSection.style.display = 'none';
            forgotSection.style.display = 'none';
            authModal.show();
        });

        // Show register modal when clicking navbar register button
        navbarRegisterBtn.addEventListener('click', () => {
            loginSection.style.display = 'none';
            registerSection.style.display = 'block';
            forgotSection.style.display = 'none';
            authModal.show();
        });

        // Show register modal when clicking get started button
        getStartedBtn.addEventListener('click', () => {
            loginSection.style.display = 'none';
            registerSection.style.display = 'block';
            forgotSection.style.display = 'none';
            authModal.show();
        });

        // Toggle between login and register forms
        document.getElementById('toggle-to-register').addEventListener('click', (e) => {
            e.preventDefault();
            loginSection.style.display = 'none';
            registerSection.style.display = 'block';
        });

        document.getElementById('toggle-to-login').addEventListener('click', (e) => {
            e.preventDefault();
            loginSection.style.display = 'block';
            registerSection.style.display = 'none';
        });

        // Forgot password toggle
        document.getElementById('toggle-to-forgot').addEventListener('click', (e) => {
            e.preventDefault();
            loginSection.style.display = 'none';
            registerSection.style.display = 'none';
            forgotSection.style.display = 'block';
        });

        document.getElementById('toggle-to-login-from-forgot').addEventListener('click', (e) => {
            e.preventDefault();
            loginSection.style.display = 'block';
            registerSection.style.display = 'none';
            forgotSection.style.display = 'none';
            document.getElementById('forgot-form').innerHTML = originalForgotForm;
        });

        function showError(elementId, message, isSuccess = false) {
            const element = document.getElementById(elementId);
            element.textContent = message;
            element.className = isSuccess ? 'success-message' : 'error-message';
            element.style.display = 'block';
            setTimeout(() => element.style.display = 'none', 5000);
        }

        function showOtpForm(email) {
            document.getElementById('forgot-form').innerHTML = `
                <input type="hidden" name="email" value="${email}">
                <input type="hidden" name="csrf_token" value="${csrfToken}">
                <div class="mb-3">
                    <label for="otp" class="form-label">Enter OTP:</label>
                    <input type="text" id="otp" name="otp" class="form-control" placeholder="6-digit code" required>
                </div>
                <div class="mb-3">
                    <label for="new-password" class="form-label">New Password:</label>
                    <input type="password" id="new-password" name="new_password" class="form-control" placeholder="Enter new password" required>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 mb-3">Reset Password</button>
            `;
            document.getElementById('forgot-form').removeEventListener('submit', handleForgotSubmit);
            document.getElementById('forgot-form').addEventListener('submit', handleResetSubmit);
        }

        function handleForgotSubmit(e) {
            e.preventDefault();
            const email = document.getElementById('forgot-email').value.trim();
            
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('forgot-error-message', 'Please enter a valid email address');
                return;
            }

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'forgot_password',
                    email,
                    csrf_token: csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showError('forgot-error-message', 'OTP sent to your email!', true);
                    showOtpForm(email);
                } else {
                    showError('forgot-error-message', data.message || 'Failed to send OTP');
                }
            })
            .catch(error => {
                console.error('Forgot password error:', error);
                showError('forgot-error-message', 'An error occurred');
            });
        }

        function handleResetSubmit(e) {
            e.preventDefault();
            const email = document.querySelector('#forgot-form input[name="email"]').value;
            const otp = document.querySelector('#forgot-form input[name="otp"]').value.trim();
            const newPassword = document.querySelector('#forgot-form input[name="new_password"]').value;

            if (!/^[0-9]{6}$/.test(otp)) {
                showError('forgot-error-message', 'OTP must be a 6-digit number');
                return;
            }
            if (newPassword.length < 8) {
                showError('forgot-error-message', 'Password must be at least 8 characters');
                return;
            }

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'reset_password',
                    email,
                    otp,
                    new_password: newPassword,
                    csrf_token: csrfToken
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showError('forgot-error-message', 'Password reset successfully! Redirecting to login...', true);
                    setTimeout(() => {
                        document.getElementById('forgot-form').innerHTML = originalForgotForm;
                        loginSection.style.display = 'block';
                        registerSection.style.display = 'none';
                        forgotSection.style.display = 'none';
                        document.getElementById('forgot-form').removeEventListener('submit', handleResetSubmit);
                        document.getElementById('forgot-form').addEventListener('submit', handleForgotSubmit);
                    }, 2000);
                } else {
                    showError('forgot-error-message', data.message || 'Failed to reset password');
                }
            })
            .catch(error => {
                console.error('Reset password error:', error);
                showError('forgot-error-message', 'An error occurred');
            });
        }

        document.getElementById('forgot-form').addEventListener('submit', handleForgotSubmit);

        function updateCsrfToken(newToken) {
            csrfToken = newToken;
            document.querySelector('meta[name="csrf-token"]').content = newToken;
            document.querySelectorAll('input[name="csrf_token"]').forEach(input => {
                input.value = newToken;
            });
        }

        // Login form submission
        document.getElementById('login-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            const remember = document.getElementById('remember').checked;

            if (!username) {
                showError('error-message', 'Please enter a username');
                return;
            }
            if (!password) {
                showError('error-message', 'Please enter a password');
                return;
            }

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'login',
                    username,
                    password,
                    remember,
                    csrf_token: csrfToken
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || 'Login failed');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    updateCsrfToken(data.csrf_token);
                    window.location.href = 'admin.php';
                } else {
                    showError('error-message', data.message || 'Invalid username or password');
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                showError('error-message', error.message === 'Invalid username or password' ? 
                    'Invalid username or password, please try again' : error.message);
            });
        });

        // Register form submission
        document.getElementById('register-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const submitBtn = e.target.querySelector('button[type="submit"]');
            const submitText = submitBtn.querySelector('.submit-text');
            const spinner = submitBtn.querySelector('.spinner-border');
            
            submitText.classList.add('d-none');
            spinner.classList.remove('d-none');
            submitBtn.disabled = true;

            const username = document.getElementById('reg-username').value.trim();
            const email = document.getElementById('reg-email').value.trim();
            const password = document.getElementById('reg-password').value;

            if (username.length < 3 || username.length > 50) {
                showError('register-error-message', 'Username must be between 3 and 50 characters');
                submitText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
                return;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                showError('register-error-message', 'Please enter a valid email address');
                submitText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
                return;
            }
            if (password.length < 8) {
                showError('register-error-message', 'Password must be at least 8 characters long');
                submitText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
                return;
            }

            fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'register',
                    username,
                    email,
                    password,
                    csrf_token: csrfToken
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    document.getElementById('reg-username').value = '';
                    document.getElementById('reg-email').value = '';
                    document.getElementById('reg-password').value = '';
                    
                    updateCsrfToken(data.csrf_token);
                    showError('register-error-message', 'Registration successful! Redirecting to login...', true);
                    
                    setTimeout(() => {
                        loginSection.style.display = 'block';
                        registerSection.style.display = 'none';
                    }, 1500);
                } else {
                    showError('register-error-message', data.message || 'Registration failed');
                }
            })
            .catch(error => {
                console.error('Register error:', error);
                showError('register-error-message', error.message);
            })
            .finally(() => {
                submitText.classList.remove('d-none');
                spinner.classList.add('d-none');
                submitBtn.disabled = false;
            });
        });
    });
    </script>
</body>
</html>