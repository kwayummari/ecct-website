<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

$success = '';
$error = '';
$form_data = [
    'title' => '',
    'slug' => '',
    'description' => '',
    'excerpt' => '',
    'category' => '',
    'duration' => '',
    'location' => '',
    'status' => 'active',
    'objectives' => '',
    'activities' => '',
    'impact' => '',
    'target_audience' => '',
    'budget' => '',
    'start_date' => '',
    'end_date' => '',
    'coordinator' => '',
    'partner_organizations' => '',
    'is_active' => 1,
    'is_featured' => 0
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validate_csrf()) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $form_data = [
            'title' => trim($_POST['title'] ?? ''),
            'slug' => trim($_POST['slug'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'excerpt' => trim($_POST['excerpt'] ?? ''),
            'category' => trim($_POST['category'] ?? ''),
            'duration' => trim($_POST['duration'] ?? ''),
            'location' => trim($_POST['location'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            'objectives' => trim($_POST['objectives'] ?? ''),
            'activities' => trim($_POST['activities'] ?? ''),
            'impact' => trim($_POST['impact'] ?? ''),
            'target_audience' => trim($_POST['target_audience'] ?? ''),
            'budget' => !empty($_POST['budget']) ? (float)$_POST['budget'] : null,
            'start_date' => !empty($_POST['start_date']) ? $_POST['start_date'] : null,
            'end_date' => !empty($_POST['end_date']) ? $_POST['end_date'] : null,
            'coordinator' => trim($_POST['coordinator'] ?? ''),
            'partner_organizations' => trim($_POST['partner_organizations'] ?? ''),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0
        ];

        // Generate slug if empty
        if (empty($form_data['slug']) && !empty($form_data['title'])) {
            $form_data['slug'] = generate_slug($form_data['title']);
        }

        // Validation
        $validation_errors = [];
        
        if (empty($form_data['title'])) {
            $validation_errors[] = 'Title is required';
        }
        
        if (empty($form_data['description'])) {
            $validation_errors[] = 'Description is required';
        }

        // Check if slug already exists
        if (!empty($form_data['slug'])) {
            $existing = $db->selectOne('programs', ['slug' => $form_data['slug']]);
            if ($existing) {
                $validation_errors[] = 'URL slug already exists';
            }
        }

        // Handle image upload
        $image_path = '';
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload_result = handle_image_upload($_FILES['featured_image'], 'programs');
            if ($upload_result['success']) {
                $image_path = $upload_result['path'];
            } else {
                $validation_errors[] = $upload_result['error'];
            }
        }

        if (empty($validation_errors)) {
            // Add image path if uploaded
            if ($image_path) {
                $form_data['featured_image'] = $image_path;
            }

            $form_data['created_at'] = date('Y-m-d H:i:s');
            $form_data['updated_at'] = date('Y-m-d H:i:s');

            if ($db->insert('programs', $form_data)) {
                $success = 'Program created successfully!';
                // Reset form
                $form_data = [
                    'title' => '',
                    'slug' => '',
                    'description' => '',
                    'excerpt' => '',
                    'category' => '',
                    'duration' => '',
                    'location' => '',
                    'status' => 'active',
                    'objectives' => '',
                    'activities' => '',
                    'impact' => '',
                    'target_audience' => '',
                    'budget' => '',
                    'start_date' => '',
                    'end_date' => '',
                    'coordinator' => '',
                    'partner_organizations' => '',
                    'is_active' => 1,
                    'is_featured' => 0
                ];
            } else {
                $error = 'Error creating program. Please try again.';
            }
        } else {
            $error = 'Please correct the following errors: ' . implode(', ', $validation_errors);
        }
    }
}

$page_title = "Add New Program";
require_once ECCT_ROOT . '/admin/includes/header.php';
?>

<div class="d-flex">
    <?php include ECCT_ROOT . '/admin/includes/sidebar.php'; ?>
    
    <main class="flex-grow-1 p-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Add New Program</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a href="list.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Programs
                    </a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Title *</label>
                                            <input type="text" class="form-control" id="title" name="title" 
                                                   value="<?php echo htmlspecialchars($form_data['title']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="slug" class="form-label">URL Slug</label>
                                            <input type="text" class="form-control" id="slug" name="slug" 
                                                   value="<?php echo htmlspecialchars($form_data['slug']); ?>">
                                            <div class="form-text">Leave empty to auto-generate from title</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="excerpt" class="form-label">Excerpt</label>
                                    <textarea class="form-control" id="excerpt" name="excerpt" rows="3"><?php echo htmlspecialchars($form_data['excerpt']); ?></textarea>
                                    <div class="form-text">Brief summary for program cards and previews</div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control" id="description" name="description" rows="8" required><?php echo htmlspecialchars($form_data['description']); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category" class="form-label">Category</label>
                                            <input type="text" class="form-control" id="category" name="category" 
                                                   value="<?php echo htmlspecialchars($form_data['category']); ?>"
                                                   placeholder="e.g., Environmental Education, Conservation">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo $form_data['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="upcoming" <?php echo $form_data['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                                <option value="completed" <?php echo $form_data['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="paused" <?php echo $form_data['status'] === 'paused' ? 'selected' : ''; ?>>Paused</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="duration" class="form-label">Duration</label>
                                            <input type="text" class="form-control" id="duration" name="duration" 
                                                   value="<?php echo htmlspecialchars($form_data['duration']); ?>"
                                                   placeholder="e.g., 6 months, Ongoing">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="location" class="form-label">Location</label>
                                            <input type="text" class="form-control" id="location" name="location" 
                                                   value="<?php echo htmlspecialchars($form_data['location']); ?>"
                                                   placeholder="e.g., Dar es Salaam, Tanzania">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="start_date" class="form-label">Start Date</label>
                                            <input type="date" class="form-control" id="start_date" name="start_date" 
                                                   value="<?php echo $form_data['start_date']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="end_date" class="form-label">End Date</label>
                                            <input type="date" class="form-control" id="end_date" name="end_date" 
                                                   value="<?php echo $form_data['end_date']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="objectives" class="form-label">Objectives</label>
                                    <textarea class="form-control" id="objectives" name="objectives" rows="4"><?php echo htmlspecialchars($form_data['objectives']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="activities" class="form-label">Activities</label>
                                    <textarea class="form-control" id="activities" name="activities" rows="4"><?php echo htmlspecialchars($form_data['activities']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="impact" class="form-label">Expected Impact</label>
                                    <textarea class="form-control" id="impact" name="impact" rows="4"><?php echo htmlspecialchars($form_data['impact']); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="target_audience" class="form-label">Target Audience</label>
                                            <input type="text" class="form-control" id="target_audience" name="target_audience" 
                                                   value="<?php echo htmlspecialchars($form_data['target_audience']); ?>"
                                                   placeholder="e.g., Students, Local Communities">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="budget" class="form-label">Budget (USD)</label>
                                            <input type="number" class="form-control" id="budget" name="budget" 
                                                   value="<?php echo $form_data['budget']; ?>" 
                                                   step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="coordinator" class="form-label">Program Coordinator</label>
                                            <input type="text" class="form-control" id="coordinator" name="coordinator" 
                                                   value="<?php echo htmlspecialchars($form_data['coordinator']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="partner_organizations" class="form-label">Partner Organizations</label>
                                            <input type="text" class="form-control" id="partner_organizations" name="partner_organizations" 
                                                   value="<?php echo htmlspecialchars($form_data['partner_organizations']); ?>"
                                                   placeholder="Separated by commas">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                               <?php echo $form_data['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Active (visible on website)
                                        </label>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                               <?php echo $form_data['is_featured'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            Featured program
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <a href="list.php" class="btn btn-secondary me-md-2">Cancel</a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Create Program
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Featured Image</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="featured_image" class="form-label">Upload Image</label>
                                <input type="file" class="form-control" id="featured_image" name="featured_image" 
                                       accept="image/*">
                                <div class="form-text">
                                    Recommended size: 800x600px<br>
                                    Max file size: 5MB<br>
                                    Formats: JPG, PNG, WebP
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Help</h5>
                        </div>
                        <div class="card-body">
                            <small class="text-muted">
                                <strong>Required fields:</strong> Title and Description<br><br>
                                <strong>Slug:</strong> URL-friendly version of the title. Leave empty to auto-generate.<br><br>
                                <strong>Status:</strong> Controls the current state of the program.<br><br>
                                <strong>Featured:</strong> Featured programs appear prominently on the website.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
// Auto-generate slug from title
document.getElementById('title').addEventListener('input', function() {
    const slug = document.getElementById('slug');
    if (!slug.value || slug.dataset.manual !== 'true') {
        slug.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.manual = 'true';
});
</script>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>