<?php

/**
 * Website Header Template for ECCT
 */

// Ensure we have the necessary includes
if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', dirname(__FILE__, 2));
}

require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Get site settings
$db = new Database();
$site_name = $db->getSetting('site_name', SITE_NAME);
$site_tagline = $db->getSetting('site_tagline', 'Striving for a cleaner, greener, healthier environment');
$site_logo = $db->getSetting('site_logo', 'assets/images/logo.jpg');
$facebook_url = $db->getSetting('facebook_url', '#');
$twitter_url = $db->getSetting('twitter_url', '#');
$instagram_url = $db->getSetting('instagram_url', '#');

// Get menu items
$menu_items = get_menu_items();

// Default page variables if not set
$page_title = $page_title ?? $site_name;
$meta_description = $meta_description ?? $db->getSetting('site_description', '');
$page_class = $page_class ?? '';
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- SEO Meta Tags -->
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="keywords" content="environmental conservation, Tanzania, ECCT, plastic waste, climate change, biodiversity">
    <meta name="author" content="Environmental Conservation Community of Tanzania">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo current_url(); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($site_name); ?>">
    <meta property="og:image" content="<?php echo SITE_URL . '/' . $site_logo; ?>">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <meta name="twitter:image" content="<?php echo SITE_URL . '/' . $site_logo; ?>">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/assets/images/favicon.ico">
    <link rel="apple-touch-icon" href="<?php echo SITE_URL; ?>/assets/images/apple-touch-icon.png">

    <!-- Stylesheets -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- <link href="https://ecct.serengetibytes.com/assets/css/style.css" rel="stylesheet"> -->
    <link href="<?php echo ASSETS_PATH; ?>/css/responsive.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Additional CSS for specific pages -->
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Google Analytics -->
    <?php
    $google_analytics = $db->getSetting('google_analytics', '');
    if (!empty($google_analytics)):
    ?>
        <?php echo $google_analytics; ?>
    <?php endif; ?>
</head>

<body class="<?php echo $page_class; ?>">

    <!-- Skip to main content for accessibility -->
    <a class="skip-link sr-only sr-only-focusable" href="#main-content">Skip to main content</a>

    <!-- Top Bar -->
    <div class="top-bar bg-primary text-white py-2 d-none d-md-block">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="contact-info">
                        <span class="me-4">
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($db->getSetting('contact_email', SITE_EMAIL)); ?>
                        </span>
                        <span>
                            <i class="fas fa-phone me-2"></i>
                            <?php echo htmlspecialchars($db->getSetting('contact_phone', '')); ?>
                        </span>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="social-links">
                        <?php if (!empty($facebook_url) && $facebook_url !== '#'): ?>
                            <a href="<?php echo $facebook_url; ?>" target="_blank" class="text-white me-3">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($twitter_url) && $twitter_url !== '#'): ?>
                            <a href="<?php echo $twitter_url; ?>" target="_blank" class="text-white me-3">
                                <i class="fab fa-twitter"></i>
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($instagram_url) && $instagram_url !== '#'): ?>
                            <a href="<?php echo $instagram_url; ?>" target="_blank" class="text-white">
                                <i class="fab fa-instagram"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
                <img src="<?php echo SITE_URL . '/' . $site_logo; ?>"
                    alt="<?php echo htmlspecialchars($site_name); ?>"
                    height="50">
            </a>

            <!-- Mobile menu button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Menu -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'index') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>">Home</a>
                    </li>

                    <?php if ($menu_items): ?>
                        <?php foreach ($menu_items as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link <?php echo ($current_page === $item['slug']) ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL . '/' . $item['slug']; ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'news') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/news">News</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'campaigns') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/campaigns">Campaigns</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'gallery') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/gallery">Gallery</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($current_page === 'contact') ? 'active' : ''; ?>"
                            href="<?php echo SITE_URL; ?>/contact">Contact</a>
                    </li>

                    <li class="nav-item">
                        <a class="btn btn-primary ms-2" href="<?php echo SITE_URL; ?>/volunteer">
                            Volunteer
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <?php if (has_flash()): ?>
        <div class="container mt-3">
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

    <!-- Main Content Area -->
    <main id="main-content" role="main">