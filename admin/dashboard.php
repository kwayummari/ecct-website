<?php
// Redirect dashboard.php to index.php to maintain consistency
define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/admin/includes/config.php';

header('Location: ' . SITE_URL . '/admin/index.php');
exit;
