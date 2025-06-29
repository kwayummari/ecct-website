<?php

/**
 * Manage News Articles - ECCT Admin Panel
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_permission('manage_content');

// Get database instance
$db = new Database();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validate_csrf()) {
        set_flash('error', 'Invalid security token.');
        redirect(current_url());
    }

    $action = $_POST['action'];
    $id = (int)$_POST['id'];

    switch ($action) {
        case 'delete':
            if ($db->delete('news', ['id' => $id])) {
                log_activity('news_delete', "News article deleted: ID $id");
                set_flash('success', 'Article deleted successfully.');
            } else {
                set_flash('error', 'Failed to delete article.');
            }
            break;

        case 'toggle_status':
            $current_status = $db->selectOne('news', ['id' => $id])['is_published'];
            $new_status = $current_status ? 0 : 1;
            if ($db->update('news', ['is_published' => $new_status], ['id' => $id])) {
                $status_text = $new_status ? 'published' : 'unpublished';
                log_activity('news_status_change', "News article $status_text: ID $id");
                set_flash('success', "Article $status_text successfully.");
            } else {
                set_flash('error', 'Failed to update article status.');
            }
            break;

        case 'toggle_featured':
            $current_featured = $db->selectOne('news', ['id' => $id])['is_featured'];
            $new_featured = $current_featured ? 0 : 1;
            if ($db->update('news', ['is_featured' => $new_featured], ['id' => $id])) {
                $featured_text = $new_featured ? 'featured' : 'unfeatured';
                log_activity('news_featured_change', "News article $featured_text: ID $id");
                set_flash('success', "Article $featured_text successfully.");
            } else {
                set_flash('error', 'Failed to update featured status.');
            }
            break;
    }

    redirect(current_url());
}

// Get parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build conditions
$conditions = [];
if ($search) {
    // We'll use a custom query for search
}
if ($status === 'published') {
    $conditions['is_published'] = 1;
} elseif ($status === 'draft') {
    $conditions['is_published'] = 0;
} elseif ($status === 'featured') {
    $conditions['is_featured'] = 1;
}

// Get news with pagination
if ($search) {
    $news_results = $db->search('news', $search, ['title', 'excerpt', 'content'], $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $total_news = count($news_results ?: []);
    $total_pages = ceil($total_news / ITEMS_PER_PAGE);
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $news_articles = array_slice($news_results ?: [], $offset, ITEMS_PER_PAGE);
    $pagination = [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'has_prev' => $page > 1,
        'has_next' => $page < $total_pages
    ];
} else {
    $pagination_result = $db->paginate('news', $page, ITEMS_PER_PAGE, $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $news_articles = $pagination_result['data'];
    $pagination = $pagination_result['pagination'];
}

// Get statistics
$stats = [
    'total' => $db->count('news'),
    'published' => $db->count('news', ['is_published' => 1]),
    'draft' => $db->count('news', ['is_published' => 0]),
    'featured' => $db->count('news', ['is_featured' => 1])
];

// Page variables
$page_title = 'Manage News Articles - ECCT Admin';
$breadcrumbs = [
    ['title' => 'News Management']
];

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Manage News Articles</h1>
                    <p class="text-muted">Create, edit and manage news articles and announcements</p>
                </div>
                <div>
                    <a href="add-news.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Article
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                            <i class="fas fa-newspaper fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['total']; ?></h4>
                            <p class="text-muted mb-0">Total Articles</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success rounded p-3 me-3">
                            <i class="fas fa-eye fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['published']; ?></h4>
                            <p class="text-muted mb-0">Published</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                            <i class="fas fa-edit fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['draft']; ?></h4>
                            <p class="text-muted mb-0">Drafts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info rounded p-3 me-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                        <div>
                            <h4 class="mb-1"><?php echo $stats['featured']; ?></h4>
                            <p class="text-muted mb-0">Featured</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <form method="GET" class="d-flex">
                                <input type="text" class="form-control me-2" name="search"
                                    value="<?php echo htmlspecialchars($search); ?>"
                                    placeholder="Search articles...">
                                <?php if ($status): ?>
                                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                                <?php endif; ?>
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-md-end mt-3 mt-md-0">
                                <div class="btn-group" role="group">
                                    <a href="?" class="btn btn-outline-secondary <?php echo empty($status) && empty($search) ? 'active' : ''; ?>">
                                        All
                                    </a>
                                    <a href="?status=published" class="btn btn-outline-success <?php echo $status === 'published' ? 'active' : ''; ?>">
                                        Published
                                    </a>
                                    <a href="?status=draft" class="btn btn-outline-warning <?php echo $status === 'draft' ? 'active' : ''; ?>">
                                        Drafts
                                    </a>
                                    <a href="?status=featured" class="btn btn-outline-info <?php echo $status === 'featured' ? 'active' : ''; ?>">
                                        Featured
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if ($search || $status): ?>
                        <div class="mt-3">
                            <div class="d-flex align-items-center">
                                <span class="text-muted me-3">Active filters:</span>
                                <?php if ($search): ?>
                                    <span class="badge bg-light text-dark me-2">
                                        Search: "<?php echo htmlspecialchars($search); ?>"
                                        <a href="?<?php echo $status ? 'status=' . urlencode($status) : ''; ?>" class="text-decoration-none ms-1">×</a>
                                    </span>
                                <?php endif; ?>
                                <?php if ($status): ?>
                                    <span class="badge bg-light text-dark me-2">
                                        Status: <?php echo ucfirst($status); ?>
                                        <a href="?<?php echo $search ? 'search=' . urlencode($search) : ''; ?>" class="text-decoration-none ms-1">×</a>
                                    </span>
                                <?php endif; ?>
                                <a href="?" class="btn btn-sm btn-outline-secondary">Clear All</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- News Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if ($news_articles): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Article</th>
                                        <th>Status</th>
                                        <th>Author</th>
                                        <th>Date</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($news_articles as $article): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-start">
                                                    <?php if ($article['featured_image']): ?>
                                                        <img src="<?php echo UPLOADS_URL . '/news/' . $article['featured_image']; ?>"
                                                            alt="<?php echo htmlspecialchars($article['title']); ?>"
                                                            class="rounded me-3" style="width: 60px; height: 60px; object-fit: cover;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center"
                                                            style="width: 60px; height: 60px;">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <a href="edit-news.php?id=<?php echo $article['id']; ?>"
                                                                class="text-decoration-none">
                                                                <?php echo htmlspecialchars($article['title']); ?>
                                                            </a>
                                                        </h6>
                                                        <p class="text-muted small mb-1">
                                                            <?php echo htmlspecialchars(truncate_text($article['excerpt'] ?: strip_tags($article['content']), 80)); ?>
                                                        </p>
                                                        <div class="small text-muted">
                                                            <?php if ($article['is_featured']): ?>
                                                                <span class="badge bg-warning text-dark me-1">Featured</span>
                                                            <?php endif; ?>
                                                            <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article['id']; ?>"
                                                                target="_blank" class="text-decoration-none">
                                                                <i class="fas fa-external-link-alt"></i> View
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $article['is_published'] ? 'success' : 'warning'; ?>">
                                                    <?php echo $article['is_published'] ? 'Published' : 'Draft'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php
                                                    $author = $db->selectOne('admin_users', ['id' => $article['created_by']]);
                                                    echo htmlspecialchars($author ? $author['full_name'] : 'Unknown');
                                                    ?>
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo format_date($article['created_at'], 'M j, Y'); ?><br>
                                                    <span class="text-muted"><?php echo format_date($article['created_at'], 'g:i A'); ?></span>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit-news.php?id=<?php echo $article['id']; ?>"
                                                        class="btn btn-sm btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Toggle Status -->
                                                    <form method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-<?php echo $article['is_published'] ? 'warning' : 'success'; ?>"
                                                            title="<?php echo $article['is_published'] ? 'Unpublish' : 'Publish'; ?>">
                                                            <i class="fas fa-<?php echo $article['is_published'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Toggle Featured -->
                                                    <form method="POST" class="d-inline">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="toggle_featured">
                                                        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                        <button type="submit"
                                                            class="btn btn-sm btn-outline-<?php echo $article['is_featured'] ? 'warning' : 'info'; ?>"
                                                            title="<?php echo $article['is_featured'] ? 'Remove from Featured' : 'Mark as Featured'; ?>">
                                                            <i class="fas fa-star"></i>
                                                        </button>
                                                    </form>

                                                    <!-- Delete -->
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this article? This action cannot be undone.');">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="News pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['has_prev']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?><?php echo $status ? '&status=' . urlencode($status) : ''; ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- No Articles -->
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">
                                <?php echo ($search || $status) ? 'No articles found' : 'No articles created yet'; ?>
                            </h4>
                            <p class="text-muted mb-4">
                                <?php if ($search || $status): ?>
                                    Try adjusting your search terms or filters.
                                <?php else: ?>
                                    Get started by creating your first news article.
                                <?php endif; ?>
                            </p>
                            <a href="add-news.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add First Article
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>