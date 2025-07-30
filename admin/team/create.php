<?php
require_once '../../includes/config.php';
require_once '../../includes/database.php';
require_once '../../includes/functions.php';
require_once '../includes/auth_check.php';

$db = new Database();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    $name = trim($_POST['name']);
    $position = trim($_POST['position']);
    $bio = trim($_POST['bio']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $linkedin_url = trim($_POST['linkedin_url']);
    $twitter_url = trim($_POST['twitter_url']);
    $facebook_url = trim($_POST['facebook_url']);
    $department = $_POST['department'];
    $sort_order = (int)$_POST['sort_order'];
    $is_leadership = isset($_POST['is_leadership']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $errors = [];

    // Validate required fields
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($position)) {
        $errors[] = 'Position is required';
    }

    if (empty($department)) {
        $errors[] = 'Department is required';
    }

    // Validate email if provided
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address';
    }

    // Validate URLs if provided
    if (!empty($linkedin_url) && !filter_var($linkedin_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid LinkedIn URL';
    }

    if (!empty($twitter_url) && !filter_var($twitter_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid Twitter URL';
    }

    if (!empty($facebook_url) && !filter_var($facebook_url, FILTER_VALIDATE_URL)) {
        $errors[] = 'Please enter a valid Facebook URL';
    }

    $image_path = '';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../assets/images/team/';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_info = pathinfo($_FILES['image']['name']);
        $extension = strtolower($file_info['extension']);

        // Validate file type
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($extension, $allowed_types)) {
            $errors[] = 'Image must be a valid image file (JPG, PNG, GIF)';
        }

        // Validate file size (5MB max)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Image file size must be less than 5MB';
        }

        if (empty($errors)) {
            // Generate unique filename
            $filename = 'team_' . time() . '_' . uniqid() . '.' . $extension;
            $image_path = 'assets/images/team/' . $filename;
            $full_path = $upload_dir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $full_path)) {
                $errors[] = 'Failed to upload image file';
            }
        }
    }

    if (empty($errors)) {
        $data = [
            'name' => $name,
            'position' => $position,
            'bio' => $bio,
            'image_path' => $image_path,
            'email' => $email,
            'phone' => $phone,
            'linkedin_url' => $linkedin_url,
            'twitter_url' => $twitter_url,
            'facebook_url' => $facebook_url,
            'department' => $department,
            'sort_order' => $sort_order,
            'is_leadership' => $is_leadership,
            'is_active' => $is_active
        ];

        if ($db->insert('team_members', $data)) {
            $_SESSION['message'] = 'Team member created successfully';
            $_SESSION['message_type'] = 'success';
            header('Location: list.php');
            exit;
        } else {
            $errors[] = 'Failed to create team member';
        }
    }
}

$page_title = "Add New Team Member";
include '../includes/header.php';
?>

<div class="container-fluid px-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Add New Team Member</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Team
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
                    <h5 class="card-title mb-0">Team Member Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="create">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="position" class="form-label">Position/Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="position" name="position"
                                        value="<?php echo htmlspecialchars($_POST['position'] ?? ''); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Department <span class="text-danger">*</span></label>
                                    <select class="form-select" id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="management" <?php echo ($_POST['department'] ?? '') === 'management' ? 'selected' : ''; ?>>Management</option>
                                        <option value="technical" <?php echo ($_POST['department'] ?? '') === 'technical' ? 'selected' : ''; ?>>Technical</option>
                                        <option value="finance" <?php echo ($_POST['department'] ?? '') === 'finance' ? 'selected' : ''; ?>>Finance</option>
                                        <option value="operations" <?php echo ($_POST['department'] ?? '') === 'operations' ? 'selected' : ''; ?>>Operations</option>
                                        <option value="communications" <?php echo ($_POST['department'] ?? '') === 'communications' ? 'selected' : ''; ?>>Communications</option>
                                        <option value="field" <?php echo ($_POST['department'] ?? '') === 'field' ? 'selected' : ''; ?>>Field</option>
                                        <option value="other" <?php echo ($_POST['department'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                        value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>"
                                        min="0" step="1">
                                    <div class="form-text">Lower numbers appear first</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Biography</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4"
                                placeholder="Brief biography and background..."><?php echo htmlspecialchars($_POST['bio'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Profile Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 5MB. Recommended: Square aspect ratio</div>
                        </div>

                        <h6 class="mb-3">Contact Information</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                        placeholder="john@example.com">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                                        placeholder="+255 123 456 789">
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3">Social Media Links</h6>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="linkedin_url" class="form-label">LinkedIn URL</label>
                                    <input type="url" class="form-control" id="linkedin_url" name="linkedin_url"
                                        value="<?php echo htmlspecialchars($_POST['linkedin_url'] ?? ''); ?>"
                                        placeholder="https://linkedin.com/in/username">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter URL</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url"
                                        value="<?php echo htmlspecialchars($_POST['twitter_url'] ?? ''); ?>"
                                        placeholder="https://twitter.com/username">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                        value="<?php echo htmlspecialchars($_POST['facebook_url'] ?? ''); ?>"
                                        placeholder="https://facebook.com/username">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_leadership" name="is_leadership"
                                        <?php echo isset($_POST['is_leadership']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_leadership">
                                        Leadership Team Member
                                    </label>
                                    <div class="form-text">Leadership members appear prominently on the About page</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                        <?php echo !isset($_POST['is_active']) || $_POST['is_active'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active
                                    </label>
                                    <div class="form-text">Only active members are displayed publicly</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Create Team Member
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
                    <h6 class="card-title mb-0">Department Descriptions</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Management:</strong> Executive leadership and strategic oversight
                    </div>
                    <div class="mb-3">
                        <strong>Technical:</strong> Research, technical expertise, and innovation
                    </div>
                    <div class="mb-3">
                        <strong>Finance:</strong> Financial management and donor relations
                    </div>
                    <div class="mb-3">
                        <strong>Operations:</strong> Project coordination and implementation
                    </div>
                    <div class="mb-3">
                        <strong>Communications:</strong> Marketing, outreach, and public relations
                    </div>
                    <div class="mb-3">
                        <strong>Field:</strong> On-ground conservation and community work
                    </div>
                    <div class="mb-0">
                        <strong>Other:</strong> Specialized roles not fitting other categories
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Image Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li>• Use professional headshots</li>
                        <li>• Square aspect ratio (1:1) preferred</li>
                        <li>• High resolution (minimum 400x400px)</li>
                        <li>• Good lighting and clear background</li>
                        <li>• File formats: JPG, PNG, GIF</li>
                        <li>• Maximum file size: 5MB</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>