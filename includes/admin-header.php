<?php

/**
 * Admin Panel Header Template for ECCT
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

    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top admin-navbar">
        <div class="container-fluid">
            <!-- Brand -->
            <a class="navbar-brand d-flex align-items-center" href="<?php echo SITE_URL; ?>/admin/">
                <img src="<?php echo SITE_URL; ?>/assets/images/logo.jpg" alt="ECCT" height="35" class="me-2">
                <span class="fw-bold">ECCT Admin</span>
            </a>

            <!-- Mobile menu button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>

                    <!-- Content Management -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-file-alt me-2"></i>Pages
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/pages/manage-pages.php">
                                    <i class="fas fa-list me-2"></i>Manage Pages
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/pages/add-page.php">
                                    <i class="fas fa-plus me-2"></i>Add Page
                                </a></li>
                        </ul>
                    </li>

                    <!-- News Management -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-newspaper me-2"></i>News
                            <?php if ($notifications['draft_news'] > 0): ?>
                                <span class="badge bg-warning text-dark ms-1"><?php echo $notifications['draft_news']; ?></span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/news/manage-news.php">
                                    <i class="fas fa-list me-2"></i>Manage Articles
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/news/add-news.php">
                                    <i class="fas fa-plus me-2"></i>Add Article
                                </a></li>
                        </ul>
                    </li>

                    <!-- Campaigns Management -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bullhorn me-2"></i>Campaigns
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/campaigns/manage-campaigns.php">
                                    <i class="fas fa-list me-2"></i>Manage Campaigns
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/campaigns/add-campaign.php">
                                    <i class="fas fa-plus me-2"></i>Add Campaign
                                </a></li>
                        </ul>
                    </li>

                    <!-- Gallery Management -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-images me-2"></i>Gallery
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/gallery/manage-gallery.php">
                                    <i class="fas fa-list me-2"></i>Manage Images
                                </a></li>
                            <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/gallery/upload-images.php">
                                    <i class="fas fa-upload me-2"></i>Upload Images
                                </a></li>
                        </ul>
                    </li>

                    <!-- Volunteers -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/volunteers/manage-volunteers.php">
                            <i class="fas fa-users me-2"></i>Volunteers
                            <?php if ($notifications['pending_volunteers'] > 0): ?>
                                <span class="badge bg-warning text-dark ms-1"><?php echo $notifications['pending_volunteers']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Messages -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>/admin/contact/manage-messages.php">
                            <i class="fas fa-envelope me-2"></i>Messages
                            <?php if ($notifications['unread_messages'] > 0): ?>
                                <span class="badge bg-danger ms-1"><?php echo $notifications['unread_messages']; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                </ul>

                <!-- Right side menu -->
                <ul class="navbar-nav">
                    <!-- View Website -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo SITE_URL; ?>" target="_blank">
                            <i class="fas fa-external-link-alt me-2"></i>View Website
                        </a>
                    </li>

                    <!-- Settings Dropdown -->
                    <?php if (has_role('admin')): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-2"></i>Settings
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings/site-settings.php">
                                        <i class="fas fa-globe me-2"></i>Site Settings
                                    </a></li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings/user-management.php">
                                        <i class="fas fa-users-cog me-2"></i>User Management
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/admin/settings/backup.php">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                <i class="fas fa-user"></i>
                            </div>
                            <span><?php echo htmlspecialchars($current_user['name']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <div class="fw-bold"><?php echo htmlspecialchars($current_user['name']); ?></div>
                                <small class="text-muted"><?php echo htmlspecialchars($current_user['email']); ?></small>
                                <small class="text-muted d-block"><?php echo ucfirst($current_user['role']); ?></small>
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
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main class="main-content">
        <div class="content-wrapper">
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