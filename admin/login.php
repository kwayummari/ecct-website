<?php

/**
 * Admin Login Page for ECCT Website
 */

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Redirect if already logged in
if (is_logged_in()) {
    redirect(SITE_URL . '/admin/');
}

$error_message = '';
$success_message = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        // Rate limiting
        if (!check_rate_limit('admin_login', 5, 900)) { // 5 attempts per 15 minutes
            $error_message = 'Too many login attempts. Please try again later.';
        } else {
            $username = sanitize_input($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);

            if (empty($username) || empty($password)) {
                $error_message = 'Please enter both username and password.';
            } else {
                $result = $auth->login($username, $password, $remember);

                if ($result['success']) {
                    $redirect_url = $_GET['redirect'] ?? SITE_URL . '/admin/';
                    redirect($redirect_url);
                } else {
                    $error_message = $result['message'];
                }
            }
        }
    }
}

$page_title = 'Admin Login - ECCT';
$page_class = 'admin-login';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Stylesheets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_PATH; ?>/css/admin.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="admin-auth">

    <div class="auth-container">
        <div class="container-fluid">
            <div class="row min-vh-100">
                <!-- Left Side - Branding -->
                <div class="col-lg-6 d-none d-lg-block auth-branding">
                    <div class="branding-content">
                        <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg"
                            alt="ECCT Logo" class="brand-logo mb-4">
                        <h2 class="text-white mb-3">Environmental Conservation Community of Tanzania</h2>
                        <p class="text-white-50 lead">
                            Empowering communities for sustainable environmental conservation
                        </p>
                        <div class="feature-list mt-5">
                            <div class="feature-item d-flex align-items-center mb-3">
                                <i class="fas fa-leaf text-success me-3"></i>
                                <span class="text-white">Environmental Protection</span>
                            </div>
                            <div class="feature-item d-flex align-items-center mb-3">
                                <i class="fas fa-users text-success me-3"></i>
                                <span class="text-white">Community Empowerment</span>
                            </div>
                            <div class="feature-item d-flex align-items-center">
                                <i class="fas fa-recycle text-success me-3"></i>
                                <span class="text-white">Sustainable Practices</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side - Login Form -->
                <div class="col-lg-6 d-flex align-items-center">
                    <div class="auth-form-container w-100">
                        <div class="text-center mb-5">
                            <h1 class="h3 fw-bold text-dark">Admin Login</h1>
                            <p class="text-muted">Sign in to access the admin panel</p>
                        </div>

                        <?php if ($error_message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <?php echo htmlspecialchars($error_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success_message): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                <?php echo htmlspecialchars($success_message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="auth-form">
                            <?php echo csrf_field(); ?>

                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text"
                                        class="form-control"
                                        id="username"
                                        name="username"
                                        value="<?php echo htmlspecialchars($username ?? ''); ?>"
                                        placeholder="Enter your username"
                                        required
                                        autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Enter your password"
                                        required>
                                    <button type="button" class="btn btn-outline-secondary toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Sign In
                            </button>

                            <div class="text-center">
                                <a href="<?php echo SITE_URL; ?>" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-2"></i>
                                    Back to Website
                                </a>
                            </div>
                        </form>

                        <!-- Security Notice -->
                        <div class="security-notice mt-5">
                            <div class="card border-0 bg-light">
                                <div class="card-body p-3">
                                    <h6 class="card-title text-warning">
                                        <i class="fas fa-shield-alt me-2"></i>Security Notice
                                    </h6>
                                    <p class="card-text small text-muted mb-0">
                                        This is a secure area. All login attempts are logged and monitored.
                                        Contact the system administrator if you're having trouble accessing your account.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.addEventListener('DOMContentLoaded', function() {
            const toggleButton = document.querySelector('.toggle-password');
            const passwordField = document.getElementById('password');

            if (toggleButton && passwordField) {
                toggleButton.addEventListener('click', function() {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);

                    const icon = this.querySelector('i');
                    icon.classList.toggle('fa-eye');
                    icon.classList.toggle('fa-eye-slash');
                });
            }

            // Auto-focus username field
            const usernameField = document.getElementById('username');
            if (usernameField && !usernameField.value) {
                usernameField.focus();
            }

            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert:not(.alert-persistent)');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
    </script>
</body>

</html>