<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// If user data is not properly loaded, try to get it directly
if (!$current_user || !is_array($current_user)) {
    if (isset($_SESSION['admin_user_id'])) {
        $current_user = $db->selectOne('admin_users', ['id' => $_SESSION['admin_user_id']]);
    }
    if (!$current_user) {
        header('Location: login.php');
        exit;
    }
}

$stats = [
    'news' => $db->count('news'),
    'campaigns' => $db->count('campaigns'),
    'volunteers' => $db->count('volunteers'),
    'messages' => $db->count('contact_messages', ['is_read' => 0]),
    'gallery' => $db->count('gallery')
];

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
                <h1 class="h2">Dashboard</h1>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-2">
                    <div class="card text-white bg-success">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $stats['news']; ?></h5>
                            <p class="card-text">News Articles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-primary">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $stats['campaigns']; ?></h5>
                            <p class="card-text">Campaigns</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-info">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $stats['volunteers']; ?></h5>
                            <p class="card-text">Volunteers</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-warning">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $stats['messages']; ?></h5>
                            <p class="card-text">New Messages</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="card text-white bg-secondary">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $stats['gallery']; ?></h5>
                            <p class="card-text">Gallery Images</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Items -->
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Recent News</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($recent_news): ?>
                                <?php foreach ($recent_news as $news): ?>
                                    <div class="border-bottom py-2">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($news['title']); ?></h6>
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($news['created_at'])); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No news articles yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Pending Volunteers</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($recent_volunteers): ?>
                                <?php foreach ($recent_volunteers as $volunteer): ?>
                                    <div class="border-bottom py-2">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($volunteer['email']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No pending applications</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>Unread Messages</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($recent_messages): ?>
                                <?php foreach ($recent_messages as $message): ?>
                                    <div class="border-bottom py-2">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($message['name']); ?></h6>
                                        <small class="text-muted"><?php echo htmlspecialchars($message['subject']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted">No unread messages</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>