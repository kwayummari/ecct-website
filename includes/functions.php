<?php

/**
 * Common Functions for ECCT Website
 * Utility functions used throughout the application
 */

require_once 'config.php';
require_once 'database.php';

/**
 * Image handling functions
 */

/**
 * Upload and process image
 */
function upload_image($file, $upload_dir, $allowed_types = null, $max_size = null)
{
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }

    $allowed_types = $allowed_types ?: ALLOWED_IMAGE_TYPES;
    $max_size = $max_size ?: MAX_FILE_SIZE;

    // Check file size
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File size too large'];
    }

    // Get file extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Check file type
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'Invalid file type'];
    }

    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $file_extension;
    $file_path = $upload_dir . '/' . $filename;

    // Create directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Optimize image
        optimize_image($file_path, $file_extension);

        return [
            'success' => true,
            'filename' => $filename,
            'path' => $file_path,
            'url' => str_replace(ECCT_ROOT, SITE_URL, $file_path)
        ];
    }

    return ['success' => false, 'message' => 'Failed to upload file'];
}

/**
 * Optimize image (reduce file size while maintaining quality)
 */
function optimize_image($file_path, $file_extension)
{
    $quality = IMAGE_QUALITY;

    switch ($file_extension) {
        case 'jpg':
        case 'jpeg':
            $image = imagecreatefromjpeg($file_path);
            if ($image) {
                imagejpeg($image, $file_path, $quality);
                imagedestroy($image);
            }
            break;

        case 'png':
            $image = imagecreatefrompng($file_path);
            if ($image) {
                imagepng($image, $file_path, floor($quality / 10));
                imagedestroy($image);
            }
            break;
    }
}

/**
 * Create thumbnail
 */
function create_thumbnail($source_path, $thumb_path, $width, $height, $crop = true)
{
    $image_info = getimagesize($source_path);
    if (!$image_info) return false;

    $src_width = $image_info[0];
    $src_height = $image_info[1];
    $mime_type = $image_info['mime'];

    // Create source image
    switch ($mime_type) {
        case 'image/jpeg':
            $src_image = imagecreatefromjpeg($source_path);
            break;
        case 'image/png':
            $src_image = imagecreatefrompng($source_path);
            break;
        case 'image/gif':
            $src_image = imagecreatefromgif($source_path);
            break;
        default:
            return false;
    }

    if (!$src_image) return false;

    // Calculate dimensions
    if ($crop) {
        $src_aspect = $src_width / $src_height;
        $thumb_aspect = $width / $height;

        if ($src_aspect > $thumb_aspect) {
            // Source is wider
            $new_width = $src_height * $thumb_aspect;
            $new_height = $src_height;
            $src_x = ($src_width - $new_width) / 2;
            $src_y = 0;
        } else {
            // Source is taller
            $new_width = $src_width;
            $new_height = $src_width / $thumb_aspect;
            $src_x = 0;
            $src_y = ($src_height - $new_height) / 2;
        }
    } else {
        // Resize without cropping
        $ratio = min($width / $src_width, $height / $src_height);
        $new_width = $src_width * $ratio;
        $new_height = $src_height * $ratio;
        $src_x = 0;
        $src_y = 0;
        $width = $new_width;
        $height = $new_height;
    }

    // Create thumbnail
    $thumb_image = imagecreatetruecolor($width, $height);

    // Preserve transparency for PNG and GIF
    if ($mime_type == 'image/png' || $mime_type == 'image/gif') {
        imagealphablending($thumb_image, false);
        imagesavealpha($thumb_image, true);
        $transparent = imagecolorallocatealpha($thumb_image, 255, 255, 255, 127);
        imagefill($thumb_image, 0, 0, $transparent);
    }

    imagecopyresampled(
        $thumb_image,
        $src_image,
        0,
        0,
        $src_x,
        $src_y,
        $width,
        $height,
        $crop ? $new_width : $src_width,
        $crop ? $new_height : $src_height
    );

    // Create directory if it doesn't exist
    $thumb_dir = dirname($thumb_path);
    if (!is_dir($thumb_dir)) {
        mkdir($thumb_dir, 0755, true);
    }

    // Save thumbnail
    $success = false;
    switch ($mime_type) {
        case 'image/jpeg':
            $success = imagejpeg($thumb_image, $thumb_path, IMAGE_QUALITY);
            break;
        case 'image/png':
            $success = imagepng($thumb_image, $thumb_path);
            break;
        case 'image/gif':
            $success = imagegif($thumb_image, $thumb_path);
            break;
    }

    imagedestroy($src_image);
    imagedestroy($thumb_image);

    return $success;
}

/**
 * Delete image and its thumbnails
 */
function delete_image($image_path)
{
    if (file_exists($image_path)) {
        unlink($image_path);
    }

    // Delete thumbnails
    $path_info = pathinfo($image_path);
    $thumbnail_dir = $path_info['dirname'] . '/thumbs';
    $filename_without_ext = $path_info['filename'];

    foreach (THUMB_SIZES as $size => $dimensions) {
        $thumb_path = $thumbnail_dir . '/' . $filename_without_ext . '_' . $size . '.' . $path_info['extension'];
        if (file_exists($thumb_path)) {
            unlink($thumb_path);
        }
    }
}

/**
 * Content management functions
 */

/**
 * Get content by slug
 */
function get_content_by_slug($table, $slug)
{
    $db = new Database();
    return $db->selectOne($table, ['slug' => $slug, 'is_published' => 1]);
}

/**
 * Get recent content
 */
function get_recent_content($table, $limit = 5)
{
    $db = new Database();
    return $db->getPublishedContent($table, [
        'order_by' => 'created_at DESC',
        'limit' => $limit
    ]);
}

/**
 * Get featured content
 */
function get_featured_content($table, $limit = 3)
{
    $db = new Database();
    return $db->getPublishedContent($table, [
        'conditions' => ['is_featured' => 1],
        'order_by' => 'created_at DESC',
        'limit' => $limit
    ]);
}

/**
 * Search content
 */
function search_content($query, $tables = ['news', 'campaigns', 'pages'])
{
    $db = new Database();
    $results = [];

    foreach ($tables as $table) {
        $search_columns = ['title', 'content'];
        if ($table === 'news') {
            $search_columns[] = 'excerpt';
        }
        if ($table === 'campaigns') {
            $search_columns[] = 'description';
        }

        $content = $db->search(
            $table,
            $query,
            $search_columns,
            ['is_published' => 1],
            ['order_by' => 'created_at DESC']
        );

        if ($content) {
            foreach ($content as $item) {
                $item['content_type'] = $table;
                $results[] = $item;
            }
        }
    }

    return $results;
}

/**
 * Email functions
 */

/**
 * Send email using PHP mail or SMTP
 */
function send_email($to, $subject, $message, $from = null, $headers = [])
{
    $from = $from ?: SITE_EMAIL;

    // Basic headers
    $email_headers = [
        'From' => $from,
        'Reply-To' => $from,
        'Content-Type' => 'text/html; charset=UTF-8',
        'X-Mailer' => 'ECCT Website'
    ];

    // Merge custom headers
    $email_headers = array_merge($email_headers, $headers);

    // Convert headers to string
    $header_string = '';
    foreach ($email_headers as $key => $value) {
        $header_string .= $key . ': ' . $value . "\r\n";
    }

    // Send email
    return mail($to, $subject, $message, $header_string);
}

/**
 * Send contact form email
 */
function send_contact_email($form_data)
{
    $subject = 'New Contact Form Submission - ' . $form_data['subject'];

    $message = '
    <html>
    <head>
        <title>Contact Form Submission</title>
    </head>
    <body>
        <h2>New Contact Form Submission</h2>
        <p><strong>Name:</strong> ' . htmlspecialchars($form_data['name']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($form_data['email']) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($form_data['phone'] ?? 'Not provided') . '</p>
        <p><strong>Subject:</strong> ' . htmlspecialchars($form_data['subject']) . '</p>
        <p><strong>Message:</strong></p>
        <div style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
            ' . nl2br(htmlspecialchars($form_data['message'])) . '
        </div>
        <hr>
        <p><small>This email was sent from the ECCT website contact form.</small></p>
    </body>
    </html>';

    return send_email(ADMIN_EMAIL, $subject, $message, $form_data['email']);
}

/**
 * Send volunteer application email
 */
function send_volunteer_notification($volunteer_data)
{
    $subject = 'New Volunteer Application - ' . $volunteer_data['first_name'] . ' ' . $volunteer_data['last_name'];

    $message = '
    <html>
    <head>
        <title>New Volunteer Application</title>
    </head>
    <body>
        <h2>New Volunteer Application</h2>
        <p><strong>Name:</strong> ' . htmlspecialchars($volunteer_data['first_name'] . ' ' . $volunteer_data['last_name']) . '</p>
        <p><strong>Email:</strong> ' . htmlspecialchars($volunteer_data['email']) . '</p>
        <p><strong>Phone:</strong> ' . htmlspecialchars($volunteer_data['phone']) . '</p>
        <p><strong>Areas of Interest:</strong> ' . htmlspecialchars($volunteer_data['areas_of_interest']) . '</p>
        <p><strong>Motivation:</strong></p>
        <div style="border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9;">
            ' . nl2br(htmlspecialchars($volunteer_data['motivation'])) . '
        </div>
        <p><a href="' . SITE_URL . '/admin/volunteers/manage-volunteers.php">View Application in Admin Panel</a></p>
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
 * Security functions
 */

/**
 * Generate and validate CSRF token for forms
 */
function csrf_field()
{
    return '<input type="hidden" name="csrf_token" value="' . get_csrf_token() . '">';
}

/**
 * Validate CSRF token
 */
function validate_csrf()
{
    $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    return verify_csrf_token($token);
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
    $_SESSION['rate_limits'] = array_filter($_SESSION['rate_limits'], function ($data) use ($now, $period) {
        return ($now - $data['time']) < $period;
    });

    // Count attempts for this action/IP
    $attempts = array_filter($_SESSION['rate_limits'], function ($data) use ($key) {
        return $data['key'] === $key;
    });

    if (count($attempts) >= $limit) {
        return false; // Rate limit exceeded
    }

    // Record this attempt
    $_SESSION['rate_limits'][] = [
        'key' => $key,
        'time' => $now
    ];

    return true;
}

/**
 * Utility functions
 */

/**
 * Format file size
 */
function format_file_size($bytes)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);

    return round($bytes, 2) . ' ' . $units[$pow];
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
 * Content management helpers
 */

/**
 * Get page menu items
 */
function get_menu_items()
{
    $db = new Database();
    return $db->getPublishedContent('pages', [
        'order_by' => 'sort_order ASC, title ASC'
    ]);
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
 * Get recent activity for dashboard
 */
function get_recent_activity($limit = 10)
{
    $db = new Database();

    $sql = "
        SELECT al.*, au.full_name, au.username
        FROM activity_log al
        LEFT JOIN admin_users au ON al.user_id = au.id
        ORDER BY al.created_at DESC
        LIMIT ?
    ";

    $stmt = $db->raw($sql, [$limit]);
    return $stmt ? $stmt->fetchAll() : [];
}

/**
 * Cache management
 */

/**
 * Simple file-based caching
 */
function cache_get($key)
{
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

function cache_set($key, $content, $duration = 3600)
{
    $cache_dir = ECCT_ROOT . '/cache';
    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
    }

    $cache_file = $cache_dir . '/' . md5($key) . '.cache';

    $data = [
        'content' => $content,
        'expires' => time() + $duration
    ];

    return file_put_contents($cache_file, serialize($data));
}

function cache_clear($key = null)
{
    $cache_dir = ECCT_ROOT . '/cache';

    if ($key) {
        $cache_file = $cache_dir . '/' . md5($key) . '.cache';
        if (file_exists($cache_file)) {
            unlink($cache_file);
        }
    } else {
        // Clear all cache files
        $files = glob($cache_dir . '/*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

/**
 * Debug helper function
 */
function debug($data, $die = false)
{
    if (DEBUG_MODE) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';

        if ($die) {
            die();
        }
    }
}

/**
 * Flash message functions
 */
function set_flash($type, $message)
{
    $_SESSION['flash'][$type] = $message;
}

function get_flash($type = null)
{
    if ($type) {
        $message = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $message;
    }

    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function has_flash($type = null)
{
    if ($type) {
        return isset($_SESSION['flash'][$type]);
    }
    return !empty($_SESSION['flash']);
}


/**
 * Send volunteer confirmation email to applicant
 */
function send_volunteer_confirmation_email($form_data)
{
    $subject = 'Thank you for volunteering with ECCT!';

    $message = '
    <html>
    <head>
        <title>Volunteer Application Confirmation</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <img src="' . SITE_URL . '/assets/images/logo.jpg" alt="ECCT" style="max-height: 60px;">
                <h1 style="color: #2c5f2d; margin-top: 10px;">Welcome to ECCT!</h1>
            </div>
            
            <p>Dear ' . htmlspecialchars($form_data['first_name']) . ',</p>
            
            <p>Thank you for your interest in volunteering with the Environmental Conservation Community of Tanzania (ECCT)!</p>
            
            <p>We have received your volunteer application and our team will review it carefully. Here\'s what happens next:</p>
            
            <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="color: #2c5f2d; margin-top: 0;">Next Steps:</h3>
                <ul>
                    <li><strong>Review Process:</strong> We will review your application within 5-7 business days</li>
                    <li><strong>Background Check:</strong> We may contact your references if provided</li>
                    <li><strong>Orientation:</strong> Approved volunteers will be invited to an orientation session</li>
                    <li><strong>Placement:</strong> We\'ll match you with opportunities based on your interests and availability</li>
                </ul>
            </div>
            
            <p><strong>Your Application Summary:</strong></p>
            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background-color: #f9f9f9;">
                <p><strong>Areas of Interest:</strong> ' . htmlspecialchars($form_data['areas_of_interest']) . '</p>
                <p><strong>Availability:</strong> ' . htmlspecialchars($form_data['availability']) . '</p>
                <p><strong>Location:</strong> ' . htmlspecialchars($form_data['city']) . ', ' . htmlspecialchars($form_data['region']) . '</p>
            </div>
            
            <p>In the meantime, you can:</p>
            <ul>
                <li>Follow us on social media for updates on our activities</li>
                <li>Visit our website to learn more about our current campaigns</li>
                <li>Share our mission with friends and family</li>
            </ul>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="' . SITE_URL . '" style="background-color: #2c5f2d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">Visit Our Website</a>
            </div>
            
            <p>If you have any questions, please don\'t hesitate to contact us at <a href="mailto:' . SITE_EMAIL . '">' . SITE_EMAIL . '</a></p>
            
            <p>Thank you for your commitment to environmental conservation!</p>
            
            <p style="margin-top: 30px;">
                Best regards,<br>
                <strong>ECCT Team</strong><br>
                Environmental Conservation Community of Tanzania
            </p>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
            <p style="font-size: 12px; color: #666; text-align: center;">
                This email was sent from the ECCT volunteer application system.<br>
                If you did not apply to volunteer with us, please ignore this email.
            </p>
        </div>
    </body>
    </html>';

    return send_email($form_data['email'], $subject, $message);
}

/**
 * Send admin notification for new volunteer application
 */
function send_admin_notification($subject_prefix, $message_text, $additional_data = [])
{
    $subject = $subject_prefix . ' - ECCT Website';

    $message = '
    <html>
    <head>
        <title>' . htmlspecialchars($subject) . '</title>
    </head>
    <body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
        <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
            <div style="background-color: #2c5f2d; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0;">
                <h1 style="margin: 0;">ECCT Admin Notification</h1>
            </div>
            
            <div style="border: 1px solid #ddd; border-top: none; padding: 20px; border-radius: 0 0 5px 5px;">
                <h2 style="color: #2c5f2d; margin-top: 0;">' . htmlspecialchars($subject_prefix) . '</h2>
                
                <p>' . htmlspecialchars($message_text) . '</p>
                
                ' . (!empty($additional_data) ? '<div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
                    <h3 style="margin-top: 0; color: #2c5f2d;">Additional Details:</h3>' .
        implode('', array_map(function ($key, $value) {
            return '<p><strong>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) . ':</strong> ' . htmlspecialchars($value) . '</p>';
        }, array_keys($additional_data), $additional_data)) .
        '</div>' : '') . '
                
                <div style="text-align: center; margin: 30px 0;">
                    <a href="' . SITE_URL . '/admin/" style="background-color: #2c5f2d; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;">
                        Go to Admin Panel
                    </a>
                </div>
                
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
                <p style="font-size: 12px; color: #666;">
                    <strong>Timestamp:</strong> ' . date('Y-m-d H:i:s') . '<br>
                    <strong>IP Address:</strong> ' . get_user_ip() . '<br>
                    <strong>User Agent:</strong> ' . htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . '
                </p>
            </div>
        </div>
    </body>
    </html>';

    return send_email(ADMIN_EMAIL, $subject, $message);
}