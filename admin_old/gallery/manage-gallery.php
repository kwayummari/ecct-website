<?php

/**
 * Manage Gallery - Admin Panel
 * ECCT Website Gallery Management
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_permission('manage_gallery');

// Get database instance
$db = new Database();

// Page variables
$page_title = 'Manage Gallery - ECCT Admin';
$current_user = get_current_user();

// Get parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!validate_csrf()) {
        set_flash('error', 'Invalid security token.');
    } else {
        $action = $_POST['bulk_action'];
        $selected_ids = $_POST['selected_images'] ?? [];

        if (empty($selected_ids)) {
            set_flash('error', 'No images selected.');
        } else {
            $success_count = 0;

            switch ($action) {
                case 'delete':
                    foreach ($selected_ids as $id) {
                        $image = $db->selectOne('gallery', ['id' => $id]);
                        if ($image) {
                            // Delete image files
                            delete_image(UPLOADS_PATH . '/gallery/' . $image['image_path']);

                            // Delete from database
                            if ($db->delete('gallery', ['id' => $id])) {
                                $success_count++;
                                log_activity('gallery_delete', "Gallery image deleted: {$image['title']}");
                            }
                        }
                    }
                    set_flash('success', "$success_count image(s) deleted successfully.");
                    break;

                case 'feature':
                    foreach ($selected_ids as $id) {
                        if ($db->update('gallery', ['is_featured' => 1], ['id' => $id])) {
                            $success_count++;
                        }
                    }
                    set_flash('success', "$success_count image(s) marked as featured.");
                    log_activity('gallery_bulk_feature', "Bulk featured $success_count images");
                    break;

                case 'unfeature':
                    foreach ($selected_ids as $id) {
                        if ($db->update('gallery', ['is_featured' => 0], ['id' => $id])) {
                            $success_count++;
                        }
                    }
                    set_flash('success', "$success_count image(s) removed from featured.");
                    log_activity('gallery_bulk_unfeature', "Bulk unfeatured $success_count images");
                    break;

                case 'set_category':
                    $new_category = sanitize_input($_POST['new_category'] ?? '');
                    if ($new_category) {
                        foreach ($selected_ids as $id) {
                            if ($db->update('gallery', ['category' => $new_category], ['id' => $id])) {
                                $success_count++;
                            }
                        }
                        set_flash('success', "$success_count image(s) moved to category '$new_category'.");
                        log_activity('gallery_bulk_category', "Bulk categorized $success_count images to $new_category");
                    }
                    break;
            }
        }
    }

    redirect(SITE_URL . '/admin/gallery/manage-gallery.php' .
        ($category ? '?category=' . urlencode($category) : '') .
        ($search ? (($category ? '&' : '?') . 'search=' . urlencode($search)) : ''));
}

// Handle individual actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = (int)$_GET['id'];

    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $image = $db->selectOne('gallery', ['id' => $id]);

        if (!$image) {
            set_flash('error', 'Image not found.');
        } else {
            switch ($action) {
                case 'delete':
                    // Delete image files
                    delete_image(UPLOADS_PATH . '/gallery/' . $image['image_path']);

                    // Delete from database
                    if ($db->delete('gallery', ['id' => $id])) {
                        set_flash('success', 'Image deleted successfully.');
                        log_activity('gallery_delete', "Gallery image deleted: {$image['title']}");
                    } else {
                        set_flash('error', 'Failed to delete image.');
                    }
                    break;

                case 'toggle_featured':
                    $new_status = $image['is_featured'] ? 0 : 1;
                    if ($db->update('gallery', ['is_featured' => $new_status], ['id' => $id])) {
                        $status_text = $new_status ? 'featured' : 'removed from featured';
                        set_flash('success', "Image $status_text successfully.");
                        log_activity('gallery_feature_toggle', "Gallery image feature toggled: {$image['title']}");
                    } else {
                        set_flash('error', 'Failed to update image status.');
                    }
                    break;
            }
        }
    }

    redirect(SITE_URL . '/admin/gallery/manage-gallery.php');
}

// Build query conditions
$conditions = [];
if ($category) {
    $conditions['category'] = $category;
}

// Get gallery images with pagination
if ($search) {
    $images_result = $db->search('gallery', $search, ['title', 'description', 'category'], $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $total_images = count($images_result ?: []);
    $images = array_slice($images_result ?: [], ($page - 1) * ITEMS_PER_PAGE, ITEMS_PER_PAGE);
    $pagination = [
        'current_page' => $page,
        'total_pages' => ceil($total_images / ITEMS_PER_PAGE),
        'has_prev' => $page > 1,
        'has_next' => $page < ceil($total_images / ITEMS_PER_PAGE)
    ];
} else {
    $pagination_result = $db->paginate('gallery', $page, ITEMS_PER_PAGE, $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $images = $pagination_result['data'];
    $pagination = $pagination_result['pagination'];
}

// Get available categories
$categories = $db->raw(
    "SELECT category, COUNT(*) as count 
     FROM gallery 
     WHERE category IS NOT NULL AND category != '' 
     GROUP BY category 
     ORDER BY category ASC"
);
$categories = $categories ? $categories->fetchAll() : [];

// Get gallery statistics
$stats = [
    'total_images' => $db->count('gallery'),
    'featured_images' => $db->count('gallery', ['is_featured' => 1]),
    'categories_count' => count($categories),
    'recent_uploads' => $db->count('gallery', [], [
        'conditions' => "created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
    ])
];

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Gallery Management</h1>
                    <p class="text-muted">Manage your photo gallery and organize images</p>
                </div>
                <div class="page-actions">
                    <a href="upload-images.php" class="btn btn-primary">
                        <i class="fas fa-upload me-2"></i>Upload Images
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                            <i class="fas fa-images fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Total Images</h6>
                            <h3 class="mb-0"><?php echo $stats['total_images']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Featured</h6>
                            <h3 class="mb-0"><?php echo $stats['featured_images']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                            <i class="fas fa-folder fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Categories</h6>
                            <h3 class="mb-0"><?php echo $stats['categories_count']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-muted mb-1">Recent (30 days)</h6>
                            <h3 class="mb-0"><?php echo $stats['recent_uploads']; ?></h3>
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
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Images</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Search by title, description...">
                        </div>

                        <div class="col-md-3">
                            <label for="category" class="form-label">Filter by Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                        <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucwords($cat['category'])); ?> (<?php echo $cat['count']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Filter
                            </button>
                            <a href="manage-gallery.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>

                        <div class="col-md-2 text-end">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="view" id="grid-view" checked>
                                <label class="btn btn-outline-secondary" for="grid-view">
                                    <i class="fas fa-th"></i>
                                </label>

                                <input type="radio" class="btn-check" name="view" id="list-view">
                                <label class="btn btn-outline-secondary" for="list-view">
                                    <i class="fas fa-list"></i>
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Grid -->
    <div class="row">
        <div class="col-12">
            <?php if ($images): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <form method="POST" action="" id="bulkForm">
                            <?php echo csrf_field(); ?>
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check me-3">
                                            <input class="form-check-input" type="checkbox" id="selectAll">
                                            <label class="form-check-label" for="selectAll">
                                                Select All
                                            </label>
                                        </div>
                                        <span class="text-muted" id="selectedCount">0 selected</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end align-items-center">
                                        <select class="form-select form-select-sm me-2" name="bulk_action" style="width: auto;">
                                            <option value="">Bulk Actions</option>
                                            <option value="feature">Mark as Featured</option>
                                            <option value="unfeature">Remove from Featured</option>
                                            <option value="set_category">Set Category</option>
                                            <option value="delete">Delete</option>
                                        </select>
                                        <input type="text" class="form-control form-control-sm me-2" name="new_category"
                                            placeholder="New category" style="width: 150px; display: none;" id="categoryInput">
                                        <button type="submit" class="btn btn-sm btn-primary" id="bulkSubmit" disabled>
                                            Apply
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body p-0">
                        <div class="gallery-grid" id="galleryGrid">
                            <div class="row g-3 p-3">
                                <?php foreach ($images as $image): ?>
                                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                                        <div class="gallery-item position-relative">
                                            <div class="form-check position-absolute top-0 start-0 m-2" style="z-index: 10;">
                                                <input class="form-check-input image-checkbox" type="checkbox"
                                                    name="selected_images[]" value="<?php echo $image['id']; ?>"
                                                    id="image_<?php echo $image['id']; ?>">
                                            </div>

                                            <?php if ($image['is_featured']): ?>
                                                <div class="position-absolute top-0 end-0 m-2" style="z-index: 10;">
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-star"></i>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <div class="image-container rounded overflow-hidden shadow-sm">
                                                <img src="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                                    alt="<?php echo htmlspecialchars($image['title']); ?>"
                                                    class="img-fluid w-100"
                                                    style="height: 200px; object-fit: cover;"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#imagePreviewModal"
                                                    data-image="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                                    data-title="<?php echo htmlspecialchars($image['title']); ?>">

                                                <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                                    <div class="btn-group">
                                                        <a href="edit-gallery.php?id=<?php echo $image['id']; ?>"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?action=toggle_featured&id=<?php echo $image['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                            class="btn btn-warning btn-sm"
                                                            title="<?php echo $image['is_featured'] ? 'Remove from Featured' : 'Mark as Featured'; ?>">
                                                            <i class="fas fa-star"></i>
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $image['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="return confirm('Are you sure you want to delete this image?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="image-info mt-2">
                                                <h6 class="mb-1"><?php echo htmlspecialchars(truncate_text($image['title'], 30)); ?></h6>
                                                <?php if ($image['category']): ?>
                                                    <small class="badge bg-light text-dark"><?php echo htmlspecialchars($image['category']); ?></small>
                                                <?php endif; ?>
                                                <small class="text-muted d-block"><?php echo format_date($image['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- List View (hidden by default) -->
                        <div class="gallery-list d-none" id="galleryList">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAllList">
                                                </div>
                                            </th>
                                            <th width="80">Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th>Featured</th>
                                            <th>Upload Date</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($images as $image): ?>
                                            <tr>
                                                <td>
                                                    <div class="form-check">
                                                        <input class="form-check-input image-checkbox-list" type="checkbox"
                                                            name="selected_images[]" value="<?php echo $image['id']; ?>">
                                                    </div>
                                                </td>
                                                <td>
                                                    <img src="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                                        alt="<?php echo htmlspecialchars($image['title']); ?>"
                                                        class="img-thumbnail"
                                                        style="width: 60px; height: 60px; object-fit: cover;">
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($image['title']); ?></strong>
                                                    <?php if ($image['description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars(truncate_text($image['description'], 50)); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($image['category']): ?>
                                                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($image['category']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($image['is_featured']): ?>
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="fas fa-star me-1"></i>Featured
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">—</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted"><?php echo format_date($image['created_at']); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit-gallery.php?id=<?php echo $image['id']; ?>"
                                                            class="btn btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?action=toggle_featured&id=<?php echo $image['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                            class="btn btn-outline-warning"
                                                            title="<?php echo $image['is_featured'] ? 'Remove from Featured' : 'Mark as Featured'; ?>">
                                                            <i class="fas fa-star"></i>
                                                        </a>
                                                        <a href="?action=delete&id=<?php echo $image['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                            class="btn btn-outline-danger"
                                                            title="Delete"
                                                            onclick="return confirm('Are you sure you want to delete this image?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <?php if ($pagination['total_pages'] > 1): ?>
                        <div class="card-footer bg-white border-0">
                            <nav aria-label="Gallery pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($pagination['has_prev']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['total_pages'], $pagination['current_page'] + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- No Images -->
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-images fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">
                            <?php echo ($search || $category) ? 'No images found' : 'No images uploaded yet'; ?>
                        </h4>
                        <p class="text-muted mb-4">
                            <?php if ($search || $category): ?>
                                Try adjusting your search terms or filters.
                            <?php else: ?>
                                Start building your gallery by uploading some images.
                            <?php endif; ?>
                        </p>
                        <?php if (!$search && !$category): ?>
                            <a href="upload-images.php" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Upload First Images
                            </a>
                        <?php else: ?>
                            <a href="manage-gallery.php" class="btn btn-outline-primary me-2">
                                <i class="fas fa-list me-2"></i>View All Images
                            </a>
                            <a href="upload-images.php" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>Upload Images
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="imagePreviewTitle">Image Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img src="" alt="" class="img-fluid" id="imagePreviewImg" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-item {
        transition: transform 0.2s ease;
    }

    .gallery-item:hover {
        transform: translateY(-2px);
    }

    .image-container {
        position: relative;
        cursor: pointer;
    }

    .image-overlay {
        background: rgba(0, 0, 0, 0.7);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-item:hover .image-overlay {
        opacity: 1;
    }

    .image-container img {
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .image-container img {
        transform: scale(1.05);
    }

    .form-check-input:checked {
        background-color: #28a745;
        border-color: #28a745;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // View toggle functionality
        const gridView = document.getElementById('grid-view');
        const listView = document.getElementById('list-view');
        const galleryGrid = document.getElementById('galleryGrid');
        const galleryList = document.getElementById('galleryList');

        gridView.addEventListener('change', function() {
            if (this.checked) {
                galleryGrid.classList.remove('d-none');
                galleryList.classList.add('d-none');
            }
        });

        listView.addEventListener('change', function() {
            if (this.checked) {
                galleryList.classList.remove('d-none');
                galleryGrid.classList.add('d-none');
            }
        });

        // Checkbox selection functionality
        const selectAll = document.getElementById('selectAll');
        const selectAllList = document.getElementById('selectAllList');
        const imageCheckboxes = document.querySelectorAll('.image-checkbox');
        const imageCheckboxesList = document.querySelectorAll('.image-checkbox-list');
        const selectedCount = document.getElementById('selectedCount');
        const bulkSubmit = document.getElementById('bulkSubmit');
        const bulkActionSelect = document.querySelector('select[name="bulk_action"]');
        const categoryInput = document.getElementById('categoryInput');

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.image-checkbox:checked, .image-checkbox-list:checked');
            const count = checkedBoxes.length;
            selectedCount.textContent = count + ' selected';
            bulkSubmit.disabled = count === 0;

            // Update select all checkboxes
            const totalBoxes = imageCheckboxes.length + imageCheckboxesList.length;
            selectAll.indeterminate = count > 0 && count < totalBoxes;
            selectAll.checked = count === totalBoxes;
            selectAllList.indeterminate = count > 0 && count < totalBoxes;
            selectAllList.checked = count === totalBoxes;
        }

        // Select all functionality
        selectAll.addEventListener('change', function() {
            imageCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        selectAllList.addEventListener('change', function() {
            imageCheckboxesList.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Individual checkbox change
        [...imageCheckboxes, ...imageCheckboxesList].forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        // Bulk action select change
        bulkActionSelect.addEventListener('change', function() {
            if (this.value === 'set_category') {
                categoryInput.style.display = 'block';
            } else {
                categoryInput.style.display = 'none';
            }
        });

        // Form submission confirmation
        document.getElementById('bulkForm').addEventListener('submit', function(e) {
            const action = bulkActionSelect.value;
            const selectedImages = document.querySelectorAll('.image-checkbox:checked, .image-checkbox-list:checked');

            if (selectedImages.length === 0) {
                e.preventDefault();
                alert('Please select at least one image.');
                return;
            }

            let confirmMessage = '';
            switch (action) {
                case 'delete':
                    confirmMessage = `Are you sure you want to delete ${selectedImages.length} image(s)? This action cannot be undone.`;
                    break;
                case 'feature':
                    confirmMessage = `Mark ${selectedImages.length} image(s) as featured?`;
                    break;
                case 'unfeature':
                    confirmMessage = `Remove ${selectedImages.length} image(s) from featured?`;
                    break;
                case 'set_category':
                    const newCategory = categoryInput.value.trim();
                    if (!newCategory) {
                        e.preventDefault();
                        alert('Please enter a category name.');
                        return;
                    }
                    confirmMessage = `Move ${selectedImages.length} image(s) to category "${newCategory}"?`;
                    break;
                default:
                    e.preventDefault();
                    alert('Please select a bulk action.');
                    return;
            }

            if (confirmMessage && !confirm(confirmMessage)) {
                e.preventDefault();
            }
        });

        // Image preview modal
        const imagePreviewModal = document.getElementById('imagePreviewModal');
        const imagePreviewImg = document.getElementById('imagePreviewImg');
        const imagePreviewTitle = document.getElementById('imagePreviewTitle');

        imagePreviewModal.addEventListener('show.bs.modal', function(event) {
            const trigger = event.relatedTarget;
            const imageSrc = trigger.getAttribute('data-image');
            const imageTitle = trigger.getAttribute('data-title');

            imagePreviewImg.src = imageSrc;
            imagePreviewImg.alt = imageTitle;
            imagePreviewTitle.textContent = imageTitle;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + A to select all
            if ((e.ctrlKey || e.metaKey) && e.key === 'a' && e.target.tagName !== 'INPUT') {
                e.preventDefault();
                selectAll.checked = true;
                selectAll.dispatchEvent(new Event('change'));
            }

            // Delete key to delete selected
            if (e.key === 'Delete' && document.activeElement.tagName !== 'INPUT') {
                const selectedImages = document.querySelectorAll('.image-checkbox:checked, .image-checkbox-list:checked');
                if (selectedImages.length > 0) {
                    bulkActionSelect.value = 'delete';
                    if (confirm(`Delete ${selectedImages.length} selected image(s)?`)) {
                        document.getElementById('bulkForm').submit();
                    }
                }
            }
        });

        // Initialize count
        updateSelectedCount();
    });
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>