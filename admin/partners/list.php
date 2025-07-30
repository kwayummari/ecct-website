<?php
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../includes/auth_check.php';

$db = new Database();

// Handle actions
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];

        // Get partner info first
        $partner = $db->selectOne('partners', ['id' => $id]);
        if ($partner) {
            // Delete logo file if exists
            if (!empty($partner['logo_path']) && file_exists('../../' . $partner['logo_path'])) {
                unlink('../../' . $partner['logo_path']);
            }

            // Delete from database
            if ($db->delete('partners', ['id' => $id])) {
                $_SESSION['message'] = 'Partner deleted successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error deleting partner';
                $_SESSION['message_type'] = 'error';
            }
        }
    } elseif ($_POST['action'] === 'toggle_status' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $partner = $db->selectOne('partners', ['id' => $id]);

        if ($partner) {
            $new_status = $partner['is_active'] ? 0 : 1;
            if ($db->update('partners', ['is_active' => $new_status], ['id' => $id])) {
                $_SESSION['message'] = 'Partner status updated successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating partner status';
                $_SESSION['message_type'] = 'error';
            }
        }
    } elseif ($_POST['action'] === 'toggle_featured' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $partner = $db->selectOne('partners', ['id' => $id]);

        if ($partner) {
            $new_featured = $partner['is_featured'] ? 0 : 1;
            if ($db->update('partners', ['is_featured' => $new_featured], ['id' => $id])) {
                $_SESSION['message'] = 'Partner featured status updated successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating partner featured status';
                $_SESSION['message_type'] = 'error';
            }
        }
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query conditions
$conditions = [];
$query_parts = [];

if (!empty($search)) {
    $query_parts[] = "(name LIKE :search OR description LIKE :search OR website_url LIKE :search)";
}

if (!empty($type_filter)) {
    $conditions['partnership_type'] = $type_filter;
}

if ($status_filter !== '') {
    $conditions['is_active'] = (int)$status_filter;
}

// Get total count for pagination
$total_count = $db->count('partners', $conditions, $query_parts);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;
$total_pages = ceil($total_count / $per_page);

// Get partners
$partners = $db->select('partners', $conditions, [
    'order_by' => 'sort_order ASC, name ASC',
    'limit' => $per_page,
    'offset' => $offset,
    'query_parts' => $query_parts,
    'search' => $search
]);

$page_title = "Partners Management";
include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Partners Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="create.php" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Add New Partner
            </a>
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php
        unset($_SESSION['message'], $_SESSION['message_type']);
    endif;
    ?>

    <!-- Search and Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                        value="<?php echo htmlspecialchars($search); ?>"
                        placeholder="Search partners...">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Partnership Type</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">All Types</option>
                        <option value="sponsor" <?php echo $type_filter === 'sponsor' ? 'selected' : ''; ?>>Sponsor</option>
                        <option value="implementation" <?php echo $type_filter === 'implementation' ? 'selected' : ''; ?>>Implementation</option>
                        <option value="technical" <?php echo $type_filter === 'technical' ? 'selected' : ''; ?>>Technical</option>
                        <option value="funding" <?php echo $type_filter === 'funding' ? 'selected' : ''; ?>>Funding</option>
                        <option value="other" <?php echo $type_filter === 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="1" <?php echo $status_filter === '1' ? 'selected' : ''; ?>>Active</option>
                        <option value="0" <?php echo $status_filter === '0' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Partners List -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Partners (<?php echo $total_count; ?>)</h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($partners)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No Partners Found</h4>
                    <p class="text-muted">
                        <?php if (empty($search) && empty($type_filter) && $status_filter === ''): ?>
                            No partners have been added yet.
                        <?php else: ?>
                            No partners match your current filters.
                        <?php endif; ?>
                    </p>
                    <a href="create.php" class="btn btn-primary">Add First Partner</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Logo</th>
                                <th>Name</th>
                                <th>Partnership Type</th>
                                <th>Website</th>
                                <th>Status</th>
                                <th>Featured</th>
                                <th>Sort Order</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($partners as $partner): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($partner['logo_path']) && file_exists('../../' . $partner['logo_path'])): ?>
                                            <img src="../../<?php echo htmlspecialchars($partner['logo_path']); ?>"
                                                alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                                class="img-thumbnail" style="width: 60px; height: 40px; object-fit: contain;">
                                        <?php else: ?>
                                            <div class="bg-light d-flex align-items-center justify-content-center"
                                                style="width: 60px; height: 40px; border-radius: 4px;">
                                                <i class="fas fa-building text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($partner['name']); ?></strong>
                                        <?php if (!empty($partner['description'])): ?>
                                            <br><small class="text-muted">
                                                <?php echo htmlspecialchars(substr($partner['description'], 0, 60)) . (strlen($partner['description']) > 60 ? '...' : ''); ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo ucfirst(htmlspecialchars($partner['partnership_type'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($partner['website_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($partner['website_url']); ?>"
                                                target="_blank" rel="noopener noreferrer"
                                                class="text-decoration-none">
                                                <i class="fas fa-external-link-alt me-1"></i>Visit
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $partner['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $partner['is_active'] ? 'btn-success' : 'btn-secondary'; ?>"
                                                title="Click to <?php echo $partner['is_active'] ? 'deactivate' : 'activate'; ?>">
                                                <?php echo $partner['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_featured">
                                            <input type="hidden" name="id" value="<?php echo $partner['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $partner['is_featured'] ? 'btn-warning' : 'btn-outline-warning'; ?>"
                                                title="Click to <?php echo $partner['is_featured'] ? 'unfeature' : 'feature'; ?>">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?php echo $partner['sort_order']; ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="edit.php?id=<?php echo $partner['id']; ?>"
                                                class="btn btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger"
                                                title="Delete"
                                                onclick="confirmDelete(<?php echo $partner['id']; ?>, '<?php echo htmlspecialchars(addslashes($partner['name'])); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <nav aria-label="Partners pagination" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                    </li>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the partner <strong id="partnerName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" class="d-inline">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteId">
                    <button type="submit" class="btn btn-danger">Delete Partner</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(id, name) {
        document.getElementById('deleteId').value = id;
        document.getElementById('partnerName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php include '../includes/footer.php'; ?>