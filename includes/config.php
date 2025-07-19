<?php
/**
 * ECCT Website Configuration File
 * Environmental Conservation Community of Tanzania
 */

// Prevent direct access
if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', dirname(__FILE__, 2));
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'u750269652_ecct2025');
define('DB_USER', 'u750269652_ecctAdmin');
define('DB_PASS', ']R6yP;OW58Z');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'Environmental Conservation Community of Tanzania');
define('SITE_URL', 'https://ecct.serengetibytes.com'); // Change to your domain
define('SITE_EMAIL', 'info@ecct.or.tz');
define('ADMIN_EMAIL', 'admin@ecct.or.tz');

// File Paths
define('ASSETS_PATH', SITE_URL . '/assets');
define('UPLOADS_PATH', ECCT_ROOT . '/assets/uploads');
define('UPLOADS_URL', SITE_URL . '/assets/uploads');

// Security Configuration
define('SESSION_NAME', 'ECCT_SESSION');
define('CSRF_TOKEN_NAME', 'ecct_token');
define('HASH_ALGORITHM', 'sha256');
define('ENCRYPTION_KEY', 'your_32_character_secret_key_here'); // Change this!

// Image Upload Configuration
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('IMAGE_QUALITY', 85);

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

// Email Configuration (SMTP)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_app_password');
define('SMTP_ENCRYPTION', 'tls');

// Timezone
date_default_timezone_set('Africa/Dar_es_Salaam');

// Error Reporting (Set to false in production)
define('DEBUG_MODE', true);

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
function get_csrf_token() {
    return $_SESSION[CSRF_TOKEN_NAME] ?? '';
}

// Helper function to verify CSRF token
function verify_csrf_token($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

// Helper function to generate secure random string
function generate_random_string($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Helper function to sanitize input
function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Helper function to validate email
function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Helper function to create SEO-friendly slug
function create_slug($string) {
    $slug = strtolower(trim($string));
    $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    return trim($slug, '-');
}

// Helper function to format date
function format_date($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

// Helper function to time ago
function time_ago($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2629743) return floor($time/86400) . ' days ago';
    if ($time < 31556926) return floor($time/2629743) . ' months ago';
    return floor($time/31556926) . ' years ago';
}

// Helper function to truncate text
function truncate_text($text, $length = 150) {
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

// Helper function to generate meta description
function generate_meta_description($content, $length = 160) {
    $text = strip_tags($content);
    return truncate_text($text, $length);
}

// Helper function to get current URL
function current_url() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    return $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

// Helper function to redirect
function redirect($url, $permanent = false) {
    if ($permanent) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $url);
    exit();
}

// Helper function to get user IP
function get_user_ip() {
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
function log_activity($action, $description = '', $table_name = '', $record_id = null) {
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
?>