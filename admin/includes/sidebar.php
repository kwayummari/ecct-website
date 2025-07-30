<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Ensure we have proper user data
if (!$current_user || !is_array($current_user)) {
    if (isset($_SESSION['admin_user_id'])) {
        $current_user = $db->selectOne('admin_users', ['id' => $_SESSION['admin_user_id']]);
    }
}

// Get notifications
$notifications = [
    'messages' => $db->count('contact_messages', ['is_read' => 0]),
    'volunteers' => $db->count('volunteers', ['status' => 'pending'])
];
?>

<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-success sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center text-white mb-4">
            <h4>ECCT Admin</h4>
            <small>Welcome, <?php echo htmlspecialchars($current_user['full_name'] ?? $current_user['username'] ?? 'Admin'); ?></small>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_page == 'index.php') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/index.php">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'news') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/news/list.php">
                    <i class="fas fa-newspaper me-2"></i>News
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'campaigns') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/campaigns/list.php">
                    <i class="fas fa-bullhorn me-2"></i>Campaigns
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'volunteers') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/volunteers/list.php">
                    <i class="fas fa-users me-2"></i>Volunteers
                    <?php if ($notifications['volunteers'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?php echo $notifications['volunteers']; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'messages') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/messages/list.php">
                    <i class="fas fa-envelope me-2"></i>Messages
                    <?php if ($notifications['messages'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?php echo $notifications['messages']; ?></span>
                    <?php endif; ?>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'gallery') ? 'bg-dark' : ''; ?> d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#gallerySubmenu" role="button" aria-expanded="<?php echo ($current_dir == 'gallery') ? 'true' : 'false'; ?>">
                    <span><i class="fas fa-images me-2"></i>Gallery</span>
                    <i class="fas fa-chevron-down"></i>
                </a>
                <div class="collapse <?php echo ($current_dir == 'gallery') ? 'show' : ''; ?>" id="gallerySubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link text-white-50 <?php echo ($current_page == 'list.php' && $current_dir == 'gallery') ? 'text-white bg-secondary' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin/gallery/list.php">
                                <i class="fas fa-list me-2"></i>View Gallery
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white-50 <?php echo ($current_page == 'upload.php' && $current_dir == 'gallery') ? 'text-white bg-secondary' : ''; ?>"
                                href="<?php echo SITE_URL; ?>/admin/gallery/upload.php">
                                <i class="fas fa-upload me-2"></i>Upload Images
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'pages') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/pages/list.php">
                    <i class="fas fa-file-alt me-2"></i>Pages
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white <?php echo ($current_dir == 'settings') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/settings/site.php">
                    <i class="fas fa-cog me-2"></i>Settings
                </a>
            </li>

            <li class="nav-item mt-3">
                <a class="nav-link text-white" href="<?php echo SITE_URL; ?>/admin/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </li>
        </ul>
    </div>
</nav>