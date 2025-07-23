<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';

// Check if user is logged in - FIXED
if (!is_logged_in()) {
    header('Location: ' . SITE_URL . '/admin/login.php');
    exit;
}

$db = new Database();
$current_user = get_logged_in_user();

// If user data is not properly loaded, try to get it directly
if (!$current_user || !is_array($current_user)) {
    if (isset($_SESSION['admin_user_id'])) {
        $current_user = $db->selectOne('admin_users', ['id' => $_SESSION['admin_user_id']]);
    }
    if (!$current_user) {
        // Clear session and redirect to login
        session_destroy();
        header('Location: ' . SITE_URL . '/admin/login.php?error=session_invalid');
        exit;
    }
}

// Get dashboard statistics
$stats = [
    'news' => $db->count('news'),
    'campaigns' => $db->count('campaigns'),
    'volunteers' => $db->count('volunteers'),
    'messages' => $db->count('contact_messages', ['is_read' => 0]),
    'gallery' => $db->count('gallery')
];

// Get recent data
$recent_news = $db->select('news', [], ['order_by' => 'created_at DESC', 'limit' => 5]);
$recent_volunteers = $db->select('volunteers', ['status' => 'pending'], ['order_by' => 'applied_at DESC', 'limit' => 5]);
$recent_messages = $db->select('contact_messages', ['is_read' => 0], ['order_by' => 'created_at DESC', 'limit' => 5]);

include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <a href="<?php echo SITE_URL; ?>" class="btn btn-sm btn-outline-success" target="_blank">
                            <i class="fas fa-external-link-alt"></i> View Website
                        </a>
                    </div>
                </div>
            </div>

            <!-- Welcome Message -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-user-check me-2"></i>
                Welcome back, <strong><?php echo htmlspecialchars($current_user['full_name'] ?? $current_user['name'] ?? 'Admin'); ?></strong>!
                Last login: <?php echo isset($current_user['last_login']) ? date('M j, Y \a\t g:i A', strtotime($current_user['last_login'])) : 'First time'; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card text-white bg-success h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><?php echo $stats['news']; ?></h5>
                                    <p class="card-text">News Articles</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-newspaper fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="news/" class="text-white text-decoration-none">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card text-white bg-primary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><?php echo $stats['campaigns']; ?></h5>
                                    <p class="card-text">Campaigns</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-bullhorn fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="campaigns/" class="text-white text-decoration-none">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card text-white bg-info h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><?php echo $stats['volunteers']; ?></h5>
                                    <p class="card-text">Volunteers</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-users fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="volunteers/" class="text-white text-decoration-none">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card text-white bg-warning h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><?php echo $stats['messages']; ?></h5>
                                    <p class="card-text">New Messages</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-envelope fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="messages/" class="text-white text-decoration-none">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                    <div class="card text-white bg-secondary h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h5 class="card-title"><?php echo $stats['gallery']; ?></h5>
                                    <p class="card-text">Gallery Items</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-images fa-2x opacity-75"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="gallery/" class="text-white text-decoration-none">
                                View All <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-clock me-2"></i>
                                Recent News
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_news)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_news as $news): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><?php echo htmlspecialchars($news['title']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo date('M j, Y', strtotime($news['created_at'])); ?>
                                                </small>
                                            </div>
                                            <span class="badge bg-<?php echo $news['is_published'] ? 'success' : 'warning'; ?> rounded-pill">
                                                <?php echo $news['is_published'] ? 'Published' : 'Draft'; ?>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No news articles yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-hand-holding-heart me-2"></i>
                                Pending Volunteers
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (!empty($recent_volunteers)): ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($recent_volunteers as $volunteer): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-bold"><?php echo htmlspecialchars($volunteer['full_name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($volunteer['email']); ?></small>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo date('M j', strtotime($volunteer['applied_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">No pending volunteer applications.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt me-2"></i>
                                Quick Actions
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <a href="news/add.php" class="btn btn-outline-success w-100">
                                        <i class="fas fa-plus me-2"></i>Add News
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="campaigns/add.php" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-megaphone me-2"></i>New Campaign
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="gallery/upload.php" class="btn btn-outline-info w-100">
                                        <i class="fas fa-upload me-2"></i>Upload Images
                                    </a>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <a href="settings/" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-cog me-2"></i>Settings
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>