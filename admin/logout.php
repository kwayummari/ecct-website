<?php
define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';

// Logout the user
logout_user();

// Redirect to login page with success message
header('Location: ' . SITE_URL . '/admin/login.php?logged_out=1');
exit;
