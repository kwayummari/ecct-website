<?php

/**
 * ECCT Website Configuration File
 */

// Prevent direct access
if (!defined('ECCT_ROOT')) {
    die('Direct access not allowed');
}

// Load environment variables
require_once ECCT_ROOT . '/includes/env.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'u750269652_ecct2025'));
define('DB_USER', env('DB_USER', 'u750269652_ecctAdmin'));
define('DB_PASS', env('DB_PASS', ']R6yP;OW58Z'));
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', env('SITE_NAME', 'ECCT - Environmental Conservation Community of Tanzania'));
define('SITE_URL', env('SITE_URL', 'https://ecct.or.tz'));
define('SITE_EMAIL', env('SITE_EMAIL', 'info@ecct.or.tz'));
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'info@ecct.or.tz'));

// Paths
define('UPLOADS_PATH', ECCT_ROOT . '/' . env('UPLOAD_PATH', 'assets/uploads'));
define('UPLOADS_URL', SITE_URL . '/' . env('UPLOAD_PATH', 'assets/uploads'));
define('ASSETS_PATH', SITE_URL . '/assets');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', env('SESSION_LIFETIME', 7200));

// File Upload Settings
define('MAX_FILE_SIZE', env('MAX_FILE_SIZE', 5 * 1024 * 1024)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif')));

// Pagination
define('DEFAULT_PER_PAGE', 10);
define('MAX_PER_PAGE', 50);

// Debug Mode
define('DEBUG_MODE', env('DEBUG_MODE', false));

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Error Reporting (disable in production)
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Custom error handler
function ecct_error_handler($errno, $errstr, $errfile, $errline)
{
    $error_message = "Error: [{$errno}] {$errstr} in {$errfile} on line {$errline}";
    error_log($error_message);

    // Don't show errors to users in production
    if (!DEBUG_MODE) {
        return true;
    }

    return false;
}

set_error_handler('ecct_error_handler');

// Helper Functions
function site_url($path = '')
{
    return SITE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

function admin_url($path = '')
{
    return SITE_URL . '/admin' . ($path ? '/' . ltrim($path, '/') : '');
}

function asset_url($path)
{
    return SITE_URL . '/assets/' . ltrim($path, '/');
}

function upload_url($path)
{
    return UPLOADS_URL . '/' . ltrim($path, '/');
}

// Security functions
function generate_csrf_token()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function get_csrf_token()
{
    return generate_csrf_token();
}

function verify_csrf_token($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function sanitize_input($input)
{
    if (is_array($input)) {
        return array_map('sanitize_input', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function create_slug($string)
{
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\s-]/', '', $string);
    $string = preg_replace('/[\s-]+/', '-', $string);
    return trim($string, '-');
}

function get_user_ip()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }
}

function time_ago($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';
    if ($time < 31536000) return floor($time / 2592000) . ' months ago';

    return floor($time / 31536000) . ' years ago';
}

function format_filesize($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= (1 << (10 * $pow));

    return round($bytes, 2) . ' ' . $units[$pow];
}

// Current URL helper
function current_url()
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    return $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Redirect helper
function redirect($url)
{
    if (headers_sent()) {
        echo '<script>window.location.href="' . htmlspecialchars($url) . '";</script>';
    } else {
        header('Location: ' . $url);
    }
    exit();
}

// Email configuration
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_USERNAME', env('SMTP_USER', ''));
define('SMTP_PASSWORD', env('SMTP_PASS', ''));
define('SMTP_ENCRYPTION', 'tls');

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600); // 1 hour

// Set global variables for templates
$GLOBALS['site_config'] = [
    'name' => SITE_NAME,
    'url' => SITE_URL,
    'email' => ADMIN_EMAIL,
    'assets_url' => ASSETS_PATH,
    'uploads_url' => UPLOADS_URL
];
