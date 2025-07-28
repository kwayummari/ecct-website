<?php
define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($db->delete('campaigns', ['id' => $id])) {
        $success = 'Campaign deleted successfully';
    } else {
        $error = 'Error deleting campaign';
    }
}

// Get all campaigns
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$result = $db->paginate('campaigns', $page, $per_page, [], ['order_by' => 'created_at DESC']);
$campaigns_list = $result['data'];
$pagination = $result['pagination'];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Campaigns</h1>
                <a href="add.php" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>Add Campaign
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

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Status</th>
                                    <th>Goal</th>
                                    <th>Raised</th>
                                    <th>Period</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($campaigns_list): ?>
                                    <?php foreach ($campaigns_list as $campaign): ?>
                                        <tr>
                                            <td>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($campaign['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($campaign['location']); ?></small>
                                            </td>
                                            <td>
                                                <?php
                                                $status_class = match ($campaign['status']) {
                                                    'active' => 'bg-success',
                                                    'completed' => 'bg-primary',
                                                    'planning' => 'bg-warning',
                                                    'cancelled' => 'bg-danger',
                                                    default => 'bg-secondary'
                                                };
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($campaign['status']); ?></span>
                                            </td>
                                            <td>$<?php echo number_format($campaign['goal_amount'], 0); ?></td>
                                            <td>$<?php echo number_format($campaign['raised_amount'], 0); ?></td>
                                            <td>
                                                <?php echo date('M j', strtotime($campaign['start_date'])); ?> -
                                                <?php echo date('M j, Y', strtotime($campaign['end_date'])); ?>
                                            </td>
                                            <td>
                                                <a href="edit.php?id=<?php echo $campaign['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="?delete=<?php echo $campaign['id']; ?>"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Are you sure you want to delete this campaign?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-bullhorn fa-3x mb-3"></i>
                                            <p>No campaigns found</p>
                                            <a href="add.php" class="btn btn-success">Add First Campaign</a>
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
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                    <li class="page-item <?php echo ($i == $pagination['current_page']) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($pagination['has_next']): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?>">Next</a>
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