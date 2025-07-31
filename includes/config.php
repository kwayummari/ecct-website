<?php

/**
 * ECCT Website Configuration File
 * Environmental Conservation Community of Tanzania
 */

// Prevent direct access
if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', dirname(__FILE__, 2));
}

// Load environment variables
require_once __DIR__ . '/env.php';

// Database Configuration
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'u750269652_ecct2025'));
define('DB_USER', env('DB_USER', 'u750269652_ecctAdmin'));
define('DB_PASS', env('DB_PASS', ']R6yP;OW58Z'));
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', env('SITE_NAME', 'Environmental Conservation Community of Tanzania'));
define('SITE_URL', env('SITE_URL', 'https://ecct.serengetibytes.com'));
define('SITE_EMAIL', env('SITE_EMAIL', 'info@ecct.or.tz'));
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'admin@ecct.or.tz'));

// Debug Mode
define('DEBUG_MODE', env('DEBUG_MODE', false));

// File Paths
define('ASSETS_PATH', SITE_URL . '/assets');
define('UPLOADS_PATH', ECCT_ROOT . '/' . env('UPLOAD_PATH', 'assets/uploads'));
define('UPLOADS_URL', SITE_URL . '/' . env('UPLOAD_PATH', 'assets/uploads'));

// Security Configuration
define('SESSION_NAME', 'ECCT_SESSION');
define('SESSION_LIFETIME', env('SESSION_LIFETIME', 7200));
define('CSRF_TOKEN_NAME', 'ecct_token');
define('CSRF_SECRET', env('CSRF_SECRET', 'default_csrf_secret_change_in_production'));
define('HASH_ALGORITHM', 'sha256');
define('SECRET_KEY', env('SECRET_KEY', 'your_32_character_secret_key_here'));
define('ENCRYPTION_KEY', env('ENCRYPTION_KEY', 'your_encryption_key_here'));
define('JWT_SECRET', env('JWT_SECRET', 'your_jwt_secret_key_here'));

// Password Security
define('PASSWORD_MIN_LENGTH', env('PASSWORD_MIN_LENGTH', 8));
define('BCRYPT_ROUNDS', env('BCRYPT_ROUNDS', 12));
define('MAX_LOGIN_ATTEMPTS', env('MAX_LOGIN_ATTEMPTS', 5));
define('LOCKOUT_DURATION', env('LOCKOUT_DURATION', 1800));

// Rate Limiting
define('RATE_LIMIT_REQUESTS', env('RATE_LIMIT_REQUESTS', 100));
define('RATE_LIMIT_WINDOW', env('RATE_LIMIT_WINDOW', 3600));

// Image Upload Configuration
define('MAX_FILE_SIZE', env('MAX_FILE_SIZE', 5242880)); // 5MB
define('ALLOWED_IMAGE_TYPES', explode(',', env('ALLOWED_IMAGE_TYPES', 'jpg,jpeg,png,gif,webp,JPG,JPEG,PNG,GIF,WEBP')));
define('IMAGE_QUALITY', 85);

// Email Configuration
define('SMTP_HOST', env('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', env('SMTP_PORT', 587));
define('SMTP_USER', env('SMTP_USER', ''));
define('SMTP_PASS', env('SMTP_PASS', ''));
define('SMTP_FROM', env('SMTP_FROM', 'noreply@ecct.or.tz'));
define('SMTP_FROM_NAME', env('SMTP_FROM_NAME', 'ECCT'));

// Google reCAPTCHA
define('RECAPTCHA_SITE_KEY', env('RECAPTCHA_SITE_KEY', ''));
define('RECAPTCHA_SECRET_KEY', env('RECAPTCHA_SECRET_KEY', ''));

// Google Maps API
define('GOOGLE_MAPS_API_KEY', env('GOOGLE_MAPS_API_KEY', ''));

// Thumbnail Sizes
define('THUMB_SIZES', [
    'small' => ['width' => 150, 'height' => 150],
    'medium' => ['width' => 300, 'height' => 300],
    'large' => ['width' => 800, 'height' => 600]
]);

// Pagination
define('ITEMS_PER_PAGE', 12);
define('NEWS_PER_PAGE', 6);
define('GALLERY_PER_PAGE', 20);

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Error Reporting (Set to false in production)
if (!defined('DEBUG_MODE')) {
    define('DEBUG_MODE', true);
}

if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_start();
}

// CSRF Token Generation
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Helper function to get CSRF token
function get_csrf_token()
{
    return $_SESSION[CSRF_TOKEN_NAME] ?? '';
}

// Helper function to verify CSRF token
function verify_csrf_token($token)
{
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Helper function to generate secure random string
function generate_random_string($length = 32)
{
    return bin2hex(random_bytes($length / 2));
}

// Helper function to sanitize input
function sanitize_input($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to validate email
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to create SEO-friendly slug
function create_slug($string)
{
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Helper function to format date
function format_date($date, $format = 'M j, Y')
{
    return date($format, strtotime($date));
}

// Helper function to time ago
function time_ago($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' minutes ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2629743) return floor($time / 86400) . ' days ago';
    if ($time < 31556926) return floor($time / 2629743) . ' months ago';
    return floor($time / 31556926) . ' years ago';
}

// Helper function to truncate text
function truncate_text($text, $length = 150)
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Helper function to generate meta description
function generate_meta_description($content, $length = 160)
{
    $text = strip_tags($content);
    return truncate_text($text, $length);
}

// Helper function to get current URL
function current_url()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Helper function to redirect
function redirect($url, $permanent = false)
{
    if ($permanent) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $url);
    exit();
}

// Helper function to get user IP
function get_user_ip()
{
    $ip_keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ip_keys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Helper function to log activity
function log_activity($action, $description = '', $table_name = '', $record_id = null)
{
    if (!isset($_SESSION['admin_id'])) return false;

    require_once ECCT_ROOT . '/includes/database.php';
    $db = new Database();

    $data = [
        'user_id' => $_SESSION['admin_id'],
        'action' => $action,
        'description' => $description,
        'table_name' => $table_name,
        'record_id' => $record_id,
        'ip_address' => get_user_ip(),
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
    ];

    return $db->insert('activity_log', $data);
}

// Set global variables for templates
$GLOBALS['site_config'] = [
    'name' => SITE_NAME,
    'url' => SITE_URL,
    'email' => SITE_EMAIL,
    'assets_url' => ASSETS_PATH,
    'uploads_url' => UPLOADS_URL
];
