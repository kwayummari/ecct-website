<?php

/**
 * User Management - Admin Panel
 * ECCT Website User CRUD Operations
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require super admin role for user management
require_role('super_admin');

// Get database instance
$db = new Database();

// Page variables
$page_title = 'User Management - ECCT Admin';
$current_user = get_current_user();

// Set breadcrumbs
$breadcrumbs = [
    ['title' => 'Settings', 'url' => SITE_URL . '/admin/settings/'],
    ['title' => 'User Management']
];

// Handle actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $user_id = (int)$_GET['id'];

    if (!verify_csrf_token($_GET['token'] ?? '')) {
        set_flash('error', 'Invalid security token.');
    } else {
        $user = $db->selectOne('admin_users', ['id' => $user_id]);

        if (!$user) {
            set_flash('error', 'User not found.');
        } else {
            switch ($action) {
                case 'activate':
                    if ($db->update('admin_users', ['is_active' => 1], ['id' => $user_id])) {
                        set_flash('success', 'User activated successfully.');
                        log_activity('user_activate', "User activated: {$user['username']}");
                    } else {
                        set_flash('error', 'Failed to activate user.');
                    }
                    break;

                case 'deactivate':
                    // Prevent deactivating current user
                    if ($user_id == $current_user['id']) {
                        set_flash('error', 'You cannot deactivate your own account.');
                    } else {
                        if ($db->update('admin_users', ['is_active' => 0], ['id' => $user_id])) {
                            set_flash('success', 'User deactivated successfully.');
                            log_activity('user_deactivate', "User deactivated: {$user['username']}");
                        } else {
                            set_flash('error', 'Failed to deactivate user.');
                        }
                    }
                    break;

                case 'delete':
                    // Prevent deleting current user
                    if ($user_id == $current_user['id']) {
                        set_flash('error', 'You cannot delete your own account.');
                    } else {
                        if ($db->delete('admin_users', ['id' => $user_id])) {
                            set_flash('success', 'User deleted successfully.');
                            log_activity('user_delete', "User deleted: {$user['username']}");
                        } else {
                            set_flash('error', 'Failed to delete user.');
                        }
                    }
                    break;
            }
        }
    }

    redirect(SITE_URL . '/admin/settings/user-management.php');
}

// Get all users
$users = $db->select('admin_users', [], [
    'order_by' => 'created_at DESC'
]);

// Get user statistics
$user_stats = [
    'total_users' => $db->count('admin_users'),
    'active_users' => $db->count('admin_users', ['is_active' => 1]),
    'inactive_users' => $db->count('admin_users', ['is_active' => 0]),
    'super_admins' => $db->count('admin_users', ['role' => 'super_admin']),
    'admins' => $db->count('admin_users', ['role' => 'admin']),
    'editors' => $db->count('admin_users', ['role' => 'editor'])
];

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">User Management</h1>
                    <p class="text-muted">Manage admin users and their permissions</p>
                </div>
                <div class="page-actions">
                    <a href="add-user.php" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>Add New User
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $user_stats['total_users']; ?></h4>
                    <small class="text-muted">Total Users</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $user_stats['active_users']; ?></h4>
                    <small class="text-muted">Active Users</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-user-times"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $user_stats['inactive_users']; ?></h4>
                    <small class="text-muted">Inactive Users</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-crown"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $user_stats['super_admins']; ?></h4>
                    <small class="text-muted">Super Admins</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $user_stats['admins']; ?></h4>
                    <small class="text-muted">Admins</small>
                </div>
            </div>
        </div>

        <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
            <div class="card stat-card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 50px; height: 50px;">
                        <i class="fas fa-user-edit"></i>
                    </div>
                    <h4 class="mb-1"><?php echo $user_stats['editors']; ?></h4>
                    <small class="text-muted">Editors</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0">Admin Users</h5>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end">
                                <input type="text" class="form-control form-control-sm search-input"
                                    placeholder="Search users..."
                                    data-target=".user-row"
                                    style="max-width: 250px;">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Created</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users): ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr class="user-row">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="user-avatar bg-<?php echo $user['role'] === 'super_admin' ? 'danger' : ($user['role'] === 'admin' ? 'warning' : 'info'); ?> rounded-circle d-flex align-items-center justify-content-center me-3"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="fas fa-user text-white"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($user['full_name']); ?>
                                                            <?php if ($user['id'] == $current_user['id']): ?>
                                                                <small class="badge bg-primary ms-1">You</small>
                                                            <?php endif; ?>
                                                        </div>
                                                        <small class="text-muted"><?php echo htmlspecialchars($user['email']); ?></small>
                                                        <br><small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo match ($user['role']) {
                                                                            'super_admin' => 'danger',
                                                                            'admin' => 'warning text-dark',
                                                                            'editor' => 'info',
                                                                            default => 'secondary'
                                                                        }; ?>">
                                                    <?php echo match ($user['role']) {
                                                        'super_admin' => 'Super Admin',
                                                        'admin' => 'Admin',
                                                        'editor' => 'Editor',
                                                        default => ucfirst($user['role'])
                                                    }; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($user['last_login']): ?>
                                                    <span title="<?php echo format_date($user['last_login'], 'M j, Y g:i A'); ?>">
                                                        <?php echo time_ago($user['last_login']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">Never</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span title="<?php echo format_date($user['created_at'], 'M j, Y g:i A'); ?>">
                                                    <?php echo format_date($user['created_at'], 'M j, Y'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>"
                                                        class="btn btn-outline-primary" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <?php if ($user['id'] != $current_user['id']): ?>
                                                        <?php if ($user['is_active']): ?>
                                                            <a href="?action=deactivate&id=<?php echo $user['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                                class="btn btn-outline-warning"
                                                                title="Deactivate User"
                                                                onclick="return confirm('Are you sure you want to deactivate this user?')">
                                                                <i class="fas fa-user-times"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <a href="?action=activate&id=<?php echo $user['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                                class="btn btn-outline-success"
                                                                title="Activate User">
                                                                <i class="fas fa-user-check"></i>
                                                            </a>
                                                        <?php endif; ?>

                                                        <a href="?action=delete&id=<?php echo $user['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                                                            class="btn btn-outline-danger"
                                                            title="Delete User"
                                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="btn btn-outline-secondary disabled" title="Cannot modify your own account">
                                                            <i class="fas fa-lock"></i>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No users found.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role Permissions Info -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Role Permissions
                    </h6>
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="text-danger">
                                <i class="fas fa-crown me-2"></i>Super Admin
                            </h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-2"></i>All permissions</li>
                                <li><i class="fas fa-check text-success me-2"></i>User management</li>
                                <li><i class="fas fa-check text-success me-2"></i>System settings</li>
                                <li><i class="fas fa-check text-success me-2"></i>Database backup</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-warning">
                                <i class="fas fa-user-shield me-2"></i>Admin
                            </h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-2"></i>Content management</li>
                                <li><i class="fas fa-check text-success me-2"></i>User messages</li>
                                <li><i class="fas fa-check text-success me-2"></i>Analytics access</li>
                                <li><i class="fas fa-times text-danger me-2"></i>User management</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-info">
                                <i class="fas fa-user-edit me-2"></i>Editor
                            </h6>
                            <ul class="list-unstyled small">
                                <li><i class="fas fa-check text-success me-2"></i>Create/edit content</li>
                                <li><i class="fas fa-check text-success me-2"></i>Upload images</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Delete content</li>
                                <li><i class="fas fa-times text-danger me-2"></i>System settings</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Search functionality
        const searchInput = document.querySelector('.search-input');
        const userRows = document.querySelectorAll('.user-row');

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();

                userRows.forEach(function(row) {
                    const text = row.textContent.toLowerCase();
                    const shouldShow = text.includes(searchTerm);
                    row.style.display = shouldShow ? '' : 'none';
                });
            });
        }

        // Role filter functionality
        const roleFilters = document.querySelectorAll('.role-filter');
        roleFilters.forEach(function(filter) {
            filter.addEventListener('change', function() {
                const selectedRole = this.value;

                userRows.forEach(function(row) {
                    const roleBadge = row.querySelector('.badge');
                    const roleText = roleBadge ? roleBadge.textContent.toLowerCase() : '';

                    if (selectedRole === '' || roleText.includes(selectedRole.toLowerCase())) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    });
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>