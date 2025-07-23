<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Authentication functions for ECCT Admin Panel
 */

if (!defined('ECCT_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Check if user is logged in
 */
function is_logged_in()
{
    return isset($_SESSION['admin_user_id']) && !empty($_SESSION['admin_user_id']);
}

/**
 * Require user to be logged in
 */
function require_login()
{
    if (!is_logged_in()) {
        // Use absolute URL to prevent redirect loops
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit;
    }

    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        logout_user();
        header('Location: ' . SITE_URL . '/admin/login.php?error=session_expired');
        exit;
    }

    $_SESSION['last_activity'] = time();
}

/**
 * Get current logged in user
 */
function get_logged_in_user()
{
    if (!is_logged_in()) {
        return false;
    }

    $db = new Database();
    $user = $db->selectOne('admin_users', ['id' => $_SESSION['admin_user_id']]);

    if (!$user) {
        logout_user();
        return false;
    }

    return $user;
}

/**
 * Authenticate user with username and password
 */
function authenticate_user($username, $password)
{
    $db = new Database();

    // Try to find the user by username first, then email
    $user = $db->selectOne('admin_users', ['username' => $username]);

    if (!$user) {
        $user = $db->selectOne('admin_users', ['email' => $username]);
    }

    // If user not found or password field missing, reject login
    if (!$user || !isset($user['password_hash'])) {
        return false;
    }

    // Check if user is active
    if (isset($user['is_active']) && !$user['is_active']) {
        return false;
    }

    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        return false;
    }

    // Update last login and increment login count
    $db->update('admin_users', [
        'last_login' => date('Y-m-d H:i:s'),
        'login_count' => isset($user['login_count']) ? $user['login_count'] + 1 : 1
    ], ['id' => $user['id']]);

    return $user;
}

/**
 * Logout user
 */
function logout_user()
{
    // Clear all session variables
    $_SESSION = array();

    // Delete the session cookie if it exists
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroy the session
    session_destroy();
}

/**
 * Check if user has specific role
 */
function has_role($role)
{
    if (!is_logged_in()) {
        return false;
    }

    $user_role = $_SESSION['admin_role'] ?? 'editor';

    // Role hierarchy: super_admin > admin > editor
    $role_hierarchy = [
        'super_admin' => 3,
        'admin' => 2,
        'editor' => 1
    ];

    $user_level = $role_hierarchy[$user_role] ?? 0;
    $required_level = $role_hierarchy[$role] ?? 0;

    return $user_level >= $required_level;
}

/**
 * Check if user can perform action
 */
function can_perform($action)
{
    if (!is_logged_in()) {
        return false;
    }

    $permissions = [
        'super_admin' => ['*'], // All permissions
        'admin' => [
            'view_dashboard',
            'manage_content',
            'manage_news',
            'manage_campaigns',
            'manage_gallery',
            'view_volunteers',
            'manage_settings',
            'view_analytics'
        ],
        'editor' => [
            'view_dashboard',
            'manage_content',
            'manage_news',
            'manage_campaigns',
            'manage_gallery',
            'view_volunteers'
        ]
    ];

    $user_role = $_SESSION['admin_role'] ?? 'editor';
    $user_permissions = $permissions[$user_role] ?? [];

    // Super admin has all permissions
    if (in_array('*', $user_permissions)) {
        return true;
    }

    return in_array($action, $user_permissions);
}

/**
 * Require specific role
 */
function require_role($role)
{
    require_login();

    if (!has_role($role)) {
        $_SESSION['error'] = 'Access denied. Insufficient permissions.';
        header('Location: ' . SITE_URL . '/admin/');
        exit;
    }
}

/**
 * Require specific permission
 */
function require_permission($action)
{
    require_login();

    if (!can_perform($action)) {
        $_SESSION['error'] = 'Access denied. Insufficient permissions.';
        header('Location: ' . SITE_URL . '/admin/');
        exit;
    }
}

/**
 * Generate a secure hash for passwords
 */
function hash_password($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 */
function verify_password($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Generate secure token
 */
function generate_token($length = 32)
{
    return bin2hex(random_bytes($length));
}

/**
 * Check if current user is the specified user
 */
function is_current_user($user_id)
{
    return is_logged_in() && $_SESSION['admin_user_id'] == $user_id;
}

/**
 * Get current user info safely
 */
function get_current_user()
{
    if (!is_logged_in()) {
        return null;
    }

    return [
        'id' => $_SESSION['admin_user_id'],
        'username' => $_SESSION['admin_username'] ?? '',
        'email' => $_SESSION['admin_email'] ?? '',
        'name' => $_SESSION['admin_name'] ?? 'Admin User',
        'role' => $_SESSION['admin_role'] ?? 'editor'
    ];
}
