<?php

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

    // Find user by username or email
    $user = $db->selectOne('admin_users', ['username' => $username]);
    if (!$user) {
        $user = $db->selectOne('admin_users', ['email' => $username]);
    }

    if (!$user) {
        return false;
    }

    // Check if user is active
    if (!$user['is_active']) {
        return false;
    }

    // Verify password
    if (!password_verify($password, $user['password'])) {
        return false;
    }

    // Update last login
    $db->update('admin_users', [
        'last_login' => date('Y-m-d H:i:s'),
        'login_count' => $user['login_count'] + 1
    ], ['id' => $user['id']]);

    // Log activity
    log_activity($user['id'], 'login', 'User logged in');

    return $user;
}

/**
 * Logout user
 */
function logout_user()
{
    if (is_logged_in()) {
        $user = get_logged_in_user();
        if ($user) {
            log_activity($user['id'], 'logout', 'User logged out');
        }
    }

    // Clear session
    session_unset();
    session_destroy();
    session_start();
    session_regenerate_id(true);
}

/**
 * Check if user has specific role
 */
function has_role($role)
{
    $user = get_logged_in_user();
    if (!$user) {
        return false;
    }

    return $user['role'] === $role || $user['role'] === 'super_admin';
}

/**
 * Check if user has permission
 */
function has_permission($permission)
{
    $user = get_logged_in_user();
    if (!$user) {
        return false;
    }

    // Super admin has all permissions
    if ($user['role'] === 'super_admin') {
        return true;
    }

    // Define role permissions
    $permissions = [
        'admin' => [
            'manage_news',
            'manage_campaigns',
            'manage_volunteers',
            'manage_messages',
            'manage_gallery',
            'manage_pages',
            'manage_settings'
        ],
        'editor' => [
            'manage_news',
            'manage_campaigns',
            'manage_gallery',
            'manage_pages'
        ],
        'moderator' => [
            'manage_volunteers',
            'manage_messages'
        ]
    ];

    $user_permissions = $permissions[$user['role']] ?? [];
    return in_array($permission, $user_permissions);
}

/**
 * Require specific permission
 */
function require_permission($permission)
{
    if (!has_permission($permission)) {
        http_response_code(403);
        die('Access denied. You do not have permission to access this resource.');
    }
}

/**
 * Create new admin user
 */
function create_admin_user($data)
{
    $db = new Database();

    // Check if username exists
    if ($db->exists('admin_users', ['username' => $data['username']])) {
        return ['error' => 'Username already exists'];
    }

    // Check if email exists
    if ($db->exists('admin_users', ['email' => $data['email']])) {
        return ['error' => 'Email already exists'];
    }

    // Hash password
    $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    $data['created_at'] = date('Y-m-d H:i:s');
    $data['is_active'] = $data['is_active'] ?? 1;
    $data['role'] = $data['role'] ?? 'editor';

    $user_id = $db->insert('admin_users', $data);

    if ($user_id) {
        log_activity($user_id, 'user_created', 'Admin user created');
        return ['success' => true, 'user_id' => $user_id];
    }

    return ['error' => 'Failed to create user'];
}

/**
 * Update admin user
 */
function update_admin_user($user_id, $data)
{
    $db = new Database();

    // Don't update password if empty
    if (empty($data['password'])) {
        unset($data['password']);
    } else {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $data['updated_at'] = date('Y-m-d H:i:s');

    if ($db->update('admin_users', $data, ['id' => $user_id])) {
        log_activity($user_id, 'user_updated', 'Admin user updated');
        return ['success' => true];
    }

    return ['error' => 'Failed to update user'];
}

/**
 * Delete admin user
 */
function delete_admin_user($user_id)
{
    $db = new Database();

    // Don't allow deleting yourself
    $current_user = get_logged_in_user();
    if ($current_user && $current_user['id'] == $user_id) {
        return ['error' => 'Cannot delete your own account'];
    }

    if ($db->delete('admin_users', ['id' => $user_id])) {
        log_activity($current_user['id'], 'user_deleted', "Admin user {$user_id} deleted");
        return ['success' => true];
    }

    return ['error' => 'Failed to delete user'];
}

/**
 * Log user activity
 */
function log_activity($user_id, $action, $description = '', $ip_address = null)
{
    $db = new Database();

    $data = [
        'user_id' => $user_id,
        'action' => $action,
        'description' => $description,
        'ip_address' => $ip_address ?: get_user_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert('activity_log', $data);
}

/**
 * Get recent activity
 */
function get_recent_activity($limit = 20)
{
    $db = new Database();

    $sql = "
        SELECT al.*, au.full_name, au.username
        FROM activity_log al
        LEFT JOIN admin_users au ON al.user_id = au.id
        ORDER BY al.created_at DESC
        LIMIT ?
    ";

    $stmt = $db->raw($sql, [$limit]);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Generate secure password
 */
function generate_password($length = 12)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Validate password strength
 */
function validate_password($password)
{
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number';
    }

    return $errors;
}

/**
 * Check for brute force attempts
 */
function check_login_attempts($username, $ip_address)
{
    $db = new Database();

    // Check attempts in last 15 minutes
    $sql = "
        SELECT COUNT(*) as attempts
        FROM login_attempts
        WHERE (username = ? OR ip_address = ?)
        AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)
    ";

    $stmt = $db->raw($sql, [$username, $ip_address]);
    $result = $stmt ? $stmt->fetch() : ['attempts' => 0];

    return (int)$result['attempts'];
}

/**
 * Log failed login attempt
 */
function log_failed_login($username, $ip_address)
{
    $db = new Database();

    $data = [
        'username' => $username,
        'ip_address' => $ip_address,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db->insert('login_attempts', $data);
}

/**
 * Clear old login attempts
 */
function cleanup_login_attempts()
{
    $db = new Database();

    // Delete attempts older than 24 hours
    $sql = "DELETE FROM login_attempts WHERE created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    $db->raw($sql);
}
