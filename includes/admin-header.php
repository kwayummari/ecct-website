<?php

/**
 * Admin Panel Header Template with Sidebar for ECCT
 */

// Ensure we have the necessary includes
if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', dirname(__FILE__, 2));
}

require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';

// Require login for all admin pages
require_login();

// Get current user info
$current_user = get_current_user();

// Default page variables if not set
$page_title = $page_title ?? 'ECCT Admin Panel';
$page_class = $page_class ?? '';

// Get database instance for notifications
$db = new Database();

// Get notification counts
$notifications = [
    'unread_messages' => $db->count('contact_messages', ['is_read' => 0]),
    'pending_volunteers' => $db->count('volunteers', ['status' => 'pending']),
    'draft_news' => $db->count('news', ['is_published' => 0])
];

// Get current page info for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Stylesheets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?php echo ASSETS_PATH; ?>/css/admin.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body class="admin-panel <?php echo $page_class; ?>">

    <div class="admin-wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <img src="<?php echo SITE_URL; ?>/assets/images/logo.png" alt="ECCT" height="40">
                    <span class="brand-text">ECCT Admin</span>
                </div>
                <button class="sidebar-toggle d-lg-none" type="button" id="sidebarToggle">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="sidebar-content">
                <div class="sidebar-user">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($current_user['name']); ?></div>
                        <div class="user-role"><?php echo ucfirst($current_user['role']); ?></div>
                    </div>
                </div>

                <ul class="sidebar-menu">
                    <!-- Dashboard -->
                    <li class="menu-item <?php echo ($current_page === 'index.php' && $current_dir === 'admin') ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/" class="menu-link">
                            <i class="menu-icon fas fa-tachometer-alt"></i>
                            <span class="menu-text">Dashboard</span>
                        </a>
                    </li>

                    <!-- Content Management -->
                    <li class="menu-item <?php echo in_array($current_dir, ['pages']) ? 'active' : ''; ?>">
                        <a href="#" class="menu-link has-submenu" data-bs-toggle="collapse" data-bs-target="#pagesMenu">
                            <i class="menu-icon fas fa-file-alt"></i>
                            <span class="menu-text">Pages</span>
                            <i class="submenu-arrow fas fa-chevron-right"></i>
                        </a>
                        <ul class="submenu collapse <?php echo ($current_dir === 'pages') ? 'show' : ''; ?>" id="pagesMenu">
                            <li><a href="<?php echo SITE_URL; ?>/admin/pages/manage-pages.php" class="submenu-link">
                                    <i class="fas fa-list"></i>Manage Pages
                                </a></li>
                            <li><a href="<?php echo SITE_URL; ?>/admin/pages/add-page.php" class="submenu-link">
                                    <i class="fas fa-plus"></i>Add Page
                                </a></li>
                        </ul>
                    </li>

                    <!-- News Management -->
                    <li class="menu-item <?php echo in_array($current_dir, ['news']) ? 'active' : ''; ?>">
                        <a href="#" class="menu-link has-submenu" data-bs-toggle="collapse" data-bs-target="#newsMenu">
                            <i class="menu-icon fas fa-newspaper"></i>
                            <span class="menu-text">News</span>
                            <?php if ($notifications['draft_news'] > 0): ?>
                                <span class="notification-badge"><?php echo $notifications['draft_news']; ?></span>
                            <?php endif; ?>
                            <i class="submenu-arrow fas fa-chevron-right"></i>
                        </a>
                        <ul class="submenu collapse <?php echo ($current_dir === 'news') ? 'show' : ''; ?>" id="newsMenu">
                            <li><a href="<?php echo SITE_URL; ?>/admin/news/manage-news.php" class="submenu-link">
                                    <i class="fas fa-list"></i>Manage Articles
                                </a></li>
                            <li><a href="<?php echo SITE_URL; ?>/admin/news/add-news.php" class="submenu-link">
                                    <i class="fas fa-plus"></i>Add Article
                                </a></li>
                        </ul>
                    </li>

                    <!-- Campaigns Management -->
                    <li class="menu-item <?php echo in_array($current_dir, ['campaigns']) ? 'active' : ''; ?>">
                        <a href="#" class="menu-link has-submenu" data-bs-toggle="collapse" data-bs-target="#campaignsMenu">
                            <i class="menu-icon fas fa-bullhorn"></i>
                            <span class="menu-text">Campaigns</span>
                            <i class="submenu-arrow fas fa-chevron-right"></i>
                        </a>
                        <ul class="submenu collapse <?php echo ($current_dir === 'campaigns') ? 'show' : ''; ?>" id="campaignsMenu">
                            <li><a href="<?php echo SITE_URL; ?>/admin/campaigns/manage-campaigns.php" class="submenu-link">
                                    <i class="fas fa-list"></i>Manage Campaigns
                                </a></li>
                            <li><a href="<?php echo SITE_URL; ?>/admin/campaigns/add-campaign.php" class="submenu-link">
                                    <i class="fas fa-plus"></i>Add Campaign
                                </a></li>
                        </ul>
                    </li>

                    <!-- Gallery Management -->
                    <li class="menu-item <?php echo in_array($current_dir, ['gallery']) ? 'active' : ''; ?>">
                        <a href="#" class="menu-link has-submenu" data-bs-toggle="collapse" data-bs-target="#galleryMenu">
                            <i class="menu-icon fas fa-images"></i>
                            <span class="menu-text">Gallery</span>
                            <i class="submenu-arrow fas fa-chevron-right"></i>
                        </a>
                        <ul class="submenu collapse <?php echo ($current_dir === 'gallery') ? 'show' : ''; ?>" id="galleryMenu">
                            <li><a href="<?php echo SITE_URL; ?>/admin/gallery/manage-gallery.php" class="submenu-link">
                                    <i class="fas fa-list"></i>Manage Images
                                </a></li>
                            <li><a href="<?php echo SITE_URL; ?>/admin/gallery/upload-images.php" class="submenu-link">
                                    <i class="fas fa-upload"></i>Upload Images
                                </a></li>
                        </ul>
                    </li>

                    <!-- Volunteers -->
                    <li class="menu-item <?php echo in_array($current_dir, ['volunteers']) ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/volunteers/manage-volunteers.php" class="menu-link">
                            <i class="menu-icon fas fa-users"></i>
                            <span class="menu-text">Volunteers</span>
                            <?php if ($notifications['pending_volunteers'] > 0): ?>
                                <span class="notification-badge"><?php echo $notifications['pending_volunteers']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Messages -->
                    <li class="menu-item <?php echo in_array($current_dir, ['contact']) ? 'active' : ''; ?>">
                        <a href="<?php echo SITE_URL; ?>/admin/contact/manage-messages.php" class="menu-link">
                            <i class="menu-icon fas fa-envelope"></i>
                            <span class="menu-text">Messages</span>
                            <?php if ($notifications['unread_messages'] > 0): ?>
                                <span class="notification-badge notification-danger"><?php echo $notifications['unread_messages']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Divider -->
                    <li class="menu-divider"></li>

                    <!-- Settings (Admin only) -->
                    <?php if (has_role('admin')): ?>
                        <li class="menu-item <?php echo in_array($current_dir, ['settings']) ? 'active' : ''; ?>">
                            <a href="#" class="menu-link has-submenu" data-bs-toggle="collapse" data-bs-target="#settingsMenu">
                                <i class="menu-icon fas fa-cog"></i>
                                <span class="menu-text">Settings</span>
                                <i class="submenu-arrow fas fa-chevron-right"></i>
                            </a>
                            <ul class="submenu collapse <?php echo ($current_dir === 'settings') ? 'show' : ''; ?>" id="settingsMenu">
                                <li><a href="<?php echo SITE_URL; ?>/admin/settings/site-settings.php" class="submenu-link">
                                        <i class="fas fa-globe"></i>Site Settings
                                    </a></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin/settings/user-management.php" class="submenu-link">
                                        <i class="fas fa-users-cog"></i>User Management
                                    </a></li>
                                <li><a href="<?php echo SITE_URL; ?>/admin/settings/backup.php" class="submenu-link">
                                        <i class="fas fa-download"></i>Backup Database
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Analytics (if user has permission) -->
                    <?php if (can_perform('view_analytics')): ?>
                        <li class="menu-item">
                            <a href="<?php echo SITE_URL; ?>/admin/analytics/" class="menu-link">
                                <i class="menu-icon fas fa-chart-bar"></i>
                                <span class="menu-text">Analytics</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>

                <!-- Bottom Menu -->
                <div class="sidebar-bottom">
                    <ul class="sidebar-menu">
                        <li class="menu-item">
                            <a href="<?php echo SITE_URL; ?>" target="_blank" class="menu-link">
                                <i class="menu-icon fas fa-external-link-alt"></i>
                                <span class="menu-text">View Website</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo SITE_URL; ?>/admin/profile.php" class="menu-link">
                                <i class="menu-icon fas fa-user"></i>
                                <span class="menu-text">My Profile</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a href="<?php echo SITE_URL; ?>/admin/logout.php" class="menu-link text-danger">
                                <i class="menu-icon fas fa-sign-out-alt"></i>
                                <span class="menu-text">Logout</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Top Header -->
            <header class="top-header">
                <div class="header-left">
                    <button class="sidebar-toggle d-lg-none" type="button" id="mobileToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <button class="sidebar-collapse d-none d-lg-block" type="button" id="sidebarCollapse">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>

                <div class="header-right">
                    <!-- Notifications -->
                    <div class="header-notifications">
                        <?php if ($notifications['unread_messages'] > 0): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/contact/manage-messages.php" class="notification-item" title="Unread Messages">
                                <i class="fas fa-envelope"></i>
                                <span class="notification-count"><?php echo $notifications['unread_messages']; ?></span>
                            </a>
                        <?php endif; ?>

                        <?php if ($notifications['pending_volunteers'] > 0): ?>
                            <a href="<?php echo SITE_URL; ?>/admin/volunteers/manage-volunteers.php" class="notification-item" title="Pending Volunteers">
                                <i class="fas fa-users"></i>
                                <span class="notification-count"><?php echo $notifications['pending_volunteers']; ?></span>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="user-dropdown" type="button" data-bs-toggle="dropdown">
                            <div class="user-avatar-small">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="user-name d-none d-md-inline"><?php echo htmlspecialchars($current_user['name']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <div class="fw-bold"><?php echo htmlspecialchars($current_user['name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($current_user['email']); ?></small>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/profile.php">
                                    <i class="fas fa-user me-2"></i>My Profile
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/change-password.php">
                                    <i class="fas fa-key me-2"></i>Change Password
                                </a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/admin/logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Content Area -->
            <div class="content-area">
                <!-- Breadcrumb Navigation -->
                <?php if (isset($breadcrumbs)): ?>
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>/admin/"><i class="fas fa-home"></i></a>
                            </li>
                            <?php foreach ($breadcrumbs as $crumb): ?>
                                <?php if (isset($crumb['url'])): ?>
                                    <li class="breadcrumb-item">
                                        <a href="<?php echo $crumb['url']; ?>"><?php echo htmlspecialchars($crumb['title']); ?></a>
                                    </li>
                                <?php else: ?>
                                    <li class="breadcrumb-item active" aria-current="page">
                                        <?php echo htmlspecialchars($crumb['title']); ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ol>
                    </nav>
                <?php endif; ?>

                <!-- Flash Messages -->
                <?php if (has_flash()): ?>
                    <div class="flash-messages mb-4">
                        <?php
                        $flash_messages = get_flash();
                        foreach ($flash_messages as $type => $message):
                            $alert_class = match ($type) {
                                'success' => 'alert-success',
                                'error' => 'alert-danger',
                                'warning' => 'alert-warning',
                                'info' => 'alert-info',
                                default => 'alert-info'
                            };
                        ?>
                            <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($message); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Page Content Starts Here -->