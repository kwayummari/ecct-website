<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->delete('pages', ['id' => $id])) {
        $success = 'Page deleted successfully';
    } else {
        $error = 'Error deleting page';
    }
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $page_id = (int)$_POST['page_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status ? 0 : 1;

    if ($db->update('pages', ['is_published' => $new_status], ['id' => $page_id])) {
        $success = $new_status ? 'Page published' : 'Page unpublished';
    } else {
        $error = 'Error updating page status';
    }
}

// Get all pages
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$result = $db->paginate('pages', $page, $per_page, [], ['order_by' => 'sort_order ASC, title ASC']);
$pages_list = $result['data'];
$pagination = $result['pagination'];

// Get page stats
$page_stats = [
    'total' => $db->count('pages'),
    'published' => $db->count('pages', ['is_published' => 1]),
    'draft' => $db->count('pages', ['is_published' => 0])
];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Pages Management</h1>
                <a href="add.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Add Page
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

            <!-- Stats -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-primary"><?php echo $page_stats['total']; ?></h5>
                            <small class="text-muted">Total Pages</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-success"><?php echo $page_stats['published']; ?></h5>
                            <small class="text-muted">Published Pages</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h5 class="text-warning"><?php echo $page_stats['draft']; ?></h5>
                            <small class="text-muted">Draft Pages</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Order</th>
                                    <th>Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pages_list): ?>
                                    <?php foreach ($pages_list as $page_item): ?>
                                        <tr>
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($page_item['title']); ?></h6>
                                                <?php if ($page_item['meta_description']): ?>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($page_item['meta_description'], 0, 80)); ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($page_item['slug']); ?></code>
                                                <br><small class="text-muted">
                                                    <a href="<?php echo SITE_URL . '/' . $page_item['slug']; ?>" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-external-link-alt me-1"></i>View
                                                    </a>
                                                </small>
                                            </td>
                                            <td>
                                                <?php if ($page_item['is_published']): ?>
                                                    <span class="badge bg-success">Published</span>
                                                <?php else: ?>
                                                    <span class="badge bg-warning">Draft</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark"><?php echo $page_item['sort_order'] ?: 'No order'; ?></span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($page_item['updated_at'])); ?><br>
                                                <small class="text-muted"><?php echo date('g:i A', strtotime($page_item['updated_at'])); ?></small>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $page_item['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Toggle Status -->
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="page_id" value="<?php echo $page_item['id']; ?>">
                                                    <input type="hidden" name="current_status" value="<?php echo $page_item['is_published']; ?>">
                                                    <button type="submit" name="toggle_status"
                                                        class="btn btn-sm btn-outline-<?php echo $page_item['is_published'] ? 'warning' : 'success'; ?>"
                                                        title="<?php echo $page_item['is_published'] ? 'Unpublish' : 'Publish'; ?>">
                                                        <i class="fas fa-<?php echo $page_item['is_published'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                    </button>
                                                </form>

                                                <a href="<?php echo SITE_URL . '/' . $page_item['slug']; ?>" target="_blank"
                                                    class="btn btn-sm btn-outline-info" title="View Page">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>

                                                <a href="?delete=<?php echo $page_item['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this page?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-file-alt fa-3x mb-3"></i>
                                            <p>No pages found</p>
                                            <a href="add.php" class="btn btn-success">Add First Page</a>
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
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>">Next</a>
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

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>