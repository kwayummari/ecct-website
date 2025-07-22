<?php

                        ini_set('display_errors', '1');
                        ini_set('display_startup_errors', '1');
                        error_reporting(E_ALL);

/**
 * Admin Dashboard for ECCT Website
 */

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login
require_login();

// Get database instance
$db = new Database();

// Page variables
$page_title = 'Dashboard - ECCT Admin';
$current_user = get_current_user();

// Get dashboard statistics
$stats = [
    'total_news' => $db->count('news'),
    'published_news' => $db->count('news', ['is_published' => 1]),
    'total_campaigns' => $db->count('campaigns'),
    'active_campaigns' => $db->count('campaigns', ['status' => 'active']),
    'total_volunteers' => $db->count('volunteers'),
    'pending_volunteers' => $db->count('volunteers', ['status' => 'pending']),
    'gallery_images' => $db->count('gallery'),
    'unread_messages' => $db->count('contact_messages', ['is_read' => 0]),
    'newsletter_subscribers' => $db->count('newsletter_subscribers', ['status' => 'active'])
];

// Get recent activity
$recent_activity = get_recent_activity(10);

// Get recent content
$recent_news = $db->select('news', [], [
    'order_by' => 'created_at DESC',
    'limit' => 5
]);

$recent_volunteers = $db->select('volunteers', [], [
    'order_by' => 'applied_at DESC',
    'limit' => 5
]);

$recent_messages = $db->select('contact_messages', [], [
    'order_by' => 'created_at DESC',
    'limit' => 5
]);

// Include admin header
include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Dashboard Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Dashboard</h1>
                    <p class="text-muted">Welcome back, <?php echo htmlspecialchars($current_user['name']); ?>!</p>
                </div>
                <div class="dashboard-actions">
                    <button class="btn btn-outline-primary btn-sm me-2" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-plus me-2"></i>Quick Add
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="news/add-news.php">
                                    <i class="fas fa-newspaper me-2"></i>New Article
                                </a></li>
                            <li><a class="dropdown-item" href="campaigns/add-campaign.php">
                                    <i class="fas fa-bullhorn me-2"></i>New Campaign
                                </a></li>
                            <li><a class="dropdown-item" href="gallery/upload-images.php">
                                    <i class="fas fa-images me-2"></i>Upload Images
                                </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Volunteers</h6>
                            <h3 class="mb-0"><?php echo $stats['total_volunteers']; ?></h3>
                            <small class="text-warning">
                                <i class="fas fa-clock me-1"></i>
                                <?php echo $stats['pending_volunteers']; ?> pending
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="volunteers/manage-volunteers.php" class="btn btn-sm btn-outline-info w-100">
                        <i class="fas fa-cog me-2"></i>Manage Volunteers
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Messages</h6>
                            <h3 class="mb-0"><?php echo $stats['unread_messages']; ?></h3>
                            <small class="text-muted">
                                <i class="fas fa-envelope-open me-1"></i>
                                Unread messages
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0 pt-0">
                    <a href="contact/manage-messages.php" class="btn btn-sm btn-outline-warning w-100">
                        <i class="fas fa-cog me-2"></i>View Messages
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Recent Activity</h5>
                        <a href="activity-log.php" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($recent_activity): ?>
                        <div class="timeline">
                            <?php foreach ($recent_activity as $activity): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($activity['action']); ?></h6>
                                                <p class="text-muted small mb-1">
                                                    <?php echo htmlspecialchars($activity['description']); ?>
                                                </p>
                                                <small class="text-muted">
                                                    by <?php echo htmlspecialchars($activity['full_name'] ?? 'System'); ?>
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo time_ago($activity['created_at']); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent activity</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Stats & Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <h5 class="card-title mb-0">Quick Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="quick-stat">
                                <i class="fas fa-images text-primary fa-2x mb-2"></i>
                                <h4 class="mb-1"><?php echo $stats['gallery_images']; ?></h4>
                                <small class="text-muted">Gallery Images</small>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="quick-stat">
                                <i class="fas fa-users text-success fa-2x mb-2"></i>
                                <h4 class="mb-1"><?php echo $stats['newsletter_subscribers']; ?></h4>
                                <small class="text-muted">Subscribers</small>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="quick-actions">
                        <h6 class="mb-3">Quick Actions</h6>
                        <div class="d-grid gap-2">
                            <a href="pages/manage-pages.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-file-alt me-2"></i>Manage Pages
                            </a>
                            <a href="settings/site-settings.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-cog me-2"></i>Site Settings
                            </a>
                            <a href="settings/backup.php" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-download me-2"></i>Backup Database
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent News -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Recent News</h6>
                        <a href="news/add-news.php" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($recent_news): ?>
                        <?php foreach ($recent_news as $news): ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="news/edit-news.php?id=<?php echo $news['id']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars(truncate_text($news['title'], 50)); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <?php echo format_date($news['created_at']); ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo $news['is_published'] ? 'success' : 'warning'; ?> ms-2">
                                    <?php echo $news['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-newspaper fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">No news articles yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Volunteers -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Recent Volunteers</h6>
                        <a href="volunteers/manage-volunteers.php" class="btn btn-sm btn-info">
                            <i class="fas fa-users"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($recent_volunteers): ?>
                        <?php foreach ($recent_volunteers as $volunteer): ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?>
                                    </h6>
                                    <small class="text-muted d-block">
                                        <?php echo htmlspecialchars($volunteer['email']); ?>
                                    </small>
                                    <small class="text-muted">
                                        Applied <?php echo time_ago($volunteer['applied_at']); ?>
                                    </small>
                                </div>
                                <span class="badge bg-<?php echo match ($volunteer['status']) {
                                                            'approved' => 'success',
                                                            'pending' => 'warning',
                                                            'active' => 'primary',
                                                            default => 'secondary'
                                                        }; ?> ms-2">
                                    <?php echo ucfirst($volunteer['status']); ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-user-plus fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">No volunteer applications yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Messages -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">Recent Messages</h6>
                        <a href="contact/manage-messages.php" class="btn btn-sm btn-warning">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if ($recent_messages): ?>
                        <?php foreach ($recent_messages as $message): ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <?php echo htmlspecialchars($message['name']); ?>
                                    </h6>
                                    <small class="text-muted d-block">
                                        <?php echo htmlspecialchars($message['subject']); ?>
                                    </small>
                                    <small class="text-muted">
                                        <?php echo time_ago($message['created_at']); ?>
                                    </small>
                                </div>
                                <?php if (!$message['is_read']): ?>
                                    <span class="badge bg-danger ms-2">New</span>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-envelope fa-2x text-muted mb-2"></i>
                            <p class="text-muted small">No messages yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function refreshDashboard() {
        location.reload();
    }

    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        // Only refresh if page is visible
        if (!document.hidden) {
            const currentTime = new Date().getTime();
            const lastRefresh = localStorage.getItem('lastDashboardRefresh');

            if (!lastRefresh || (currentTime - lastRefresh) > 300000) { // 5 minutes
                localStorage.setItem('lastDashboardRefresh', currentTime);
                refreshDashboard();
            }
        }
    }, 300000);
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>card-body">
<div class="d-flex align-items-center">
    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
        <i class="fas fa-newspaper fa-2x"></i>
    </div>
    <div class="flex-grow-1">
        <h6 class="text-muted mb-1">News Articles</h6>
        <h3 class="mb-0"><?php echo $stats['total_news']; ?></h3>
        <small class="text-success">
            <i class="fas fa-eye me-1"></i>
            <?php echo $stats['published_news']; ?> published
        </small>
    </div>
</div>
</div>
<div class="card-footer bg-transparent border-0 pt-0">
    <a href="news/manage-news.php" class="btn btn-sm btn-outline-primary w-100">
        <i class="fas fa-cog me-2"></i>Manage Articles
    </a>
</div>
</div>
</div>

<div class="col-xl-3 col-md-6 mb-3">
    <div class="card stat-card border-0 shadow-sm h-100">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                    <i class="fas fa-bullhorn fa-2x"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="text-muted mb-1">Campaigns</h6>
                    <h3 class="mb-0"><?php echo $stats['total_campaigns']; ?></h3>
                    <small class="text-success">
                        <i class="fas fa-play me-1"></i>
                        <?php echo $stats['active_campaigns']; ?> active
                    </small>
                </div>
            </div>
        </div>
        <div class="card-footer bg-transparent border-0 pt-0">
            <a href="campaigns/manage-campaigns.php" class="btn btn-sm btn-outline-success w-100">
                <i class="fas fa-cog me-2"></i>Manage Campaigns
            </a>
        </div>
    </div>
</div>

<div class="col-xl-3 col-md-6 mb-3">
    <div class="card stat-card border-0 shadow-sm h-100">
        <div class="