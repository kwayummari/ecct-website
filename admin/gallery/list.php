<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $image = $db->selectOne('gallery', ['id' => $id]);

    if ($image && $db->delete('gallery', ['id' => $id])) {
        // Delete physical file
        if ($image['image_path'] && file_exists(ECCT_ROOT . '/' . $image['image_path'])) {
            unlink(ECCT_ROOT . '/' . $image['image_path']);
        }
        $success = 'Image deleted successfully';
    } else {
        $error = 'Error deleting image';
    }
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $image_id = (int)$_POST['image_id'];
    $current_status = (int)$_POST['current_status'];
    $new_status = $current_status ? 0 : 1;

    if ($db->update('gallery', ['is_active' => $new_status], ['id' => $image_id])) {
        $success = $new_status ? 'Image activated' : 'Image deactivated';
    } else {
        $error = 'Error updating image status';
    }
}

// Filter by category
$category_filter = $_GET['category'] ?? '';
$conditions = [];
if ($category_filter) {
    $conditions['category'] = $category_filter;
}

// Get all gallery images
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$result = $db->paginate('gallery', $page, $per_page, $conditions, ['order_by' => 'uploaded_at DESC']);
$gallery_list = $result['data'];
$pagination = $result['pagination'];

// Get categories for filter
$categories = $db->raw("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categories ? $categories->fetchAll(PDO::FETCH_COLUMN) : [];

// Get gallery stats
$gallery_stats = [
    'total' => $db->count('gallery'),
    'active' => $db->count('gallery', ['is_active' => 1]),
    'inactive' => $db->count('gallery', ['is_active' => 0])
];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Gallery Management</h1>
                <a href="upload.php" class="btn btn-success">
                    <i class="fas fa-upload me-2"></i>Upload Images
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

            <!-- Stats and Filters -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-primary"><?php echo $gallery_stats['total']; ?></h5>
                                    <small class="text-muted">Total Images</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-success"><?php echo $gallery_stats['active']; ?></h5>
                                    <small class="text-muted">Active Images</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <h5 class="text-secondary"><?php echo $gallery_stats['inactive']; ?></h5>
                                    <small class="text-muted">Inactive Images</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <form method="GET" class="d-flex">
                        <select name="category" class="form-select me-2">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category); ?>"
                                    <?php echo $category_filter == $category ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(ucwords($category)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-outline-primary">Filter</button>
                    </form>
                </div>
            </div>

            <!-- Gallery Grid -->
            <?php if ($gallery_list): ?>
                <div class="row">
                    <?php foreach ($gallery_list as $image): ?>
                        <div class="col-md-3 mb-4">
                            <div class="card">
                                <div class="position-relative">
                                    <img src="<?php echo SITE_URL . '/' . $image['image_path']; ?>"
                                        class="card-img-top"
                                        style="height: 200px; object-fit: cover;"
                                        alt="<?php echo htmlspecialchars($image['title']); ?>">

                                    <!-- Status Badge -->
                                    <span class="position-absolute top-0 end-0 m-2 badge bg-<?php echo $image['is_active'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $image['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>

                                <div class="card-body">
                                    <h6 class="card-title"><?php echo htmlspecialchars($image['title']); ?></h6>

                                    <?php if ($image['description']): ?>
                                        <p class="card-text small text-muted">
                                            <?php echo htmlspecialchars(substr($image['description'], 0, 80)); ?>...
                                        </p>
                                    <?php endif; ?>

                                    <?php if ($image['category']): ?>
                                        <span class="badge bg-light text-dark mb-2"><?php echo htmlspecialchars($image['category']); ?></span>
                                    <?php endif; ?>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($image['uploaded_at'])); ?></small>

                                        <div class="btn-group">
                                            <!-- Toggle Status -->
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="image_id" value="<?php echo $image['id']; ?>">
                                                <input type="hidden" name="current_status" value="<?php echo $image['is_active']; ?>">
                                                <button type="submit" name="toggle_status"
                                                    class="btn btn-sm btn-outline-<?php echo $image['is_active'] ? 'warning' : 'success'; ?>"
                                                    title="<?php echo $image['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                    <i class="fas fa-<?php echo $image['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                                </button>
                                            </form>

                                            <!-- View Full Size -->
                                            <a href="<?php echo SITE_URL . '/' . $image['image_path']; ?>"
                                                target="_blank" class="btn btn-sm btn-outline-primary" title="View Full Size">
                                                <i class="fas fa-expand"></i>
                                            </a>

                                            <!-- Delete -->
                                            <a href="?delete=<?php echo $image['id']; ?>"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Are you sure you want to delete this image?')" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?>">Previous</a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $category_filter ? '&category=' . urlencode($category_filter) : ''; ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-images fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No images found</h4>
                    <p class="text-muted">Upload your first images to get started</p>
                    <a href="upload.php" class="btn btn-success">
                        <i class="fas fa-upload me-2"></i>Upload Images
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>