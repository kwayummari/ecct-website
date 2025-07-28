<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Get campaign
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$campaign = $db->selectOne('campaigns', ['id' => $id]);

if (!$campaign) {
    header('Location: list.php?error=Campaign not found');
    exit;
}

if ($_POST) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $content = trim($_POST['content']);
    $goal_amount = (float)$_POST['goal_amount'];
    $raised_amount = (float)$_POST['raised_amount'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $location = trim($_POST['location']);
    $status = $_POST['status'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $featured_image = $campaign['featured_image'];

    // Handle image upload
    if (!empty($_FILES['featured_image']['name'])) {
        $upload_dir = ECCT_ROOT . '/assets/uploads/campaigns/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
            // Delete old image
            if ($featured_image && file_exists(ECCT_ROOT . '/' . $featured_image)) {
                unlink(ECCT_ROOT . '/' . $featured_image);
            }
            $featured_image = 'assets/uploads/campaigns/' . $filename;
        }
    }

    if (!empty($title) && !empty($description) && $goal_amount > 0) {
        $slug = create_slug($title);

        $data = [
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'content' => $content,
            'featured_image' => $featured_image,
            'goal_amount' => $goal_amount,
            'raised_amount' => $raised_amount,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'location' => $location,
            'status' => $status,
            'is_published' => $is_published,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($db->update('campaigns', $data, ['id' => $id])) {
            header('Location: list.php?success=Campaign updated successfully');
            exit;
        } else {
            $error = 'Error updating campaign';
        }
    } else {
        $error = 'Please fill in all required fields';
    }
}

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Campaign</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Campaign Title *</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($_POST['title'] ?? $campaign['title']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="Brief description of the campaign" required><?php echo htmlspecialchars($_POST['description'] ?? $campaign['description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Full Content</label>
                                    <textarea class="form-control" id="content" name="content" rows="8"><?php echo htmlspecialchars($_POST['content'] ?? $campaign['content']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location"
                                        value="<?php echo htmlspecialchars($_POST['location'] ?? $campaign['location']); ?>"
                                        placeholder="e.g., Dar es Salaam, Tanzania">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="goal_amount" class="form-label">Goal Amount ($) *</label>
                                    <input type="number" class="form-control" id="goal_amount" name="goal_amount"
                                        value="<?php echo htmlspecialchars($_POST['goal_amount'] ?? $campaign['goal_amount']); ?>"
                                        min="0" step="0.01" required>
                                </div>

                                <div class="mb-3">
                                    <label for="raised_amount" class="form-label">Raised Amount ($)</label>
                                    <input type="number" class="form-control" id="raised_amount" name="raised_amount"
                                        value="<?php echo htmlspecialchars($_POST['raised_amount'] ?? $campaign['raised_amount']); ?>"
                                        min="0" step="0.01">
                                </div>

                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date"
                                        value="<?php echo htmlspecialchars($_POST['start_date'] ?? $campaign['start_date']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date"
                                        value="<?php echo htmlspecialchars($_POST['end_date'] ?? $campaign['end_date']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <?php
                                        $current_status = $_POST['status'] ?? $campaign['status'];
                                        $statuses = ['planning', 'active', 'completed', 'cancelled'];
                                        foreach ($statuses as $status_option):
                                        ?>
                                            <option value="<?php echo $status_option; ?>" <?php echo ($current_status == $status_option) ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($status_option); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">Featured Image</label>
                                    <?php if ($campaign['featured_image']): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo SITE_URL . '/' . $campaign['featured_image']; ?>"
                                                alt="Current image" class="img-thumbnail" style="max-width: 200px;">
                                            <p class="small text-muted">Current image</p>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                                    <small class="form-text text-muted">Leave empty to keep current image</small>
                                </div>

                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_published" name="is_published"
                                        <?php echo (isset($_POST['is_published']) ? $_POST['is_published'] : $campaign['is_published']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_published">
                                        Published
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Campaign
                            </button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>