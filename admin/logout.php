<?php

/**
 * Admin Logout - ECCT Admin Panel
 */

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/auth.php';

// Check if user is logged in
if (is_logged_in()) {
    // Log the logout activity
    log_activity('logout', 'User logged out');

    // Perform logout
    $auth->logout();
}

// Redirect to login page with logout message
session_start();
set_flash('info', 'You have been logged out successfully.');
redirect(SITE_URL . '/admin/login.php');
