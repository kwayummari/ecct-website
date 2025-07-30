<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

// Handle actions
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];

        // Get team member info first
        $member = $db->selectOne('team_members', ['id' => $id]);
        if ($member) {
            // Delete image file if exists
            if (!empty($member['image_path']) && file_exists('../../' . $member['image_path'])) {
                unlink('../../' . $member['image_path']);
            }

            // Delete from database
            if ($db->delete('team_members', ['id' => $id])) {
                $_SESSION['message'] = 'Team member deleted successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error deleting team member';
                $_SESSION['message_type'] = 'error';
            }
        }
    } elseif ($_POST['action'] === 'toggle_status' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $member = $db->selectOne('team_members', ['id' => $id]);

        if ($member) {
            $new_status = $member['is_active'] ? 0 : 1;
            if ($db->update('team_members', ['is_active' => $new_status], ['id' => $id])) {
                $_SESSION['message'] = 'Team member status updated successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating team member status';
                $_SESSION['message_type'] = 'error';
            }
        }
    } elseif ($_POST['action'] === 'toggle_leadership' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $member = $db->selectOne('team_members', ['id' => $id]);

        if ($member) {
            $new_leadership = $member['is_leadership'] ? 0 : 1;
            if ($db->update('team_members', ['is_leadership' => $new_leadership], ['id' => $id])) {
                $_SESSION['message'] = 'Team member leadership status updated successfully';
                $_SESSION['message_type'] = 'success';
            } else {
                $_SESSION['message'] = 'Error updating team member leadership status';
                $_SESSION['message_type'] = 'error';
            }
        }
    }
}

// Get search and filter parameters
$search = $_GET['search'] ?? '';
$department_filter = $_GET['department'] ?? '';
$leadership_filter = $_GET['leadership'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Build query conditions
$conditions = [];
$query_parts = [];

if (!empty($search)) {
    $query_parts[] = "(name LIKE :search OR position LIKE :search OR email LIKE :search)";
}

if (!empty($department_filter)) {
    $conditions['department'] = $department_filter;
}

if ($leadership_filter !== '') {
    $conditions['is_leadership'] = (int)$leadership_filter;
}

if ($status_filter !== '') {
    $conditions['is_active'] = (int)$status_filter;
}

// Get total count for pagination
$total_count = $db->count('team_members', $conditions, $query_parts);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;
$total_pages = ceil($total_count / $per_page);

// Get team members
$team_members = $db->select('team_members', $conditions, [
    'order_by' => 'sort_order ASC, name ASC',
    'limit' => $per_page,
    'offset' => $offset,
    'query_parts' => $query_parts,
    'search' => $search
]);

$page_title = "Team Management";
require_once ECCT_ROOT . '/admin/includes/header.php';
?>

<div class="d-flex">
    <?php include ECCT_ROOT . '/admin/includes/sidebar.php'; ?>

    <main class="flex-grow-1 p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Team Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="create.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Add New Member
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
                        <div class="col-md-3">
                            <label for="search" class="form-label">Search</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Search team members...">
                        </div>
                        <div class="col-md-3">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select" id="department" name="department">
                                <option value="">All Departments</option>
                                <option value="management" <?php echo $department_filter === 'management' ? 'selected' : ''; ?>>Management</option>
                                <option value="technical" <?php echo $department_filter === 'technical' ? 'selected' : ''; ?>>Technical</option>
                                <option value="finance" <?php echo $department_filter === 'finance' ? 'selected' : ''; ?>>Finance</option>
                                <option value="operations" <?php echo $department_filter === 'operations' ? 'selected' : ''; ?>>Operations</option>
                                <option value="communications" <?php echo $department_filter === 'communications' ? 'selected' : ''; ?>>Communications</option>
                                <option value="field" <?php echo $department_filter === 'field' ? 'selected' : ''; ?>>Field</option>
                                <option value="other" <?php echo $department_filter === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="leadership" class="form-label">Leadership</label>
                            <select class="form-select" id="leadership" name="leadership">
                                <option value="">All</option>
                                <option value="1" <?php echo $leadership_filter === '1' ? 'selected' : ''; ?>>Leadership</option>
                                <option value="0" <?php echo $leadership_filter === '0' ? 'selected' : ''; ?>>Staff</option>
                            </select>
                        </div>
                        <div class="col-md-2">
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

            <!-- Team Members Grid -->
            <div class="row">
                <?php if (empty($team_members)): ?>
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h4 class="text-muted">No Team Members Found</h4>
                            <p class="text-muted">
                                <?php if (empty($search) && empty($department_filter) && $leadership_filter === '' && $status_filter === ''): ?>
                                    No team members have been added yet.
                                <?php else: ?>
                                    No team members match your current filters.
                                <?php endif; ?>
                            </p>
                            <a href="create.php" class="btn btn-primary">Add First Team Member</a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($team_members as $member): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="position-relative">
                                    <?php if (!empty($member['image_path']) && file_exists('../../' . $member['image_path'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($member['image_path']); ?>"
                                            alt="<?php echo htmlspecialchars($member['name']); ?>"
                                            class="card-img-top" style="height: 250px; object-fit: cover;">
                                    <?php else: ?>
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                                            style="height: 250px;">
                                            <i class="fas fa-user fa-3x text-muted"></i>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Status badges -->
                                    <div class="position-absolute top-0 end-0 p-2">
                                        <?php if ($member['is_leadership']): ?>
                                            <span class="badge bg-warning mb-1">Leadership</span><br>
                                        <?php endif; ?>
                                        <span class="badge <?php echo $member['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $member['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($member['name']); ?></h5>
                                    <p class="card-text">
                                        <strong><?php echo htmlspecialchars($member['position']); ?></strong><br>
                                        <span class="badge bg-info"><?php echo ucfirst(htmlspecialchars($member['department'])); ?></span>
                                    </p>

                                    <?php if (!empty($member['bio'])): ?>
                                        <p class="card-text text-muted">
                                            <?php echo htmlspecialchars(substr($member['bio'], 0, 100)) . (strlen($member['bio']) > 100 ? '...' : ''); ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($member['email'])): ?>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-envelope me-1"></i>
                                                <?php echo htmlspecialchars($member['email']); ?>
                                            </small>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="card-footer bg-transparent">
                                    <div class="d-flex gap-1">
                                        <a href="edit.php?id=<?php echo $member['id']; ?>"
                                            class="btn btn-sm btn-outline-primary flex-fill">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>

                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $member['is_active'] ? 'btn-warning' : 'btn-success'; ?>"
                                                title="<?php echo $member['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>

                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="toggle_leadership">
                                            <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                            <button type="submit" class="btn btn-sm <?php echo $member['is_leadership'] ? 'btn-warning' : 'btn-outline-warning'; ?>"
                                                title="<?php echo $member['is_leadership'] ? 'Remove from leadership' : 'Add to leadership'; ?>">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                            title="Delete"
                                            onclick="confirmDelete(<?php echo $member['id']; ?>, '<?php echo htmlspecialchars(addslashes($member['name'])); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Team members pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department_filter); ?>&leadership=<?php echo urlencode($leadership_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department_filter); ?>&leadership=<?php echo urlencode($leadership_filter); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&department=<?php echo urlencode($department_filter); ?>&leadership=<?php echo urlencode($leadership_filter); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
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
                        <p>Are you sure you want to delete team member <strong id="memberName"></strong>?</p>
                        <p class="text-muted">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" id="deleteId">
                            <button type="submit" class="btn btn-danger">Delete Member</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function confirmDelete(id, name) {
                document.getElementById('deleteId').value = id;
                document.getElementById('memberName').textContent = name;
                new bootstrap.Modal(document.getElementById('deleteModal')).show();
            }
        </script>

</div>
</main>
</div>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>