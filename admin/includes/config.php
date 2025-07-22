<?php

/**
 * ECCT Website Configuration File
 */

// Prevent direct access
if (!defined('ECCT_ROOT')) {
    die('Direct access not allowed');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u750269652_ecct2025');
define('DB_USER', 'u750269652_ecct2025');
define('DB_PASS', 'your_database_password');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'ECCT - Environmental Conservation Community of Tanzania');
define('SITE_URL', 'https://www.ecct.or.tz');
define('ADMIN_EMAIL', 'info@ecct.or.tz');

// Paths
define('UPLOADS_PATH', ECCT_ROOT . '/assets/uploads');
define('UPLOADS_URL', SITE_URL . '/assets/uploads');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hour

// File Upload Settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']);

// Pagination
define('DEFAULT_PER_PAGE', 10);
define('MAX_PER_PAGE', 50);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Error Reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Custom error handler
function ecct_error_handler($errno, $errstr, $errfile, $errline)
{
    $error_message = "Error: [{$errno}] {$errstr} in {$errfile} on line {$errline}";
    error_log($error_message);

    // Don't show errors to users in production
    if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
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

// Email configuration (if needed)
define('SMTP_HOST', '');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('SMTP_ENCRYPTION', 'tls');

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600); // 1 hour