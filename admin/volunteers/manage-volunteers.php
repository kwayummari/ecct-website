<?php

/**
 * Manage Volunteers - ECCT Admin Panel
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_permission('view_volunteers');

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
        case 'approve':
            if ($db->update('volunteers', ['status' => 'approved', 'processed_at' => date('Y-m-d H:i:s'), 'processed_by' => get_current_user()['id']], ['id' => $id])) {
                log_activity('volunteer_approve', "Volunteer approved: ID $id");
                set_flash('success', 'Volunteer application approved.');
            } else {
                set_flash('error', 'Failed to approve volunteer.');
            }
            break;

        case 'reject':
            if ($db->update('volunteers', ['status' => 'inactive', 'processed_at' => date('Y-m-d H:i:s'), 'processed_by' => get_current_user()['id']], ['id' => $id])) {
                log_activity('volunteer_reject', "Volunteer rejected: ID $id");
                set_flash('success', 'Volunteer application rejected.');
            } else {
                set_flash('error', 'Failed to reject volunteer.');
            }
            break;

        case 'activate':
            if ($db->update('volunteers', ['status' => 'active'], ['id' => $id])) {
                log_activity('volunteer_activate', "Volunteer activated: ID $id");
                set_flash('success', 'Volunteer activated.');
            } else {
                set_flash('error', 'Failed to activate volunteer.');
            }
            break;

        case 'delete':
            if ($db->delete('volunteers', ['id' => $id])) {
                log_activity('volunteer_delete', "Volunteer deleted: ID $id");
                set_flash('success', 'Volunteer application deleted.');
            } else {
                set_flash('error', 'Failed to delete volunteer.');
            }
            break;

        case 'add_note':
            $note = sanitize_input($_POST['note'] ?? '');
            if (!empty($note)) {
                $current_notes = $db->selectOne('volunteers', ['id' => $id])['notes'] ?? '';
                $new_notes = $current_notes . "\n" . date('Y-m-d H:i:s') . " - " . get_current_user()['name'] . ": " . $note;
                if ($db->update('volunteers', ['notes' => trim($new_notes)], ['id' => $id])) {
                    log_activity('volunteer_note_add', "Note added to volunteer: ID $id");
                    set_flash('success', 'Note added successfully.');
                } else {
                    set_flash('error', 'Failed to add note.');
                }
            }
            break;
    }

    redirect(current_url());
}

// Export functionality
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    require_permission('manage_content'); // Higher permission for export

    $status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
    $conditions = [];
    if ($status_filter) {
        $conditions['status'] = $status_filter;
    }

    $volunteers = $db->select('volunteers', $conditions, ['order_by' => 'applied_at DESC']);

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ecct_volunteers_' . date('Y-m-d') . '.csv"');

    $output = fopen('php://output', 'w');

    // CSV headers
    fputcsv($output, [
        'ID',
        'Name',
        'Email',
        'Phone',
        'Age',
        'Gender',
        'City',
        'Region',
        'Education',
        'Occupation',
        'Areas of Interest',
        'Availability',
        'Status',
        'Applied Date',
        'Processed Date'
    ]);

    // CSV data
    foreach ($volunteers as $volunteer) {
        $age = $volunteer['date_of_birth'] ? date_diff(date_create($volunteer['date_of_birth']), date_create('today'))->y : '';

        fputcsv($output, [
            $volunteer['id'],
            $volunteer['first_name'] . ' ' . $volunteer['last_name'],
            $volunteer['email'],
            $volunteer['phone'],
            $age,
            $volunteer['gender'],
            $volunteer['city'],
            $volunteer['region'],
            $volunteer['education_level'],
            $volunteer['occupation'],
            $volunteer['areas_of_interest'],
            $volunteer['availability'],
            ucfirst($volunteer['status']),
            format_date($volunteer['applied_at']),
            $volunteer['processed_at'] ? format_date($volunteer['processed_at']) : ''
        ]);
    }

    fclose($output);
    exit;
}

// Get parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';

// Build conditions
$conditions = [];
if ($status && in_array($status, ['pending', 'approved', 'active', 'inactive'])) {
    $conditions['status'] = $status;
}

// Get volunteers with pagination
if ($search) {
    $volunteer_results = $db->search('volunteers', $search, ['first_name', 'last_name', 'email'], $conditions, [
        'order_by' => 'applied_at DESC'
    ]);
    $total_volunteers = count($volunteer_results ?: []);
    $total_pages = ceil($total_volunteers / ITEMS_PER_PAGE);
    $offset = ($page - 1) * ITEMS_PER_PAGE;
    $volunteers = array_slice($volunteer_results ?: [], $offset, ITEMS_PER_PAGE);
    $pagination = [
        'current_page' => $page,
        'total_pages' => $total_pages,
        'has_prev' => $page > 1,
        'has_next' => $page < $total_pages
    ];
} else {
    $pagination_result = $db->paginate('volunteers', $page, ITEMS_PER_PAGE, $conditions, [
        'order_by' => 'applied_at DESC'
    ]);
    $volunteers = $pagination_result['data'];
    $pagination = $pagination_result['pagination'];
}

// Get statistics
$stats = [
    'total' => $db->count('volunteers'),
    'pending' => $db->count('volunteers', ['status' => 'pending']),
    'approved' => $db->count('volunteers', ['status' => 'approved']),
    'active' => $db->count('volunteers', ['status' => 'active']),
    'inactive' => $db->count('volunteers', ['status' => 'inactive'])
];

// Page variables
$page_title = 'Manage Volunteers - ECCT Admin';
$breadcrumbs = [
    ['title' => 'Volunteer Management']
];

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Manage Volunteers</h1>
                    <p class="text-muted">Review volunteer applications and manage volunteer database</p>
                </div>
                <div>
                    <a href="?export=csv<?php echo $status ? '&status=' . urlencode($status) : ''; ?>"
                        class="btn btn-success me-2">
                        <i class="fas fa-download me-2"></i>Export CSV
                    </a>
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-2"></i>View Form
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-primary mb-2">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['total']; ?></h4>
                    <p class="text-muted small mb-0">Total</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-warning mb-2">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['pending']; ?></h4>
                    <p class="text-muted small mb-0">Pending</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-info mb-2">
                        <i class="fas fa-check fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['approved']; ?></h4>
                    <p class="text-muted small mb-0">Approved</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-success mb-2">
                        <i class="fas fa-user-check fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['active']; ?></h4>
                    <p class="text-muted small mb-0">Active</p>
                </div>
            </div>
        </div>

        <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon text-secondary mb-2">
                        <i class="fas fa-user-times fa-2x"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $stats['inactive']; ?></h4>
                    <p class="text-muted small mb-0">Inactive</p>
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
                                    placeholder="Search volunteers...">
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
                                    <a href="?status=pending" class="btn btn-outline-warning <?php echo $status === 'pending' ? 'active' : ''; ?>">
                                        Pending (<?php echo $stats['pending']; ?>)
                                    </a>
                                    <a href="?status=approved" class="btn btn-outline-info <?php echo $status === 'approved' ? 'active' : ''; ?>">
                                        Approved
                                    </a>
                                    <a href="?status=active" class="btn btn-outline-success <?php echo $status === 'active' ? 'active' : ''; ?>">
                                        Active
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Volunteers Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if ($volunteers): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Volunteer</th>
                                        <th>Contact</th>
                                        <th>Areas of Interest</th>
                                        <th>Status</th>
                                        <th>Applied</th>
                                        <th width="200">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($volunteers as $volunteer): ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-1">
                                                        <?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?>
                                                    </h6>
                                                    <small class="text-muted">
                                                        <?php
                                                        $age = $volunteer['date_of_birth'] ? date_diff(date_create($volunteer['date_of_birth']), date_create('today'))->y : 'N/A';
                                                        echo $age . ' years • ' . ucfirst($volunteer['gender'] ?? 'N/A');
                                                        ?>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($volunteer['city'] . ', ' . $volunteer['region']); ?>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        <a href="mailto:<?php echo htmlspecialchars($volunteer['email']); ?>"
                                                            class="text-decoration-none">
                                                            <?php echo htmlspecialchars($volunteer['email']); ?>
                                                        </a>
                                                    </small>
                                                    <small class="text-muted d-block">
                                                        <i class="fas fa-phone me-1"></i>
                                                        <a href="tel:<?php echo htmlspecialchars($volunteer['phone']); ?>"
                                                            class="text-decoration-none">
                                                            <?php echo htmlspecialchars($volunteer['phone']); ?>
                                                        </a>
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars(truncate_text($volunteer['areas_of_interest'], 50)); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo match ($volunteer['status']) {
                                                                            'pending' => 'warning',
                                                                            'approved' => 'info',
                                                                            'active' => 'success',
                                                                            'inactive' => 'secondary',
                                                                            default => 'secondary'
                                                                        }; ?>">
                                                    <?php echo ucfirst($volunteer['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo format_date($volunteer['applied_at'], 'M j, Y'); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <!-- View Details -->
                                                    <button type="button" class="btn btn-sm btn-outline-info"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#volunteerModal<?php echo $volunteer['id']; ?>"
                                                        title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                    <!-- Status Actions -->
                                                    <?php if ($volunteer['status'] === 'pending'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="action" value="approve">
                                                            <input type="hidden" name="id" value="<?php echo $volunteer['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>

                                                        <form method="POST" class="d-inline">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="action" value="reject">
                                                            <input type="hidden" name="id" value="<?php echo $volunteer['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Reject">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php elseif ($volunteer['status'] === 'approved'): ?>
                                                        <form method="POST" class="d-inline">
                                                            <?php echo csrf_field(); ?>
                                                            <input type="hidden" name="action" value="activate">
                                                            <input type="hidden" name="id" value="<?php echo $volunteer['id']; ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="Activate">
                                                                <i class="fas fa-user-plus"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>

                                                    <!-- Delete -->
                                                    <form method="POST" class="d-inline"
                                                        onsubmit="return confirm('Are you sure you want to delete this volunteer application?');">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="id" value="<?php echo $volunteer['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Volunteer Details Modal -->
                                        <div class="modal fade" id="volunteerModal<?php echo $volunteer['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">
                                                            <?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <h6>Personal Information</h6>
                                                                <table class="table table-sm">
                                                                    <tr>
                                                                        <td><strong>Email:</strong></td>
                                                                        <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Phone:</strong></td>
                                                                        <td><?php echo htmlspecialchars($volunteer['phone']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Age:</strong></td>
                                                                        <td><?php echo $volunteer['date_of_birth'] ? date_diff(date_create($volunteer['date_of_birth']), date_create('today'))->y : 'N/A'; ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Gender:</strong></td>
                                                                        <td><?php echo ucfirst($volunteer['gender'] ?? 'N/A'); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Location:</strong></td>
                                                                        <td><?php echo htmlspecialchars($volunteer['city'] . ', ' . $volunteer['region']); ?></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td><strong>Education:</strong></td>
                                                                        <td><?php echo htmlspecialchars($volunteer['education_level']); ?></td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <h6>Volunteer Information</h6>
                                                                <p><strong>Areas of Interest:</strong><br>
                                                                    <?php echo htmlspecialchars($volunteer['areas_of_interest']); ?></p>
                                                                <p><strong>Availability:</strong><br>
                                                                    <?php echo htmlspecialchars($volunteer['availability'] ?? 'Not specified'); ?></p>
                                                                <p><strong>Skills:</strong><br>
                                                                    <?php echo htmlspecialchars($volunteer['skills'] ?? 'Not specified'); ?></p>
                                                            </div>
                                                        </div>

                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <h6>Motivation</h6>
                                                                <p><?php echo nl2br(htmlspecialchars($volunteer['motivation'])); ?></p>
                                                            </div>
                                                        </div>

                                                        <?php if (!empty($volunteer['notes'])): ?>
                                                            <div class="row mt-3">
                                                                <div class="col-12">
                                                                    <h6>Notes</h6>
                                                                    <div class="bg-light p-3 rounded">
                                                                        <?php echo nl2br(htmlspecialchars($volunteer['notes'])); ?>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php endif; ?>

                                                        <!-- Add Note -->
                                                        <div class="row mt-3">
                                                            <div class="col-12">
                                                                <form method="POST">
                                                                    <?php echo csrf_field(); ?>
                                                                    <input type="hidden" name="action" value="add_note">
                                                                    <input type="hidden" name="id" value="<?php echo $volunteer['id']; ?>">
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Add Note</label>
                                                                        <textarea class="form-control" name="note" rows="3"
                                                                            placeholder="Add a note about this volunteer..."></textarea>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-sm btn-primary">
                                                                        Add Note
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($pagination['total_pages'] > 1): ?>
                            <nav aria-label="Volunteers pagination" class="mt-4">
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
                        <!-- No Volunteers -->
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">
                                <?php echo ($search || $status) ? 'No volunteers found' : 'No volunteer applications yet'; ?>
                            </h4>
                            <p class="text-muted mb-4">
                                <?php if ($search || $status): ?>
                                    Try adjusting your search terms or filters.
                                <?php else: ?>
                                    Volunteer applications will appear here when submitted through the website.
                                <?php endif; ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>