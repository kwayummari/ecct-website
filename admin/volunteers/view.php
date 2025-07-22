<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Get volunteer
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$volunteer = $db->selectOne('volunteers', ['id' => $id]);

if (!$volunteer) {
    header('Location: list.php?error=Volunteer not found');
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    $notes = trim($_POST['notes']);

    $data = [
        'status' => $new_status,
        'notes' => $notes,
        'processed_by' => $current_user['id'],
        'processed_at' => date('Y-m-d H:i:s')
    ];

    if ($db->update('volunteers', $data, ['id' => $id])) {
        $success = 'Volunteer status updated successfully';
        $volunteer = $db->selectOne('volunteers', ['id' => $id]); // Refresh data
    } else {
        $error = 'Error updating volunteer status';
    }
}

// Get processor info if available
$processor = null;
if ($volunteer['processed_by']) {
    $processor = $db->selectOne('admin_users', ['id' => $volunteer['processed_by']]);
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Volunteer Application Details</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
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

            <div class="row">
                <div class="col-md-8">
                    <!-- Personal Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Full Name:</strong><br>
                                        <?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></p>

                                    <p><strong>Email:</strong><br>
                                        <a href="mailto:<?php echo htmlspecialchars($volunteer['email']); ?>">
                                            <?php echo htmlspecialchars($volunteer['email']); ?>
                                        </a>
                                    </p>

                                    <p><strong>Phone:</strong><br>
                                        <a href="tel:<?php echo htmlspecialchars($volunteer['phone']); ?>">
                                            <?php echo htmlspecialchars($volunteer['phone']); ?>
                                        </a>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Age:</strong><br>
                                        <?php echo htmlspecialchars($volunteer['age']); ?> years old</p>

                                    <p><strong>Location:</strong><br>
                                        <?php echo htmlspecialchars($volunteer['location']); ?></p>

                                    <p><strong>Occupation:</strong><br>
                                        <?php echo htmlspecialchars($volunteer['occupation']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Volunteer Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Volunteer Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Areas of Interest:</strong>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($volunteer['interests'])); ?></p>
                            </div>

                            <div class="mb-3">
                                <strong>Skills & Experience:</strong>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($volunteer['skills'])); ?></p>
                            </div>

                            <div class="mb-3">
                                <strong>Availability:</strong>
                                <p class="mt-1"><?php echo nl2br(htmlspecialchars($volunteer['availability'])); ?></p>
                            </div>

                            <?php if ($volunteer['experience']): ?>
                                <div class="mb-3">
                                    <strong>Previous Experience:</strong>
                                    <p class="mt-1"><?php echo nl2br(htmlspecialchars($volunteer['experience'])); ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($volunteer['motivation']): ?>
                                <div class="mb-3">
                                    <strong>Motivation:</strong>
                                    <p class="mt-1"><?php echo nl2br(htmlspecialchars($volunteer['motivation'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Admin Notes -->
                    <?php if ($volunteer['notes']): ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Admin Notes</h5>
                            </div>
                            <div class="card-body">
                                <p><?php echo nl2br(htmlspecialchars($volunteer['notes'])); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <!-- Status & Actions -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Status & Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Current Status:</strong><br>
                                <?php
                                $status_class = match ($volunteer['status']) {
                                    'pending' => 'bg-warning',
                                    'approved' => 'bg-info',
                                    'active' => 'bg-success',
                                    'inactive' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                };
                                ?>
                                <span class="badge <?php echo $status_class; ?> mt-1"><?php echo ucfirst($volunteer['status']); ?></span>
                            </div>

                            <div class="mb-3">
                                <strong>Applied:</strong><br>
                                <?php echo date('F j, Y \a\t g:i A', strtotime($volunteer['applied_at'])); ?>
                            </div>

                            <?php if ($volunteer['processed_at']): ?>
                                <div class="mb-3">
                                    <strong>Last Updated:</strong><br>
                                    <?php echo date('F j, Y \a\t g:i A', strtotime($volunteer['processed_at'])); ?>
                                    <?php if ($processor): ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($processor['full_name']); ?></small>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Status Update Form -->
                            <form method="POST" class="mt-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Update Status:</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="pending" <?php echo ($volunteer['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved" <?php echo ($volunteer['status'] == 'approved') ? 'selected' : ''; ?>>Approved</option>
                                        <option value="active" <?php echo ($volunteer['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo ($volunteer['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes:</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="4"
                                        placeholder="Add any notes about this volunteer..."><?php echo htmlspecialchars($volunteer['notes']); ?></textarea>
                                </div>

                                <button type="submit" name="update_status" class="btn btn-success w-100">
                                    <i class="fas fa-save me-2"></i>Update Status
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="mailto:<?php echo htmlspecialchars($volunteer['email']); ?>" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-envelope me-2"></i>Send Email
                            </a>

                            <a href="tel:<?php echo htmlspecialchars($volunteer['phone']); ?>" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-phone me-2"></i>Call
                            </a>

                            <a href="list.php?delete=<?php echo $volunteer['id']; ?>"
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Are you sure you want to delete this application?')">
                                <i class="fas fa-trash me-2"></i>Delete Application
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include '../includes/footer.php'; ?>