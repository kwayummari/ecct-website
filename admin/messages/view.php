<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Get message
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = $db->selectOne('contact_messages', ['id' => $id]);

if (!$message) {
    header('Location: list.php?error=Message not found');
    exit;
}

// Auto-mark as read when viewing
if (!$message['is_read']) {
    $db->update('contact_messages', [
        'is_read' => 1,
        'replied_by' => $current_user['id'],
        'replied_at' => date('Y-m-d H:i:s')
    ], ['id' => $id]);
    $message['is_read'] = 1;
}

// Handle mark as read/unread
if (isset($_POST['toggle_read'])) {
    $is_read = $message['is_read'] ? 0 : 1;

    $data = [
        'is_read' => $is_read,
        'replied_by' => $is_read ? $current_user['id'] : null,
        'replied_at' => $is_read ? date('Y-m-d H:i:s') : null
    ];

    if ($db->update('contact_messages', $data, ['id' => $id])) {
        $success = $is_read ? 'Message marked as read' : 'Message marked as unread';
        $message = $db->selectOne('contact_messages', ['id' => $id]); // Refresh data
    } else {
        $error = 'Error updating message status';
    }
}

// Get reply handler info if available
$reply_handler = null;
if ($message['replied_by']) {
    $reply_handler = $db->selectOne('admin_users', ['id' => $message['replied_by']]);
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Message Details</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Messages
                </a>
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

            <div class="row">
                <div class="col-md-8">
                    <!-- Message Content -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <?php echo htmlspecialchars($message['subject'] ?: 'No Subject'); ?>
                                </h5>
                                <?php if ($message['is_read']): ?>
                                    <span class="badge bg-success">Read</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Unread</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Message Header -->
                            <div class="border-bottom pb-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>From:</strong> <?php echo htmlspecialchars($message['name']); ?></p>
                                        <p class="mb-1"><strong>Email:</strong>
                                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>">
                                                <?php echo htmlspecialchars($message['email']); ?>
                                            </a>
                                        </p>
                                        <?php if ($message['phone']): ?>
                                            <p class="mb-1"><strong>Phone:</strong>
                                                <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>">
                                                    <?php echo htmlspecialchars($message['phone']); ?>
                                                </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Date:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($message['created_at'])); ?></p>
                                        <?php if ($message['ip_address']): ?>
                                            <p class="mb-1"><strong>IP Address:</strong> <?php echo htmlspecialchars($message['ip_address']); ?></p>
                                        <?php endif; ?>
                                        <?php if ($message['replied_at']): ?>
                                            <p class="mb-1"><strong>Handled:</strong> <?php echo date('F j, Y \a\t g:i A', strtotime($message['replied_at'])); ?>
                                                <?php if ($reply_handler): ?>
                                                    <br><small class="text-muted">by <?php echo htmlspecialchars($reply_handler['full_name']); ?></small>
                                                <?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Message Body -->
                            <div class="message-content">
                                <h6>Message:</h6>
                                <div class="bg-light p-3 rounded">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Reply Form -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Reply</h5>
                        </div>
                        <div class="card-body">
                            <form action="mailto:<?php echo htmlspecialchars($message['email']); ?>" method="get">
                                <input type="hidden" name="subject" value="Re: <?php echo htmlspecialchars($message['subject']); ?>">

                                <div class="mb-3">
                                    <label for="reply_subject" class="form-label">Subject:</label>
                                    <input type="text" class="form-control" id="reply_subject"
                                        value="Re: <?php echo htmlspecialchars($message['subject']); ?>" readonly>
                                </div>

                                <div class="mb-3">
                                    <label for="reply_body" class="form-label">Message:</label>
                                    <textarea class="form-control" id="reply_body" name="body" rows="6"
                                        placeholder="Type your reply here...">Dear <?php echo htmlspecialchars($message['name']); ?>,

Thank you for contacting ECCT.



Best regards,
<?php echo htmlspecialchars($current_user['full_name']); ?>
Environmental Conservation Community of Tanzania</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-reply me-2"></i>Open in Email Client
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Actions</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="mb-3">
                                <button type="submit" name="toggle_read" class="btn btn-outline-<?php echo $message['is_read'] ? 'warning' : 'success'; ?> w-100">
                                    <i class="fas fa-<?php echo $message['is_read'] ? 'envelope' : 'envelope-open'; ?> me-2"></i>
                                    Mark as <?php echo $message['is_read'] ? 'Unread' : 'Read'; ?>
                                </button>
                            </form>

                            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject']); ?>"
                                class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-reply me-2"></i>Reply via Email
                            </a>

                            <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>"
                                class="btn btn-success w-100 mb-2" <?php echo !$message['phone'] ? 'style="display:none;"' : ''; ?>>
                                <i class="fas fa-phone me-2"></i>Call
                            </a>

                            <a href="list.php?delete=<?php echo $message['id']; ?>"
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Are you sure you want to delete this message?')">
                                <i class="fas fa-trash me-2"></i>Delete Message
                            </a>
                        </div>
                    </div>

                    <!-- Message Info -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Message Info</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>Received:</strong><br>
                                <?php echo date('F j, Y', strtotime($message['created_at'])); ?><br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($message['created_at'])); ?></small>
                            </p>

                            <?php if ($message['ip_address']): ?>
                                <p><strong>IP Address:</strong><br>
                                    <?php echo htmlspecialchars($message['ip_address']); ?></p>
                            <?php endif; ?>

                            <?php if ($message['user_agent']): ?>
                                <p><strong>Browser:</strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars(substr($message['user_agent'], 0, 100)); ?></small>
                                </p>
                            <?php endif; ?>

                            <p><strong>Message Length:</strong><br>
                                <?php echo strlen($message['message']); ?> characters</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>