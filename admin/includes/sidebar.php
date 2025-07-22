<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

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
            <small>Welcome, <?php echo htmlspecialchars($current_user['full_name']); ?></small>
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
                <a class="nav-link text-white <?php echo ($current_dir == 'gallery') ? 'bg-dark' : ''; ?>"
                    href="<?php echo SITE_URL; ?>/admin/gallery/list.php">
                    <i class="fas fa-images me-2"></i>Gallery
                </a>
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