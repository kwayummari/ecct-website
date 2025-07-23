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
