<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Handle status update
if (isset($_POST['update_status'])) {
    $volunteer_id = (int)$_POST['volunteer_id'];
    $new_status = $_POST['status'];

    $data = [
        'status' => $new_status,
        'processed_by' => $current_user['id'],
        'processed_at' => date('Y-m-d H:i:s')
    ];

    if ($db->update('volunteers', $data, ['id' => $volunteer_id])) {
        $success = 'Volunteer status updated successfully';
    } else {
        $error = 'Error updating volunteer status';
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->delete('volunteers', ['id' => $id])) {
        $success = 'Volunteer application deleted successfully';
    } else {
        $error = 'Error deleting volunteer application';
    }
}

// Filter by status
$status_filter = $_GET['status'] ?? '';
$conditions = [];
if ($status_filter) {
    $conditions['status'] = $status_filter;
}

// Get all volunteers
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$result = $db->paginate('volunteers', $page, $per_page, $conditions, ['order_by' => 'applied_at DESC']);
$volunteers_list = $result['data'];
$pagination = $result['pagination'];

// Get status counts
$status_counts = [
    'all' => $db->count('volunteers'),
    'pending' => $db->count('volunteers', ['status' => 'pending']),
    'approved' => $db->count('volunteers', ['status' => 'approved']),
    'active' => $db->count('volunteers', ['status' => 'active']),
    'inactive' => $db->count('volunteers', ['status' => 'inactive'])
];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Volunteer Applications</h1>
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

            <!-- Status Filter Tabs -->
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo !$status_filter ? 'active' : ''; ?>" href="list.php">
                        All <span class="badge bg-secondary ms-1"><?php echo $status_counts['all']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'pending' ? 'active' : ''; ?>" href="list.php?status=pending">
                        Pending <span class="badge bg-warning ms-1"><?php echo $status_counts['pending']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'approved' ? 'active' : ''; ?>" href="list.php?status=approved">
                        Approved <span class="badge bg-info ms-1"><?php echo $status_counts['approved']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'active' ? 'active' : ''; ?>" href="list.php?status=active">
                        Active <span class="badge bg-success ms-1"><?php echo $status_counts['active']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'inactive' ? 'active' : ''; ?>" href="list.php?status=inactive">
                        Inactive <span class="badge bg-secondary ms-1"><?php echo $status_counts['inactive']; ?></span>
                    </a>
                </li>
            </ul>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Volunteer</th>
                                    <th>Contact</th>
                                    <th>Skills/Interests</th>
                                    <th>Status</th>
                                    <th>Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($volunteers_list): ?>
                                    <?php foreach ($volunteers_list as $volunteer): ?>
                                        <tr>
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($volunteer['age']); ?> years old</small>
                                            </td>
                                            <td>
                                                <div><?php echo htmlspecialchars($volunteer['email']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($volunteer['phone']); ?></small>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($volunteer['skills'], 0, 60)); ?>...</small>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = match ($volunteer['status']) {
                                                    'pending' => 'bg-warning',
                                                    'approved' => 'bg-info',
                                                    'active' => 'bg-success',
                                                    'inactive' => 'bg-secondary',
                                                    default => 'bg-light text-dark'
                                                };
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($volunteer['status']); ?></span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($volunteer['applied_at'])); ?></td>
                                            <td>
                                                <a href="view.php?id=<?php echo $volunteer['id']; ?>" class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                <!-- Quick Status Update -->
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="volunteer_id" value="<?php echo $volunteer['id']; ?>">
                                                                <input type="hidden" name="status" value="approved">
                                                                <button type="submit" name="update_status" class="dropdown-item">
                                                                    <i class="fas fa-check text-info me-2"></i>Approve
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="volunteer_id" value="<?php echo $volunteer['id']; ?>">
                                                                <input type="hidden" name="status" value="active">
                                                                <button type="submit" name="update_status" class="dropdown-item">
                                                                    <i class="fas fa-user-check text-success me-2"></i>Activate
                                                                </button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="volunteer_id" value="<?php echo $volunteer['id']; ?>">
                                                                <input type="hidden" name="status" value="inactive">
                                                                <button type="submit" name="update_status" class="dropdown-item">
                                                                    <i class="fas fa-user-times text-secondary me-2"></i>Deactivate
                                                                </button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>

                                                <a href="?delete=<?php echo $volunteer['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this application?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3"></i>
                                            <p>No volunteer applications found</p>
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
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?>">Next</a>
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