<?php

/**
 * Admin Logout - ECCT Website
 */

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';

// Check if user is logged in
if (is_logged_in()) {
    // Log the logout activity
    log_activity('logout', 'User logged out');

    // Logout the user
    $auth->logout();

    // Set success message
    set_flash('success', 'You have been logged out successfully.');
}

// Redirect to login page
redirect(SITE_URL . '/admin/login.php');
