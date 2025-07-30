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
    
    // Get program details before deletion
    $program = $db->selectOne('programs', ['id' => $id]);
    
    if ($program && $db->delete('programs', ['id' => $id])) {
        // Delete featured image if exists
        if ($program['featured_image'] && file_exists(ECCT_ROOT . '/' . $program['featured_image'])) {
            unlink(ECCT_ROOT . '/' . $program['featured_image']);
        }
        $success = 'Program deleted successfully';
    } else {
        $error = 'Error deleting program';
    }
}

// Handle status toggle
if (isset($_POST['toggle_status'])) {
    $id = (int)$_POST['program_id'];
    $program = $db->selectOne('programs', ['id' => $id]);
    
    if ($program) {
        $new_status = $program['is_active'] ? 0 : 1;
        if ($db->update('programs', ['is_active' => $new_status], ['id' => $id])) {
            $success = 'Program status updated successfully';
        } else {
            $error = 'Error updating program status';
        }
    }
}

// Handle featured toggle
if (isset($_POST['toggle_featured'])) {
    $id = (int)$_POST['program_id'];
    $program = $db->selectOne('programs', ['id' => $id]);
    
    if ($program) {
        $new_featured = $program['is_featured'] ? 0 : 1;
        if ($db->update('programs', ['is_featured' => $new_featured], ['id' => $id])) {
            $success = 'Program featured status updated successfully';
        } else {
            $error = 'Error updating program featured status';
        }
    }
}

// Get filters
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Build conditions
$conditions = [];
if ($category) {
    $conditions['category'] = $category;
}
if ($status !== '') {
    $conditions['status'] = $status;
}
if ($search) {
    $conditions['title'] = ['LIKE', "%$search%"];
}

// Get programs with pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$result = $db->paginate('programs', $page, 20, $conditions, [
    'order_by' => 'created_at DESC'
]);

$programs = $result['data'];
$pagination = $result['pagination'];

// Get available categories
$categories = $db->raw("SELECT DISTINCT category FROM programs WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categories ? $categories->fetchAll(PDO::FETCH_COLUMN) : [];

$page_title = "Programs Management";
require_once ECCT_ROOT . '/admin/includes/header.php';
?>

<div class="d-flex">
    <?php include ECCT_ROOT . '/admin/includes/sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Programs Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add New Program
                    </a>
                </div>
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

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search programs...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="upcoming" <?php echo $status === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="completed" <?php echo $status === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="paused" <?php echo $status === 'paused' ? 'selected' : ''; ?>>Paused</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="list.php" class="btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Programs Table -->
            <div class="card">
                <div class="card-body">
                    <?php if (empty($programs)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-leaf fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No programs found</h5>
                            <p class="text-muted">Start by adding your first program.</p>
                            <a href="create.php" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Add First Program
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Duration</th>
                                        <th>Featured</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($programs as $program): ?>
                                        <tr>
                                            <td>
                                                <?php if ($program['featured_image']): ?>
                                                    <img src="<?php echo SITE_URL . '/' . $program['featured_image']; ?>" 
                                                         alt="<?php echo htmlspecialchars($program['title']); ?>" 
                                                         class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" 
                                                         style="width: 60px; height: 60px; border-radius: 4px;">
                                                        <i class="fas fa-leaf text-muted"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div>
                                                    <a href="edit.php?id=<?php echo $program['id']; ?>" class="text-decoration-none fw-semibold">
                                                        <?php echo htmlspecialchars($program['title']); ?>
                                                    </a>
                                                    <?php if ($program['excerpt']): ?>
                                                        <div class="text-muted small mt-1">
                                                            <?php echo htmlspecialchars(truncate_text($program['excerpt'], 60)); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($program['category']): ?>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars(ucfirst($program['category'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_colors = [
                                                    'active' => 'success',
                                                    'upcoming' => 'warning',
                                                    'completed' => 'info',
                                                    'paused' => 'secondary'
                                                ];
                                                $color = $status_colors[$program['status']] ?? 'secondary';
                                                ?>
                                                <span class="badge bg-<?php echo $color; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($program['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($program['duration'] ?: 'N/A'); ?></td>
                                            <td>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                                                    <button type="submit" name="toggle_featured" 
                                                            class="btn btn-sm <?php echo $program['is_featured'] ? 'btn-warning' : 'btn-outline-warning'; ?>" 
                                                            title="<?php echo $program['is_featured'] ? 'Remove from featured' : 'Add to featured'; ?>">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                            </td>
                                            <td><?php echo format_date($program['created_at'], 'M j, Y'); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="edit.php?id=<?php echo $program['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="program_id" value="<?php echo $program['id']; ?>">
                                                        <button type="submit" name="toggle_status" 
                                                                class="btn btn-sm <?php echo $program['is_active'] ? 'btn-outline-warning' : 'btn-outline-success'; ?>" 
                                                                title="<?php echo $program['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                            <i class="fas <?php echo $program['is_active'] ? 'fa-pause' : 'fa-play'; ?>"></i>
                                                        </button>
                                                    </form>
                                                    <a href="?delete=<?php echo $program['id']; ?>" 
                                                       class="btn btn-sm btn-outline-danger" 
                                                       title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this program?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category); ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>