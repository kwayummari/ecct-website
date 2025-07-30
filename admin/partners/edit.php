<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

// Get partner ID
$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: list.php');
    exit;
}

// Get partner data
$partner = $db->selectOne('partners', ['id' => $id]);
if (!$partner) {
    $_SESSION['message'] = 'Partner not found';
    $_SESSION['message_type'] = 'error';
    header('Location: list.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $website_url = trim($_POST['website_url']);
    $partnership_type = $_POST['partnership_type'];
    $sort_order = (int)$_POST['sort_order'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];

    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Partner name is required';
    }

    if (empty($partnership_type)) {
        $errors[] = 'Partnership type is required';
    }

    // Validate website URL if provided
    if (!empty($website_url) && !filter_var($website_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid website URL';
    }

    $logo_path = $partner['logo_path']; // Keep existing logo by default

    // Handle logo upload
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/partners/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_info = pathinfo($_FILES['logo']['name']);
        $extension = strtolower($file_info['extension']);

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'svg'];
        if (!in_array($extension, $allowed_types)) {
            $errors[] = 'Logo must be a valid image file (JPG, PNG, GIF, SVG)';
        }

        // Validate file size (2MB max)
        if ($_FILES['logo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Logo file size must be less than 2MB';
        }

        if (empty($errors)) {
            // Delete old logo if exists
            if (!empty($partner['logo_path']) && file_exists('../../' . $partner['logo_path'])) {
                unlink('../../' . $partner['logo_path']);
            }

            // Generate unique filename
            $filename = 'partner_' . time() . '_' . uniqid() . '.' . $extension;
            $logo_path = 'assets/images/partners/' . $filename;
            $full_path = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $full_path)) {
                $errors[] = 'Failed to upload logo file';
                $logo_path = $partner['logo_path']; // Revert to old logo
            }
        }
    }

    // Handle logo removal
    if (isset($_POST['remove_logo']) && $_POST['remove_logo'] === '1') {
        if (!empty($partner['logo_path']) && file_exists('../../' . $partner['logo_path'])) {
            unlink('../../' . $partner['logo_path']);
        }
        $logo_path = '';
    }

    if (empty($errors)) {
        $data = [
            'name' => $name,
            'description' => $description,
            'logo_path' => $logo_path,
            'website_url' => $website_url,
            'partnership_type' => $partnership_type,
            'sort_order' => $sort_order,
            'is_featured' => $is_featured,
            'is_active' => $is_active
        ];

        if ($db->update('partners', $data, ['id' => $id])) {
            $_SESSION['message'] = 'Partner updated successfully';
            $_SESSION['message_type'] = 'success';
            header('Location: list.php');
            exit;
        } else {
            $errors[] = 'Failed to update partner';
        }
    }

    // If there were errors, update the partner array with POST data for form repopulation
    if (!empty($errors)) {
        $partner = array_merge($partner, $_POST);
    }
}

$page_title = "Edit Partner - " . $partner['name'];
include ECCT_ROOT . '/admin/includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Edit Partner</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Partners
            </a>
        </div>
    </div>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h6>Please fix the following errors:</h6>
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Partner Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Partner Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($partner['name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="partnership_type" class="form-label">Partnership Type <span class="text-danger">*</span></label>
                                    <select class="form-select" id="partnership_type" name="partnership_type" required>
                                        <option value="">Select Type</option>
                                        <option value="sponsor" <?php echo $partner['partnership_type'] === 'sponsor' ? 'selected' : ''; ?>>Sponsor</option>
                                        <option value="implementation" <?php echo $partner['partnership_type'] === 'implementation' ? 'selected' : ''; ?>>Implementation</option>
                                        <option value="technical" <?php echo $partner['partnership_type'] === 'technical' ? 'selected' : ''; ?>>Technical</option>
                                        <option value="funding" <?php echo $partner['partnership_type'] === 'funding' ? 'selected' : ''; ?>>Funding</option>
                                        <option value="other" <?php echo $partner['partnership_type'] === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4"
                                placeholder="Brief description of the partner and partnership..."><?php echo htmlspecialchars($partner['description']); ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="website_url" class="form-label">Website URL</label>
                                    <input type="url" class="form-control" id="website_url" name="website_url"
                                        value="<?php echo htmlspecialchars($partner['website_url']); ?>"
                                        placeholder="https://example.com">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                        value="<?php echo htmlspecialchars($partner['sort_order']); ?>"
                                        min="0" step="1">
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="logo" class="form-label">Partner Logo</label>

                            <?php if (!empty($partner['logo_path']) && file_exists('../../' . $partner['logo_path'])): ?>
                                <div class="current-logo mb-2">
                                    <p class="mb-2"><strong>Current Logo:</strong></p>
                                    <img src="../../<?php echo htmlspecialchars($partner['logo_path']); ?>"
                                        alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                        class="img-thumbnail" style="max-height: 100px;">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" id="remove_logo" name="remove_logo" value="1">
                                        <label class="form-check-label" for="remove_logo">
                                            Remove current logo
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF, SVG. Max size: 2MB. Leave empty to keep current logo.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                        <?php echo $partner['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">
                                        Featured Partner
                                    </label>
                                    <div class="form-text">Featured partners appear on the homepage</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        <?php echo $partner['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                    <div class="form-text">Only active partners are displayed publicly</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Update Partner
                            </button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Partner Details</h6>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7"><?php echo date('M j, Y', strtotime($partner['created_at'])); ?></dd>

                        <dt class="col-sm-5">Last Updated:</dt>
                        <dd class="col-sm-7"><?php echo date('M j, Y', strtotime($partner['updated_at'])); ?></dd>

                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            <span class="badge <?php echo $partner['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                <?php echo $partner['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </dd>

                        <dt class="col-sm-5">Featured:</dt>
                        <dd class="col-sm-7">
                            <span class="badge <?php echo $partner['is_featured'] ? 'bg-warning' : 'bg-light text-dark'; ?>">
                                <?php echo $partner['is_featured'] ? 'Yes' : 'No'; ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Partnership Types</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Sponsor:</strong> Organizations providing financial or material support
                    </div>
                    <div class="mb-3">
                        <strong>Implementation:</strong> Partners actively involved in project execution
                    </div>
                    <div class="mb-3">
                        <strong>Technical:</strong> Organizations providing technical expertise or research
                    </div>
                    <div class="mb-3">
                        <strong>Funding:</strong> Grant providers and funding organizations
                    </div>
                    <div class="mb-0">
                        <strong>Other:</strong> Strategic partnerships not fitting other categories
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ECCT_ROOT . '/admin/includes/footer.php'; ?>