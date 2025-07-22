<?php

/**
 * General utility functions for ECCT Website
 */

if (!defined('ECCT_ROOT')) {
    die('Direct access not allowed');
}

/**
 * Send email function
 */
function send_email($to, $subject, $message, $from = null)
{
    $from = $from ?: ADMIN_EMAIL;

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . SITE_NAME . ' <' . $from . '>',
        'Reply-To: ' . $from,
        'X-Mailer: PHP/' . phpversion()
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}

/**
 * Validate email address
 */
function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Clean and validate phone number
 */
function clean_phone($phone)
{
    return preg_replace('/[^0-9+\-\s\(\)]/', '', $phone);
}

/**
 * Get recent content
 */
function get_recent_content($table, $limit = 5)
{
    $db = new Database();
    return $db->select($table, [], [
        'order_by' => 'created_at DESC',
        'limit' => $limit
    ]);
}

/**
 * Get content by slug
 */
function get_content_by_slug($table, $slug)
{
    $db = new Database();
    return $db->selectOne($table, ['slug' => $slug, 'is_published' => 1]);
}

/**
 * Generate excerpt from content
 */
function generate_excerpt($content, $length = 150)
{
    $content = strip_tags($content);
    if (strlen($content) <= $length) {
        return $content;
    }

    $excerpt = substr($content, 0, $length);
    $last_space = strrpos($excerpt, ' ');

    if ($last_space !== false) {
        $excerpt = substr($excerpt, 0, $last_space);
    }

    return $excerpt . '...';
}

/**
 * Upload file helper
 */
function upload_file($file, $upload_dir, $allowed_types = null, $max_size = null)
{
    $allowed_types = $allowed_types ?: ALLOWED_IMAGE_TYPES;
    $max_size = $max_size ?: MAX_FILE_SIZE;

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'File upload error: ' . $file['error']];
    }

    // Check file size
    if ($file['size'] > $max_size) {
        return ['error' => 'File too large. Maximum size: ' . format_filesize($max_size)];
    }

    // Check file type
    if (!in_array($file['type'], $allowed_types)) {
        return ['error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowed_types)];
    }

    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $upload_path,
            'size' => $file['size'],
            'type' => $file['type']
        ];
    }

    return ['error' => 'Failed to move uploaded file'];
}

/**
 * Delete file helper
 */
function delete_file($file_path)
{
    if (file_exists($file_path)) {
        return unlink($file_path);
    }
    return true;
}

/**
 * Resize image
 */
function resize_image($source_path, $destination_path, $max_width, $max_height = null, $quality = 85)
{
    $max_height = $max_height ?: $max_width;

    // Get image info
    $image_info = getimagesize($source_path);
    if (!$image_info) {
        return false;
    }

    list($orig_width, $orig_height, $image_type) = $image_info;

    // Calculate new dimensions
    $ratio = min($max_width / $orig_width, $max_height / $orig_height);
    $new_width = round($orig_width * $ratio);
    $new_height = round($orig_height * $ratio);

    // Create image resources
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($source_path);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($source_path);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }

    if (!$source) {
        return false;
    }

    // Create new image
    $destination = imagecreatetruecolor($new_width, $new_height);

    // Preserve transparency for PNG and GIF
    if ($image_type == IMAGETYPE_PNG || $image_type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $new_width, $new_height, $transparent);
    }

    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);

    // Save image
    $result = false;
    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($destination, $destination_path, $quality);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($destination, $destination_path);
            break;
        case IMAGETYPE_GIF:
            $result = imagegif($destination, $destination_path);
            break;
    }

    // Clean up
    imagedestroy($source);
    imagedestroy($destination);

    return $result;
}

/**
 * Generate thumbnail
 */
function generate_thumbnail($source_path, $thumb_path, $size = 300)
{
    return resize_image($source_path, $thumb_path, $size, $size);
}

/**
 * Send notification email for new volunteer
 */
function send_volunteer_notification($volunteer_data)
{
    $subject = 'New Volunteer Application - ' . $volunteer_data['first_name'] . ' ' . $volunteer_data['last_name'];

    $message = '
    <html>
    <head><title>New Volunteer Application</title></head>
    <body>
        <h2>New Volunteer Application Received</h2>
        <p><strong>Name:</strong> ' . htmlspecialchars($volunteer_data['first_name'] . ' ' . $volunteer_data['last_name']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($volunteer_data['email']) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($volunteer_data['phone']) . '</p>
        <p><strong>Age:</strong> ' . htmlspecialchars($volunteer_data['age']) . '</p>
        <p><strong>Location:</strong> ' . htmlspecialchars($volunteer_data['location']) . '</p>
        <p><strong>Interests:</strong><br>' . nl2br(htmlspecialchars($volunteer_data['interests'])) . '</p>
        <p><strong>Skills:</strong><br>' . nl2br(htmlspecialchars($volunteer_data['skills'])) . '</p>
        <p><a href="' . SITE_URL . '/admin/volunteers/view.php?id=' . $volunteer_data['id'] . '">View Application in Admin Panel</a></p>
    </body>
    </html>';

    return send_email(ADMIN_EMAIL, $subject, $message);
}

/**
 * Send notification email for new contact message
 */
function send_message_notification($message_data)
{
    $subject = 'New Contact Message - ' . ($message_data['subject'] ?: 'No Subject');

    $message = '
    <html>
    <head><title>New Contact Message</title></head>
    <body>
        <h2>New Contact Message Received</h2>
        <p><strong>From:</strong> ' . htmlspecialchars($message_data['name']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($message_data['email']) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($message_data['phone']) . '</p>
        <p><strong>Subject:</strong> ' . htmlspecialchars($message_data['subject'] ?: 'No Subject') . '</p>
        <p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message_data['message'])) . '</p>
        <p><a href="' . SITE_URL . '/admin/messages/view.php?id=' . $message_data['id'] . '">View Message in Admin Panel</a></p>
    </body>
    </html>';

    return send_email(ADMIN_EMAIL, $subject, $message);
}

/**
 * Validation functions
 */

/**
 * Validate form data
 */
function validate_form($data, $rules)
{
    $errors = [];

    foreach ($rules as $field => $rule_set) {
        $value = $data[$field] ?? '';

        // Required field validation
        if (isset($rule_set['required']) && $rule_set['required'] && empty($value)) {
            $errors[$field] = ($rule_set['label'] ?? $field) . ' is required';
            continue;
        }

        // Skip other validations if field is empty and not required
        if (empty($value)) continue;

        // Email validation
        if (isset($rule_set['email']) && $rule_set['email'] && !is_valid_email($value)) {
            $errors[$field] = 'Please enter a valid email address';
        }

        // Minimum length validation
        if (isset($rule_set['min_length']) && strlen($value) < $rule_set['min_length']) {
            $errors[$field] = ($rule_set['label'] ?? $field) . ' must be at least ' . $rule_set['min_length'] . ' characters';
        }

        // Maximum length validation
        if (isset($rule_set['max_length']) && strlen($value) > $rule_set['max_length']) {
            $errors[$field] = ($rule_set['label'] ?? $field) . ' cannot exceed ' . $rule_set['max_length'] . ' characters';
        }

        // Pattern validation
        if (isset($rule_set['pattern']) && !preg_match($rule_set['pattern'], $value)) {
            $errors[$field] = $rule_set['pattern_message'] ?? 'Invalid format for ' . ($rule_set['label'] ?? $field);
        }
    }

    return $errors;
}

/**
 * Clean HTML content (for WYSIWYG editors)
 */
function clean_html($html)
{
    // Allow basic HTML tags
    $allowed_tags = '<p><br><strong><b><em><i><u><h1><h2><h3><h4><h5><h6><ul><ol><li><a><img><blockquote><code><pre>';

    // Strip unwanted tags
    $html = strip_tags($html, $allowed_tags);

    // Remove dangerous attributes
    $html = preg_replace('/<([^>]*)\s(on\w+|style|script)=[^>]*>/i', '<$1>', $html);

    return $html;
}

/**
 * Rate limiting function
 */
function check_rate_limit($action, $limit = 5, $period = 300)
{ // 5 attempts per 5 minutes
    $ip = get_user_ip();
    $key = $action . '_' . $ip;

    if (!isset($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }

    $now = time();

    // Clean old entries
    foreach ($_SESSION['rate_limits'] as $k => $data) {
        if ($now - $data['time'] > $period) {
            unset($_SESSION['rate_limits'][$k]);
        }
    }

    // Count attempts
    $attempts = 0;
    foreach ($_SESSION['rate_limits'] as $k => $data) {
        if (strpos($k, $key) === 0) {
            $attempts++;
        }
    }

    if ($attempts >= $limit) {
        return false;
    }

    // Record this attempt
    $_SESSION['rate_limits'][$key . '_' . $now] = ['time' => $now];

    return true;
}

/**
 * Generate breadcrumbs
 */
function generate_breadcrumbs($pages)
{
    $breadcrumbs = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';

    foreach ($pages as $page) {
        if (isset($page['url'])) {
            $breadcrumbs .= '<li class="breadcrumb-item"><a href="' . $page['url'] . '">' . $page['title'] . '</a></li>';
        } else {
            $breadcrumbs .= '<li class="breadcrumb-item active" aria-current="page">' . $page['title'] . '</li>';
        }
    }

    $breadcrumbs .= '</ol></nav>';
    return $breadcrumbs;
}

/**
 * Get social media share links
 */
function get_share_links($url, $title, $description = '')
{
    $encoded_url = urlencode($url);
    $encoded_title = urlencode($title);
    $encoded_description = urlencode($description);

    return [
        'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$encoded_url}",
        'twitter' => "https://twitter.com/intent/tweet?url={$encoded_url}&text={$encoded_title}",
        'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$encoded_url}",
        'whatsapp' => "https://wa.me/?text={$encoded_title}%20{$encoded_url}",
        'email' => "mailto:?subject={$encoded_title}&body={$encoded_description}%0A%0A{$encoded_url}"
    ];
}

/**
 * Get site statistics
 */
function get_site_stats()
{
    $db = new Database();

    return [
        'total_news' => $db->count('news', ['is_published' => 1]),
        'total_campaigns' => $db->count('campaigns'),
        'total_gallery_images' => $db->count('gallery'),
        'total_volunteers' => $db->count('volunteers'),
        'pending_volunteers' => $db->count('volunteers', ['status' => 'pending']),
        'unread_messages' => $db->count('contact_messages', ['is_read' => 0])
    ];
}

/**
 * Cache management
 */

/**
 * Simple file-based caching
 */
function cache_get($key)
{
    if (!CACHE_ENABLED) return null;

    $cache_file = ECCT_ROOT . '/cache/' . md5($key) . '.cache';

    if (!file_exists($cache_file)) {
        return null;
    }

    $data = file_get_contents($cache_file);
    $data = unserialize($data);

    // Check if cache has expired
    if ($data['expires'] < time()) {
        unlink($cache_file);
        return null;
    }

    return $data['content'];
}

function cache_set($key, $content, $duration = null)
{
    if (!CACHE_ENABLED) return false;

    $duration = $duration ?: CACHE_LIFETIME;
    $cache_dir = ECCT_ROOT . '/cache/';

    if (!file_exists($cache_dir)) {
        mkdir($cache_dir, 0777, true);
    }

    $cache_file = $cache_dir . md5($key) . '.cache';
    $data = [
        'content' => $content,
        'expires' => time() + $duration
    ];

    return file_put_contents($cache_file, serialize($data)) !== false;
}

function cache_delete($key)
{
    $cache_file = ECCT_ROOT . '/cache/' . md5($key) . '.cache';
    if (file_exists($cache_file)) {
        return unlink($cache_file);
    }
    return true;
}

function cache_clear()
{
    $cache_dir = ECCT_ROOT . '/cache/';
    if (file_exists($cache_dir)) {
        $files = glob($cache_dir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    return false;
}
