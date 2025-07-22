<?php

/**
 * Add New Campaign - ECCT Admin
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

$errors = [];
$success_message = '';
$form_data = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $errors[] = 'Invalid security token. Please try again.';
    } else {
        // Get form data
        $form_data = [
            'title' => sanitize_input($_POST['title'] ?? ''),
            'slug' => create_slug($_POST['title'] ?? ''),
            'description' => sanitize_input($_POST['description'] ?? ''),
            'content' => clean_html($_POST['content'] ?? ''),
            'location' => sanitize_input($_POST['location'] ?? ''),
            'status' => sanitize_input($_POST['status'] ?? 'planning'),
            'start_date' => sanitize_input($_POST['start_date'] ?? ''),
            'end_date' => sanitize_input($_POST['end_date'] ?? ''),
            'goal_amount' => (float)($_POST['goal_amount'] ?? 0),
            'raised_amount' => (float)($_POST['raised_amount'] ?? 0),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'created_by' => $_SESSION['admin_id']
        ];

        // Validation
        if (empty($form_data['title'])) {
            $errors[] = 'Campaign title is required.';
        } elseif (strlen($form_data['title']) > 200) {
            $errors[] = 'Campaign title cannot exceed 200 characters.';
        }

        if (empty($form_data['description'])) {
            $errors[] = 'Campaign description is required.';
        }

        if (empty($form_data['content'])) {
            $errors[] = 'Campaign content is required.';
        }

        if (!in_array($form_data['status'], ['planning', 'active', 'completed', 'cancelled'])) {
            $errors[] = 'Invalid campaign status.';
        }

        // Validate dates
        if ($form_data['start_date'] && $form_data['end_date']) {
            if (strtotime($form_data['start_date']) > strtotime($form_data['end_date'])) {
                $errors[] = 'End date must be after start date.';
            }
        }

        // Validate amounts
        if ($form_data['goal_amount'] < 0) {
            $errors[] = 'Goal amount cannot be negative.';
        }

        if ($form_data['raised_amount'] < 0) {
            $errors[] = 'Raised amount cannot be negative.';
        }

        if ($form_data['raised_amount'] > $form_data['goal_amount'] && $form_data['goal_amount'] > 0) {
            $errors[] = 'Raised amount cannot exceed goal amount.';
        }

        // Check for duplicate slug
        if ($db->exists('campaigns', ['slug' => $form_data['slug']])) {
            $form_data['slug'] = $form_data['slug'] . '-' . time();
        }

        // Handle image upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOADS_PATH . '/campaigns';
            $upload_result = upload_image($_FILES['featured_image'], $upload_dir);

            if ($upload_result['success']) {
                $form_data['featured_image'] = $upload_result['filename'];

                // Create thumbnails
                foreach (THUMB_SIZES as $size => $dimensions) {
                    $thumb_dir = $upload_dir . '/thumbs';
                    if (!is_dir($thumb_dir)) {
                        mkdir($thumb_dir, 0755, true);
                    }

                    $thumb_path = $thumb_dir . '/' . pathinfo($upload_result['filename'], PATHINFO_FILENAME) . '_' . $size . '.' . pathinfo($upload_result['filename'], PATHINFO_EXTENSION);
                    create_thumbnail($upload_result['path'], $thumb_path, $dimensions['width'], $dimensions['height']);
                }
            } else {
                $errors[] = 'Image upload failed: ' . $upload_result['message'];
            }
        }

        if (empty($errors)) {
            // Clean form data for database
            $clean_data = $form_data;

            // Convert empty strings to null for date fields
            if (empty($clean_data['start_date'])) $clean_data['start_date'] = null;
            if (empty($clean_data['end_date'])) $clean_data['end_date'] = null;
            if (empty($clean_data['location'])) $clean_data['location'] = null;

            // Insert campaign
            $campaign_id = $db->insert('campaigns', $clean_data);

            if ($campaign_id) {
                // Handle tags if any were submitted
                if (!empty($_POST['tags'])) {
                    $tag_names = array_map('trim', explode(',', $_POST['tags']));
                    foreach ($tag_names as $tag_name) {
                        if (!empty($tag_name)) {
                            $tag_slug = create_slug($tag_name);

                            // Check if tag exists
                            $tag = $db->selectOne('tags', ['slug' => $tag_slug]);
                            if (!$tag) {
                                // Create new tag
                                $tag_id = $db->insert('tags', [
                                    'name' => $tag_name,
                                    'slug' => $tag_slug,
                                    'color' => '#28a745'
                                ]);
                            } else {
                                $tag_id = $tag['id'];
                            }

                            // Link tag to campaign
                            if ($tag_id) {
                                $db->insert('campaign_tags', [
                                    'campaign_id' => $campaign_id,
                                    'tag_id' => $tag_id
                                ]);
                            }
                        }
                    }
                }

                log_activity('campaign_create', "New campaign created: {$form_data['title']}", 'campaigns', $campaign_id);
                set_flash('success', 'Campaign created successfully!');
                redirect('manage-campaigns.php');
            } else {
                $errors[] = 'Failed to create campaign. Please try again.';
            }
        }
    }
}

// Get available tags for autocomplete
$available_tags = $db->select('tags', [], ['order_by' => 'name ASC']);

$page_title = 'Add New Campaign - ECCT Admin';
include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Add New Campaign</h1>
                    <p class="text-muted">Create a new environmental conservation campaign</p>
                </div>
                <div>
                    <a href="manage-campaigns.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Campaigns
                    </a>
                </div>
            </div>

            <?php if ($errors): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <h6 class="alert-heading">Please correct the following errors:</h6>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>

                <div class="row">
                    <!-- Main Content -->
                    <div class="col-lg-8">
                        <!-- Basic Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Campaign Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Campaign Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>"
                                        required maxlength="200">
                                    <div class="form-text">Maximum 200 characters</div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Short Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        required maxlength="500"><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                                    <div class="form-text">Brief description for campaign cards and previews (max 500 chars)</div>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Campaign Content <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="content" name="content" rows="12"
                                        required><?php echo htmlspecialchars($form_data['content'] ?? ''); ?></textarea>
                                    <div class="form-text">Full campaign description and details</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="location" class="form-label">Location</label>
                                        <input type="text" class="form-control" id="location" name="location"
                                            value="<?php echo htmlspecialchars($form_data['location'] ?? ''); ?>"
                                            placeholder="e.g., Dar es Salaam, Tanzania">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="planning" <?php echo ($form_data['status'] ?? 'planning') === 'planning' ? 'selected' : ''; ?>>Planning</option>
                                            <option value="active" <?php echo ($form_data['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="completed" <?php echo ($form_data['status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo ($form_data['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date"
                                            value="<?php echo htmlspecialchars($form_data['start_date'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date"
                                            value="<?php echo htmlspecialchars($form_data['end_date'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Goals -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Financial Goals (Optional)</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="goal_amount" class="form-label">Goal Amount (USD)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="goal_amount" name="goal_amount"
                                                value="<?php echo htmlspecialchars($form_data['goal_amount'] ?? ''); ?>"
                                                min="0" step="0.01" placeholder="0.00">
                                        </div>
                                        <div class="form-text">Set to 0 if no financial goal</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="raised_amount" class="form-label">Amount Raised (USD)</label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="raised_amount" name="raised_amount"
                                                value="<?php echo htmlspecialchars($form_data['raised_amount'] ?? ''); ?>"
                                                min="0" step="0.01" placeholder="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Tags</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="tags" class="form-label">Campaign Tags</label>
                                    <input type="text" class="form-control" id="tags" name="tags"
                                        value="<?php echo htmlspecialchars($_POST['tags'] ?? ''); ?>"
                                        placeholder="Enter tags separated by commas">
                                    <div class="form-text">e.g., Beach Cleanup, Plastic Waste, Community</div>
                                </div>

                                <?php if ($available_tags): ?>
                                    <div class="available-tags">
                                        <small class="text-muted">Available tags:</small><br>
                                        <?php foreach ($available_tags as $tag): ?>
                                            <span class="badge rounded-pill me-1 mb-1 tag-suggestion"
                                                style="background-color: <?php echo $tag['color']; ?>; cursor: pointer;"
                                                onclick="addTag('<?php echo htmlspecialchars($tag['name']); ?>')">
                                                <?php echo htmlspecialchars($tag['name']); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Featured Image -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Featured Image</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="featured_image" class="form-label">Upload Image</label>
                                    <input type="file" class="form-control" id="featured_image" name="featured_image"
                                        accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text">
                                        Recommended size: 800x600px<br>
                                        Max file size: <?php echo format_file_size(MAX_FILE_SIZE); ?><br>
                                        Formats: JPG, PNG, GIF, WebP
                                    </div>
                                </div>

                                <div id="imagePreview" class="mt-3" style="display: none;">
                                    <img id="previewImg" src="" alt="Preview" class="img-fluid rounded">
                                </div>
                            </div>
                        </div>

                        <!-- Campaign Settings -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Campaign Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                        <?php echo isset($form_data['is_featured']) && $form_data['is_featured'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_featured">
                                        <strong>Featured Campaign</strong>
                                    </label>
                                    <div class="form-text">Featured campaigns appear prominently on the website</div>
                                </div>
                            </div>
                        </div>

                        <!-- Help -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-question-circle me-2"></i>Help
                                </h6>
                            </div>
                            <div class="card-body">
                                <small class="text-muted">
                                    <strong>Campaign Tips:</strong><br>
                                    • Use clear, compelling titles<br>
                                    • Include specific goals and timelines<br>
                                    • Add high-quality images<br>
                                    • Set realistic financial targets<br>
                                    • Use relevant tags for discoverability
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <a href="manage-campaigns.php" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-2"></i>Cancel
                                    </a>
                                    <div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Create Campaign
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Image preview
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        } else {
            preview.style.display = 'none';
        }
    }

    // Add tag to input
    function addTag(tagName) {
        const tagsInput = document.getElementById('tags');
        const currentTags = tagsInput.value.split(',').map(tag => tag.trim()).filter(tag => tag);

        if (!currentTags.includes(tagName)) {
            currentTags.push(tagName);
            tagsInput.value = currentTags.join(', ');
        }
    }

    // Form validation
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            const forms = document.getElementsByClassName('needs-validation');
            Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();

    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        // This is optional - you might want to show the slug that will be generated
        console.log('Slug will be:', this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-'));
    });

    // Validate financial amounts
    document.getElementById('raised_amount').addEventListener('input', function() {
        const goalAmount = parseFloat(document.getElementById('goal_amount').value) || 0;
        const raisedAmount = parseFloat(this.value) || 0;

        if (goalAmount > 0 && raisedAmount > goalAmount) {
            this.setCustomValidity('Raised amount cannot exceed goal amount');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('goal_amount').addEventListener('input', function() {
        const raisedInput = document.getElementById('raised_amount');
        const goalAmount = parseFloat(this.value) || 0;
        const raisedAmount = parseFloat(raisedInput.value) || 0;

        if (goalAmount > 0 && raisedAmount > goalAmount) {
            raisedInput.setCustomValidity('Raised amount cannot exceed goal amount');
        } else {
            raisedInput.setCustomValidity('');
        }
    });

    // Date validation
    document.getElementById('end_date').addEventListener('change', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = this.value;

        if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
            this.setCustomValidity('End date must be after start date');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('start_date').addEventListener('change', function() {
        const endDate = document.getElementById('end_date');
        const startDate = this.value;
        const endDateValue = endDate.value;

        if (startDate && endDateValue && new Date(endDateValue) < new Date(startDate)) {
            endDate.setCustomValidity('End date must be after start date');
        } else {
            endDate.setCustomValidity('');
        }
    });
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>