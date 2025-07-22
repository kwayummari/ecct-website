<?php
define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';

session_start();
session_destroy();

header('Location: login.php');
exit;
