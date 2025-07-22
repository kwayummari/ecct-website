<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Handle mark as read/unread
if (isset($_POST['mark_read'])) {
    $message_id = (int)$_POST['message_id'];
    $is_read = (int)$_POST['is_read'];

    $data = [
        'is_read' => $is_read,
        'replied_by' => $is_read ? $current_user['id'] : null,
        'replied_at' => $is_read ? date('Y-m-d H:i:s') : null
    ];

    if ($db->update('contact_messages', $data, ['id' => $message_id])) {
        $success = $is_read ? 'Message marked as read' : 'Message marked as unread';
    } else {
        $error = 'Error updating message status';
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->delete('contact_messages', ['id' => $id])) {
        $success = 'Message deleted successfully';
    } else {
        $error = 'Error deleting message';
    }
}

// Filter by read status
$read_filter = $_GET['filter'] ?? '';
$conditions = [];
if ($read_filter === 'unread') {
    $conditions['is_read'] = 0;
} elseif ($read_filter === 'read') {
    $conditions['is_read'] = 1;
}

// Get all messages
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$result = $db->paginate('contact_messages', $page, $per_page, $conditions, ['order_by' => 'created_at DESC']);
$messages_list = $result['data'];
$pagination = $result['pagination'];

// Get message counts
$message_counts = [
    'all' => $db->count('contact_messages'),
    'unread' => $db->count('contact_messages', ['is_read' => 0]),
    'read' => $db->count('contact_messages', ['is_read' => 1])
];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Contact Messages</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Filter Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo !$read_filter ? 'active' : ''; ?>" href="list.php">
                        All Messages <span class="badge bg-secondary ms-1"><?php echo $message_counts['all']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $read_filter == 'unread' ? 'active' : ''; ?>" href="list.php?filter=unread">
                        Unread <span class="badge bg-danger ms-1"><?php echo $message_counts['unread']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $read_filter == 'read' ? 'active' : ''; ?>" href="list.php?filter=read">
                        Read <span class="badge bg-success ms-1"><?php echo $message_counts['read']; ?></span>
                    </a>
                </li>
            </ul>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($messages_list): ?>
                                    <?php foreach ($messages_list as $message): ?>
                                        <tr class="<?php echo !$message['is_read'] ? 'table-warning' : ''; ?>">
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($message['name']); ?></h6>
                                                <small class="text-muted">
                                                    <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                        <?php echo htmlspecialchars($message['email']); ?>
                                                    </a>
                                                </small>
                                                <?php if ($message['phone']): ?>
                                                    <br><small class="text-muted"><?php echo htmlspecialchars($message['phone']); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?></strong>
                                            </td>
                                            <td>
                                                <p class="mb-1"><?php echo htmlspecialchars(substr($message['message'], 0, 100)); ?><?php echo strlen($message['message']) > 100 ? '...' : ''; ?></p>
                                            </td>
                                            <td>
                                                <?php if ($message['is_read']): ?>
                                                    <span class="badge bg-success">Read</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Unread</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($message['created_at'])); ?><br>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($message['created_at'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $message['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Message">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Mark Read/Unread -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                                    <input type="hidden" name="is_read" value="<?php echo $message['is_read'] ? 0 : 1; ?>">
                                                    <button type="submit" name="mark_read" class="btn btn-sm btn-outline-<?php echo $message['is_read'] ? 'warning' : 'success'; ?>"
                                                        title="<?php echo $message['is_read'] ? 'Mark as Unread' : 'Mark as Read'; ?>">
                                                        <i class="fas fa-<?php echo $message['is_read'] ? 'envelope' : 'envelope-open'; ?>"></i>
                                                    </button>
                                                </form>

                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>"
                                                    class="btn btn-sm btn-outline-info" title="Reply">
                                                    <i class="fas fa-reply"></i>
                                                </a>

                                                <a href="?delete=<?php echo $message['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this message?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-envelope fa-3x mb-3"></i>
                                            <p>No messages found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <nav class="mt-3">
                            <ul class="pagination justify-content-center">
                                <?php if ($pagination['has_prev']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $read_filter ? '&filter=' . $read_filter : ''; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $read_filter ? '&filter=' . $read_filter : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $read_filter ? '&filter=' . $read_filter : ''; ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>