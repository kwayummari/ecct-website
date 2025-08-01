<?php

/**
 * Admin Helper Functions for ECCT
 * Provides utility functions that don't conflict with main site functions
 */

if (!defined('ECCT_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Get current admin user (safe wrapper)
 */
function get_admin_user()
{
    if (!is_logged_in()) {
        return null;
    }

    $db = new Database();
    $user = $db->selectOne('admin_users', ['id' => $_SESSION['admin_user_id']]);

    if (!$user) {
        return null;
    }

    // Add session data to user array
    $user['session_name'] = $_SESSION['admin_name'] ?? $user['full_name'] ?? $user['name'] ?? 'Admin';
    $user['session_role'] = $_SESSION['admin_role'] ?? $user['role'] ?? 'editor';

    return $user;
}

/**
 * Check admin permissions safely
 */
function admin_can($permission)
{
    $user = get_admin_user();
    if (!$user) {
        return false;
    }

    $role = $user['session_role'] ?? $user['role'] ?? 'editor';

    // Super admin can do everything
    if ($role === 'super_admin') {
        return true;
    }

    // Define permissions by role
    $permissions = [
        'admin' => [
            'manage_news',
            'manage_campaigns',
            'manage_volunteers',
            'manage_messages',
            'manage_gallery',
            'manage_pages',
            'manage_settings',
            'view_analytics'
        ],
        'editor' => [
            'manage_news',
            'manage_campaigns',
            'manage_gallery',
            'manage_pages'
        ],
        'moderator' => [
            'manage_volunteers',
            'manage_messages'
        ]
    ];

    $user_permissions = $permissions[$role] ?? [];
    return in_array($permission, $user_permissions);
}

/**
 * Require admin permission
 */
function admin_require($permission)
{
    if (!admin_can($permission)) {
        http_response_code(403);
        die('Access denied. You do not have permission to access this resource.');
    }
}

/**
 * Get admin navigation items based on permissions
 */
function get_admin_nav()
{
    $user = get_admin_user();
    if (!$user) {
        return [];
    }

    $nav = [
        'dashboard' => [
            'title' => 'Dashboard',
            'icon' => 'tachometer-alt',
            'url' => 'index.php'
        ]
    ];

    if (admin_can('manage_news')) {
        $nav['news'] = [
            'title' => 'News & Articles',
            'icon' => 'newspaper',
            'url' => 'news/',
            'submenu' => [
                'all' => ['title' => 'All Articles', 'url' => 'news/'],
                'add' => ['title' => 'Add New', 'url' => 'news/add.php']
            ]
        ];
    }

    if (admin_can('manage_campaigns')) {
        $nav['campaigns'] = [
            'title' => 'Campaigns',
            'icon' => 'bullhorn',
            'url' => 'campaigns/',
            'submenu' => [
                'all' => ['title' => 'All Campaigns', 'url' => 'campaigns/'],
                'add' => ['title' => 'Add New', 'url' => 'campaigns/add.php']
            ]
        ];
    }

    if (admin_can('manage_gallery')) {
        $nav['gallery'] = [
            'title' => 'Gallery',
            'icon' => 'images',
            'url' => 'gallery/',
            'submenu' => [
                'all' => ['title' => 'All Images', 'url' => 'gallery/'],
                'upload' => ['title' => 'Upload New', 'url' => 'gallery/upload.php']
            ]
        ];
    }

    if (admin_can('manage_volunteers')) {
        $nav['volunteers'] = [
            'title' => 'Volunteers',
            'icon' => 'users',
            'url' => 'volunteers/'
        ];
    }

    if (admin_can('manage_messages')) {
        $nav['messages'] = [
            'title' => 'Messages',
            'icon' => 'envelope',
            'url' => 'messages/'
        ];
    }

    if (admin_can('manage_pages')) {
        $nav['pages'] = [
            'title' => 'Pages',
            'icon' => 'file-alt',
            'url' => 'pages/'
        ];
    }

    if (admin_can('manage_settings')) {
        $nav['settings'] = [
            'title' => 'Settings',
            'icon' => 'cog',
            'url' => 'settings/',
            'submenu' => [
                'general' => ['title' => 'General', 'url' => 'settings/'],
                'users' => ['title' => 'Users', 'url' => 'settings/users.php'],
                'email' => ['title' => 'Email', 'url' => 'settings/email.php']
            ]
        ];
    }

    return $nav;
}

/**
 * Generate breadcrumbs
 */
function admin_breadcrumbs($items = [])
{
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => SITE_URL . '/admin/']
    ];

    foreach ($items as $item) {
        $breadcrumbs[] = $item;
    }

    return $breadcrumbs;
}

/**
 * Format admin date
 */
function admin_date($date, $format = 'M j, Y g:i A')
{
    return date($format, strtotime($date));
}

/**
 * Admin time ago function
 */
function admin_time_ago($datetime)
{
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time / 60) . ' min ago';
    if ($time < 86400) return floor($time / 3600) . ' hours ago';
    if ($time < 2592000) return floor($time / 86400) . ' days ago';
    if ($time < 31536000) return floor($time / 2592000) . ' months ago';

    return floor($time / 31536000) . ' years ago';
}

/**
 * Get admin status badge
 */
function admin_status_badge($status, $published_field = 'is_published')
{
    switch ($status) {
        case 1:
        case 'active':
        case 'published':
            return '<span class="badge bg-success">Active</span>';
        case 0:
        case 'inactive':
        case 'draft':
            return '<span class="badge bg-warning">Draft</span>';
        case 'pending':
            return '<span class="badge bg-info">Pending</span>';
        case 'archived':
            return '<span class="badge bg-secondary">Archived</span>';
        default:
            return '<span class="badge bg-light text-dark">' . ucfirst($status) . '</span>';
    }
}

/**
 * Handle image upload for gallery
 */
function handle_image_upload($file, $folder = 'gallery')
{
    // Validate file
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'error' => 'No file uploaded'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload error: ' . $file['error']];
    }

    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File too large. Maximum size: ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB'];
    }

    // Check file type and extension
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_extension_lower = strtolower($file_extension);
    
    // Define allowed MIME types and extensions
    $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'JPG', 'JPEG', 'PNG', 'GIF'];
    
    // Check either MIME type OR file extension (more flexible)
    $valid_mime = in_array($mime_type, $allowed_mime_types);
    $valid_extension = in_array($file_extension, $allowed_extensions) || in_array($file_extension_lower, ['jpg', 'jpeg', 'png', 'gif']);
    
    if (!$valid_mime && !$valid_extension) {
        return ['success' => false, 'error' => 'Invalid file type. Allowed: JPG, JPEG, PNG, GIF (case insensitive)'];
    }

    // Create upload directories
    $upload_dir = ECCT_ROOT . '/assets/uploads/' . $folder;
    $thumb_dir = $upload_dir . '/thumbs';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    if (!is_dir($thumb_dir)) {
        mkdir($thumb_dir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $image_path = 'assets/uploads/' . $folder . '/' . $filename;
    $thumbnail_path = 'assets/uploads/' . $folder . '/thumbs/' . $filename;

    $full_image_path = ECCT_ROOT . '/' . $image_path;
    $full_thumbnail_path = ECCT_ROOT . '/' . $thumbnail_path;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $full_image_path)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file'];
    }

    // Create thumbnail
    if (!create_thumbnail($full_image_path, $full_thumbnail_path, 300, 300)) {
        // If thumbnail creation fails, still consider upload successful
        $thumbnail_path = $image_path; // Use original image as thumbnail
    }

    return [
        'success' => true,
        'image_path' => $image_path,
        'thumbnail_path' => $thumbnail_path,
        'filename' => $filename
    ];
}

/**
 * Create thumbnail from image
 */
function create_thumbnail($source, $destination, $max_width = 300, $max_height = 300)
{
    if (!file_exists($source)) {
        return false;
    }

    // Get image info
    $image_info = getimagesize($source);
    if (!$image_info) {
        return false;
    }

    $width = $image_info[0];
    $height = $image_info[1];
    $type = $image_info[2];

    // Calculate new dimensions
    $ratio = min($max_width / $width, $max_height / $height);
    $new_width = round($width * $ratio);
    $new_height = round($height * $ratio);

    // Create image resource based on type
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source_image = imagecreatefromjpeg($source);
            break;
        case IMAGETYPE_PNG:
            $source_image = imagecreatefrompng($source);
            break;
        case IMAGETYPE_GIF:
            $source_image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }

    if (!$source_image) {
        return false;
    }

    // Create thumbnail
    $thumbnail = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        $transparent = imagecolorallocatealpha($thumbnail, 255, 255, 255, 127);
        imagefill($thumbnail, 0, 0, $transparent);
    }

    // Resize image
    imagecopyresampled($thumbnail, $source_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Save thumbnail
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($thumbnail, $destination, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($thumbnail, $destination, 8);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($thumbnail, $destination);
            break;
    }

    // Clean up
    imagedestroy($source_image);
    imagedestroy($thumbnail);

    return $result;
}
