<?php

/**
 * Authentication Functions for ECCT Website
 * Handles user login, session management, and access control
 */

require_once 'config.php';
require_once 'database.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Login user
     */
    public function login($username, $password, $remember = false)
    {
        // Get user from database
        $user = $this->db->selectOne('admin_users', [
            'username' => $username,
            'is_active' => 1
        ]);

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }

        // Update last login
        $this->db->update('admin_users', [
            'last_login' => date('Y-m-d H:i:s')
        ], ['id' => $user['id']]);

        // Set session variables
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_email'] = $user['email'];
        $_SESSION['admin_name'] = $user['full_name'];
        $_SESSION['admin_role'] = $user['role'];
        $_SESSION['login_time'] = time();

        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
            // Store token in database (you might want to create a remember_tokens table)
        }

        // Log activity
        log_activity('login', 'User logged in successfully');

        return ['success' => true, 'message' => 'Login successful'];
    }

    /**
     * Logout user
     */
    public function logout()
    {
        // Log activity before destroying session
        log_activity('logout', 'User logged out');

        // Destroy session
        session_destroy();

        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }

        return true;
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn()
    {
        return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
    }

    /**
     * Check if user has specific role
     */
    public function hasRole($role)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userRole = $_SESSION['admin_role'];

        // Role hierarchy: super_admin > admin > editor
        $roleHierarchy = [
            'super_admin' => 3,
            'admin' => 2,
            'editor' => 1
        ];

        $userLevel = $roleHierarchy[$userRole] ?? 0;
        $requiredLevel = $roleHierarchy[$role] ?? 0;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Check if user can perform action
     */
    public function canPerform($action)
    {
        if (!$this->isLoggedIn()) {
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

        $userRole = $_SESSION['admin_role'];
        $userPermissions = $permissions[$userRole] ?? [];

        // Super admin has all permissions
        if (in_array('*', $userPermissions)) {
            return true;
        }

        return in_array($action, $userPermissions);
    }

    /**
     * Require login (redirect if not logged in)
     */
    public function requireLogin()
    {
        if (!$this->isLoggedIn()) {
            redirect(SITE_URL . '/admin/login.php?redirect=' . urlencode(current_url()));
        }
    }

    /**
     * Require specific role
     */
    public function requireRole($role)
    {
        $this->requireLogin();

        if (!$this->hasRole($role)) {
            $_SESSION['error'] = 'Access denied. Insufficient permissions.';
            redirect(SITE_URL . '/admin/');
        }
    }

    /**
     * Require specific permission
     */
    public function requirePermission($action)
    {
        $this->requireLogin();

        if (!$this->canPerform($action)) {
            $_SESSION['error'] = 'Access denied. Insufficient permissions.';
            redirect(SITE_URL . '/admin/');
        }
    }

    /**
     * Get current user info
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['admin_id'],
            'username' => $_SESSION['admin_username'],
            'email' => $_SESSION['admin_email'],
            'name' => $_SESSION['admin_name'],
            'role' => $_SESSION['admin_role']
        ];
    }

    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Get user
        $user = $this->db->selectOne('admin_users', ['id' => $userId]);

        if (!$user) {
            return ['success' => false, 'message' => 'User not found'];
        }

        // Verify current password
        if (!password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long'];
        }

        // Update password
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $updated = $this->db->update('admin_users', [
            'password_hash' => $newHashedPassword
        ], ['id' => $userId]);

        if ($updated) {
            log_activity('password_change', 'Password changed successfully');
            return ['success' => true, 'message' => 'Password changed successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update password'];
        }
    }

    /**
     * Reset password (for admin use)
     */
    public function resetPassword($userId, $newPassword)
    {
        $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updated = $this->db->update('admin_users', [
            'password_hash' => $newHashedPassword
        ], ['id' => $userId]);

        if ($updated) {
            log_activity('password_reset', "Password reset for user ID: $userId");
            return ['success' => true, 'message' => 'Password reset successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to reset password'];
        }
    }

    /**
     * Create new admin user
     */
    public function createUser($userData)
    {
        // Validate required fields
        $required = ['username', 'email', 'password', 'full_name', 'role'];
        foreach ($required as $field) {
            if (empty($userData[$field])) {
                return ['success' => false, 'message' => "Field '{$field}' is required"];
            }
        }

        // Check if username already exists
        if ($this->db->exists('admin_users', ['username' => $userData['username']])) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Check if email already exists
        if ($this->db->exists('admin_users', ['email' => $userData['email']])) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Validate password
        if (strlen($userData['password']) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long'];
        }

        // Hash password
        $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        unset($userData['password']);

        // Insert user
        $userId = $this->db->insert('admin_users', $userData);

        if ($userId) {
            log_activity('user_create', "New user created: {$userData['username']}");
            return ['success' => true, 'message' => 'User created successfully', 'user_id' => $userId];
        } else {
            return ['success' => false, 'message' => 'Failed to create user'];
        }
    }

    /**
     * Update user
     */
    public function updateUser($userId, $userData)
    {
        // Remove password from update if empty
        if (isset($userData['password']) && empty($userData['password'])) {
            unset($userData['password']);
        } elseif (isset($userData['password'])) {
            // Hash new password
            $userData['password_hash'] = password_hash($userData['password'], PASSWORD_DEFAULT);
            unset($userData['password']);
        }

        $updated = $this->db->update('admin_users', $userData, ['id' => $userId]);

        if ($updated) {
            log_activity('user_update', "User updated: ID $userId");
            return ['success' => true, 'message' => 'User updated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to update user'];
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($userId)
    {
        // Prevent deletion of current user
        if ($userId == $_SESSION['admin_id']) {
            return ['success' => false, 'message' => 'Cannot delete current user'];
        }

        $deleted = $this->db->delete('admin_users', ['id' => $userId]);

        if ($deleted) {
            log_activity('user_delete', "User deleted: ID $userId");
            return ['success' => true, 'message' => 'User deleted successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to delete user'];
        }
    }

    /**
     * Check session timeout
     */
    public function checkSessionTimeout($timeout = 7200)
    { // 2 hours default
        if ($this->isLoggedIn() && isset($_SESSION['login_time'])) {
            if (time() - $_SESSION['login_time'] > $timeout) {
                $this->logout();
                return false;
            }
            // Update last activity time
            $_SESSION['login_time'] = time();
        }
        return true;
    }
}

// Global auth instance
$auth = new Auth();

// Helper functions for templates
function is_logged_in()
{
    global $auth;
    return $auth->isLoggedIn();
}

function has_role($role)
{
    global $auth;
    return $auth->hasRole($role);
}

function can_perform($action)
{
    global $auth;
    return $auth->canPerform($action);
}

function require_login()
{
    global $auth;
    return $auth->requireLogin();
}

function require_role($role)
{
    global $auth;
    return $auth->requireRole($role);
}

function require_permission($action)
{
    global $auth;
    return $auth->requirePermission($action);
}

function get_current_user()
{
    global $auth;
    return $auth->getCurrentUser();
}
