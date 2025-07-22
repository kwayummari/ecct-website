<?php

/**
 * Admin Campaigns Management for ECCT Website
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_login();
require_permission('manage_campaigns');

// Get database instance
$db = new Database();

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    if (!validate_csrf()) {
        set_flash('error', 'Invalid security token.');
    } else {
        $action = $_POST['bulk_action'];
        $selected_ids = $_POST['selected_campaigns'] ?? [];

        if (!empty($selected_ids) && in_array($action, ['delete', 'activate', 'complete', 'feature', 'unfeature'])) {
            $success_count = 0;

            foreach ($selected_ids as $id) {
                $campaign_id = (int)$id;

                switch ($action) {
                    case 'delete':
                        if (can_perform('delete_campaigns')) {
                            // Get campaign info for logging
                            $campaign = $db->selectOne('campaigns', ['id' => $campaign_id]);
                            if ($campaign && $db->delete('campaigns', ['id' => $campaign_id])) {
                                log_activity('campaign_delete', "Campaign deleted: {$campaign['title']}", 'campaigns', $campaign_id);
                                $success_count++;
                            }
                        }
                        break;

                    case 'activate':
                        if ($db->update('campaigns', ['status' => 'active'], ['id' => $campaign_id])) {
                            log_activity('campaign_status_change', "Campaign activated: ID $campaign_id", 'campaigns', $campaign_id);
                            $success_count++;
                        }
                        break;

                    case 'complete':
                        if ($db->update('campaigns', ['status' => 'completed'], ['id' => $campaign_id])) {
                            log_activity('campaign_status_change', "Campaign marked as completed: ID $campaign_id", 'campaigns', $campaign_id);
                            $success_count++;
                        }
                        break;

                    case 'feature':
                        if ($db->update('campaigns', ['is_featured' => 1], ['id' => $campaign_id])) {
                            log_activity('campaign_feature', "Campaign featured: ID $campaign_id", 'campaigns', $campaign_id);
                            $success_count++;
                        }
                        break;

                    case 'unfeature':
                        if ($db->update('campaigns', ['is_featured' => 0], ['id' => $campaign_id])) {
                            log_activity('campaign_unfeature', "Campaign unfeatured: ID $campaign_id", 'campaigns', $campaign_id);
                            $success_count++;
                        }
                        break;
                }
            }

            if ($success_count > 0) {
                set_flash('success', "$success_count campaign(s) updated successfully.");
            } else {
                set_flash('error', 'No campaigns were updated.');
            }
        } else {
            set_flash('error', 'No campaigns selected or invalid action.');
        }

        redirect($_SERVER['REQUEST_URI']);
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));

// Build conditions
$conditions = [];
if ($status_filter && in_array($status_filter, ['planning', 'active', 'completed', 'cancelled'])) {
    $conditions['status'] = $status_filter;
}

// Handle search
if ($search) {
    $campaigns_result = $db->search('campaigns', $search, ['title', 'description', 'content'], $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $total_campaigns = count($campaigns_result ?: []);
    $campaigns = array_slice($campaigns_result ?: [], ($page - 1) * ITEMS_PER_PAGE, ITEMS_PER_PAGE);
    $total_pages = ceil($total_campaigns / ITEMS_PER_PAGE);
} else {
    $pagination_result = $db->paginate('campaigns', $page, ITEMS_PER_PAGE, $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $campaigns = $pagination_result['data'];
    $pagination = $pagination_result['pagination'];
}

// Get campaign statistics
$stats = [
    'total' => $db->count('campaigns'),
    'active' => $db->count('campaigns', ['status' => 'active']),
    'completed' => $db->count('campaigns', ['status' => 'completed']),
    'planning' => $db->count('campaigns', ['status' => 'planning']),
    'featured' => $db->count('campaigns', ['is_featured' => 1])
];

$page_title = 'Manage Campaigns - ECCT Admin';
include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Manage Campaigns</h1>
                    <p class="text-muted">Create and manage environmental conservation campaigns</p>
                </div>
                <div>
                    <a href="add-campaign.php" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Campaign
                    </a>
                </div>
            </div>

            <!-- Campaign Statistics -->
            <div class="row mb-4">
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-bullhorn fa-2x"></i>
                            </div>
                            <h5 class="mb-1"><?php echo $stats['total']; ?></h5>
                            <small class="text-muted">Total</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-success mb-2">
                                <i class="fas fa-play fa-2x"></i>
                            </div>
                            <h5 class="mb-1"><?php echo $stats['active']; ?></h5>
                            <small class="text-muted">Active</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-primary mb-2">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                            <h5 class="mb-1"><?php echo $stats['completed']; ?></h5>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-warning mb-2">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <h5 class="mb-1"><?php echo $stats['planning']; ?></h5>
                            <small class="text-muted">Planning</small>
                        </div>
                    </div>
                </div>
                <div class="col-xl-2 col-md-4 col-6 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="text-info mb-2">
                                <i class="fas fa-star fa-2x"></i>
                            </div>
                            <h5 class="mb-1"><?php echo $stats['featured']; ?></h5>
                            <small class="text-muted">Featured</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters and Search -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Search Campaigns</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Search by title, description...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="planning" <?php echo $status_filter === 'planning' ? 'selected' : ''; ?>>Planning</option>
                                <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-5 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search me-1"></i>Search
                            </button>
                            <a href="manage-campaigns.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Campaigns Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <?php if ($campaigns): ?>
                        <form method="POST" action="" id="campaignsForm">
                            <?php echo csrf_field(); ?>

                            <!-- Bulk Actions -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center">
                                    <select name="bulk_action" class="form-select me-2" style="width: auto;">
                                        <option value="">Bulk Actions</option>
                                        <option value="activate">Mark as Active</option>
                                        <option value="complete">Mark as Completed</option>
                                        <option value="feature">Feature</option>
                                        <option value="unfeature">Remove Feature</option>
                                        <?php if (can_perform('delete_campaigns')): ?>
                                            <option value="delete">Delete</option>
                                        <?php endif; ?>
                                    </select>
                                    <button type="submit" class="btn btn-outline-primary btn-sm" onclick="return confirmBulkAction()">
                                        Apply
                                    </button>
                                </div>
                                <div>
                                    <small class="text-muted">
                                        Showing <?php echo count($campaigns); ?>
                                        <?php if (isset($pagination)): ?>
                                            of <?php echo $pagination['total']; ?>
                                        <?php endif; ?> campaigns
                                    </small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
                                            <th>Campaign</th>
                                            <th>Status</th>
                                            <th>Location</th>
                                            <th>Dates</th>
                                            <th>Progress</th>
                                            <th>Featured</th>
                                            <th>Created</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($campaigns as $campaign): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input campaign-checkbox"
                                                        name="selected_campaigns[]" value="<?php echo $campaign['id']; ?>">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if ($campaign['featured_image']): ?>
                                                            <img src="<?php echo UPLOADS_URL . '/campaigns/' . $campaign['featured_image']; ?>"
                                                                alt="<?php echo htmlspecialchars($campaign['title']); ?>"
                                                                class="rounded me-3" style="width: 50px; height: 50px; object-fit: cover;">
                                                        <?php else: ?>
                                                            <div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                                <i class="fas fa-bullhorn text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h6 class="mb-1">
                                                                <a href="edit-campaign.php?id=<?php echo $campaign['id']; ?>"
                                                                    class="text-decoration-none">
                                                                    <?php echo htmlspecialchars($campaign['title']); ?>
                                                                </a>
                                                            </h6>
                                                            <small class="text-muted">
                                                                <?php echo htmlspecialchars(truncate_text($campaign['description'], 60)); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo match ($campaign['status']) {
                                                                                'active' => 'success',
                                                                                'completed' => 'primary',
                                                                                'planning' => 'warning',
                                                                                'cancelled' => 'danger',
                                                                                default => 'secondary'
                                                                            }; ?>">
                                                        <?php echo ucfirst($campaign['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo htmlspecialchars($campaign['location'] ?: 'Not specified'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php if ($campaign['start_date']): ?>
                                                            <?php echo format_date($campaign['start_date'], 'M j'); ?>
                                                            <?php if ($campaign['end_date']): ?>
                                                                - <?php echo format_date($campaign['end_date'], 'M j, Y'); ?>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            No dates set
                                                        <?php endif; ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <?php if ($campaign['goal_amount'] > 0): ?>
                                                        <?php
                                                        $progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                                        $progress = min(100, $progress);
                                                        ?>
                                                        <div class="progress" style="height: 8px;">
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                style="width: <?php echo $progress; ?>%"></div>
                                                        </div>
                                                        <small class="text-muted">
                                                            $<?php echo number_format($campaign['raised_amount']); ?> /
                                                            $<?php echo number_format($campaign['goal_amount']); ?>
                                                        </small>
                                                    <?php else: ?>
                                                        <small class="text-muted">No goal set</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($campaign['is_featured']): ?>
                                                        <i class="fas fa-star text-warning" title="Featured"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star text-muted" title="Not featured"></i>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo format_date($campaign['created_at']); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit-campaign.php?id=<?php echo $campaign['id']; ?>"
                                                            class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                                            class="btn btn-sm btn-outline-info" title="View" target="_blank">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                        <?php if (can_perform('delete_campaigns')): ?>
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                title="Delete" onclick="deleteCampaign(<?php echo $campaign['id']; ?>, '<?php echo htmlspecialchars(addslashes($campaign['title'])); ?>')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                            <nav aria-label="Campaigns pagination" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['has_prev']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                Next
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- No Campaigns -->
                        <div class="text-center py-5">
                            <i class="fas fa-bullhorn fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted mb-3">
                                <?php echo ($search || $status_filter) ? 'No campaigns found' : 'No campaigns yet'; ?>
                            </h5>
                            <p class="text-muted mb-4">
                                <?php if ($search || $status_filter): ?>
                                    Try adjusting your search criteria or filters.
                                <?php else: ?>
                                    Create your first environmental conservation campaign to get started.
                                <?php endif; ?>
                            </p>
                            <a href="add-campaign.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add New Campaign
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Select all checkbox functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.campaign-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Individual checkbox change
    document.querySelectorAll('.campaign-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const allCheckboxes = document.querySelectorAll('.campaign-checkbox');
            const selectAllCheckbox = document.getElementById('selectAll');
            const checkedCount = document.querySelectorAll('.campaign-checkbox:checked').length;

            selectAllCheckbox.checked = checkedCount === allCheckboxes.length;
            selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
        });
    });

    // Confirm bulk action
    function confirmBulkAction() {
        const selectedCount = document.querySelectorAll('.campaign-checkbox:checked').length;
        const action = document.querySelector('select[name="bulk_action"]').value;

        if (selectedCount === 0) {
            alert('Please select at least one campaign.');
            return false;
        }

        if (!action) {
            alert('Please select an action.');
            return false;
        }

        const actionNames = {
            'delete': 'delete',
            'activate': 'mark as active',
            'complete': 'mark as completed',
            'feature': 'feature',
            'unfeature': 'remove feature from'
        };

        return confirm(`Are you sure you want to ${actionNames[action]} ${selectedCount} campaign(s)?`);
    }

    // Delete single campaign
    function deleteCampaign(id, title) {
        if (confirm(`Are you sure you want to delete the campaign "${title}"? This action cannot be undone.`)) {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'delete-campaign.php';

            const csrfField = document.createElement('input');
            csrfField.type = 'hidden';
            csrfField.name = 'csrf_token';
            csrfField.value = '<?php echo get_csrf_token(); ?>';

            const idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'campaign_id';
            idField.value = id;

            form.appendChild(csrfField);
            form.appendChild(idField);
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>