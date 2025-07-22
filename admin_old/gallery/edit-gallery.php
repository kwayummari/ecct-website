<?php

/**
 * Edit Gallery Item - Admin Panel
 * ECCT Website Gallery Edit
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_permission('manage_gallery');

// Get database instance
$db = new Database();

// Get image ID
$image_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$image_id) {
    set_flash('error', 'Invalid image ID.');
    redirect(SITE_URL . '/admin/gallery/manage-gallery.php');
}

// Get image data
$image = $db->selectOne('gallery', ['id' => $image_id]);

if (!$image) {
    set_flash('error', 'Image not found.');
    redirect(SITE_URL . '/admin/gallery/manage-gallery.php');
}

// Page variables
$page_title = 'Edit Image: ' . $image['title'] . ' - ECCT Admin';
$current_user = get_current_user();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token.';
    } else {
        // Validation rules
        $validation_rules = [
            'title' => ['required' => true, 'min_length' => 2, 'max_length' => 200, 'label' => 'Title'],
            'alt_text' => ['max_length' => 200, 'label' => 'Alt Text'],
            'category' => ['max_length' => 100, 'label' => 'Category'],
            'description' => ['max_length' => 1000, 'label' => 'Description']
        ];

        $form_data = [
            'title' => sanitize_input($_POST['title'] ?? ''),
            'description' => sanitize_input($_POST['description'] ?? ''),
            'alt_text' => sanitize_input($_POST['alt_text'] ?? ''),
            'category' => sanitize_input($_POST['category'] ?? ''),
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0)
        ];

        $validation_errors = validate_form($form_data, $validation_rules);

        // Handle image replacement
        $new_image_uploaded = false;
        if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOADS_PATH . '/gallery';
            $upload_result = upload_image($_FILES['new_image'], $upload_dir);

            if ($upload_result['success']) {
                // Delete old image
                delete_image(UPLOADS_PATH . '/gallery/' . $image['image_path']);

                // Update image path
                $form_data['image_path'] = $upload_result['filename'];
                $new_image_uploaded = true;

                // Create thumbnails for new image
                foreach (THUMB_SIZES as $size => $dimensions) {
                    $thumb_dir = $upload_dir . '/thumbs';
                    if (!is_dir($thumb_dir)) {
                        mkdir($thumb_dir, 0755, true);
                    }

                    $thumb_path = $thumb_dir . '/' . pathinfo($upload_result['filename'], PATHINFO_FILENAME) .
                        '_' . $size . '.' . pathinfo($upload_result['filename'], PATHINFO_EXTENSION);

                    create_thumbnail(
                        $upload_result['path'],
                        $thumb_path,
                        $dimensions['width'],
                        $dimensions['height']
                    );
                }
            } else {
                $validation_errors['new_image'] = $upload_result['message'];
            }
        }

        if (empty($validation_errors)) {
            // Update database
            $updated = $db->update('gallery', $form_data, ['id' => $image_id]);

            if ($updated !== false) {
                $success_message = 'Image updated successfully.';

                // Log activity
                $activity_desc = "Gallery image updated: {$form_data['title']}";
                if ($new_image_uploaded) {
                    $activity_desc .= " (image file replaced)";
                }
                log_activity('gallery_update', $activity_desc);

                // Update local image data
                $image = array_merge($image, $form_data);

                // Clear any cached data
                cache_clear('gallery_featured');
                cache_clear('gallery_recent');
            } else {
                $error_message = 'Failed to update image.';
            }
        } else {
            $error_message = 'Please correct the following errors: ' . implode(', ', $validation_errors);
        }
    }
}

// Get existing categories for dropdown
$categories = $db->raw(
    "SELECT DISTINCT category, COUNT(*) as count 
     FROM gallery 
     WHERE category IS NOT NULL AND category != '' 
     GROUP BY category 
     ORDER BY category ASC"
);
$existing_categories = $categories ? $categories->fetchAll() : [];

// Get image file information
$image_path = UPLOADS_PATH . '/gallery/' . $image['image_path'];
$image_info = [];
if (file_exists($image_path)) {
    $image_info = [
        'size' => filesize($image_path),
        'dimensions' => getimagesize($image_path),
        'type' => mime_content_type($image_path),
        'created' => filectime($image_path),
        'modified' => filemtime($image_path)
    ];
}

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit Image</h1>
                    <p class="text-muted">Modify image details and settings</p>
                </div>
                <div class="page-actions">
                    <a href="manage-gallery.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Gallery
                    </a>
                    <a href="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                        target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-2"></i>View Full Size
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Image Preview -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">
                        <i class="fas fa-image me-2"></i>Current Image
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="image-preview-container">
                        <img src="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                            alt="<?php echo htmlspecialchars($image['title']); ?>"
                            class="img-fluid w-100"
                            id="currentImage"
                            style="max-height: 400px; object-fit: contain;">
                    </div>

                    <?php if ($image['is_featured']): ?>
                        <div class="position-absolute top-0 end-0 m-3">
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-star me-1"></i>Featured
                            </span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Image Information -->
                <div class="card-footer bg-light">
                    <div class="row text-center">
                        <div class="col-6">
                            <small class="text-muted d-block">File Size</small>
                            <strong><?php echo isset($image_info['size']) ? format_file_size($image_info['size']) : 'Unknown'; ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Dimensions</small>
                            <strong>
                                <?php
                                if (isset($image_info['dimensions'])) {
                                    echo $image_info['dimensions'][0] . ' × ' . $image_info['dimensions'][1];
                                } else {
                                    echo 'Unknown';
                                }
                                ?>
                            </strong>
                        </div>
                    </div>
                    <div class="row text-center mt-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Uploaded</small>
                            <strong><?php echo format_date($image['created_at']); ?></strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">File Type</small>
                            <strong><?php echo isset($image_info['type']) ? strtoupper(explode('/', $image_info['type'])[1]) : 'Unknown'; ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="col-lg-7">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="mb-0">
                        <i class="fas fa-edit me-2"></i>Image Details
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>

                        <!-- Basic Information -->
                        <div class="form-section mb-4">
                            <h6 class="section-heading mb-3">Basic Information</h6>

                            <div class="mb-3">
                                <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                <input type="text"
                                    class="form-control"
                                    id="title"
                                    name="title"
                                    value="<?php echo htmlspecialchars($image['title']); ?>"
                                    required>
                                <div class="invalid-feedback">
                                    Please provide a title for the image.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control"
                                    id="description"
                                    name="description"
                                    rows="3"
                                    placeholder="Optional description of the image"><?php echo htmlspecialchars($image['description']); ?></textarea>
                                <div class="form-text">
                                    Provide a detailed description to help visitors understand the context of this image.
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="alt_text" class="form-label">Alt Text</label>
                                    <input type="text"
                                        class="form-control"
                                        id="alt_text"
                                        name="alt_text"
                                        value="<?php echo htmlspecialchars($image['alt_text']); ?>"
                                        placeholder="Accessibility description">
                                    <div class="form-text">
                                        Describe the image for screen readers and accessibility.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control"
                                            id="category"
                                            name="category"
                                            value="<?php echo htmlspecialchars($image['category']); ?>"
                                            placeholder="Image category"
                                            list="categoryList">
                                        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                            <i class="fas fa-list"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <h6 class="dropdown-header">Existing Categories</h6>
                                            </li>
                                            <?php foreach ($existing_categories as $cat): ?>
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        onclick="document.getElementById('category').value = '<?php echo htmlspecialchars($cat['category']); ?>'">
                                                        <?php echo htmlspecialchars(ucwords($cat['category'])); ?>
                                                        <small class="text-muted">(<?php echo $cat['count']; ?>)</small>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <datalist id="categoryList">
                                        <?php foreach ($existing_categories as $cat): ?>
                                            <option value="<?php echo htmlspecialchars($cat['category']); ?>">
                                            <?php endforeach; ?>
                                    </datalist>
                                </div>
                            </div>
                        </div>

                        <!-- Display Settings -->
                        <div class="form-section mb-4">
                            <h6 class="section-heading mb-3">Display Settings</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input"
                                            type="checkbox"
                                            id="is_featured"
                                            name="is_featured"
                                            <?php echo $image['is_featured'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">
                                            <strong>Featured Image</strong>
                                        </label>
                                        <div class="form-text">
                                            Featured images are highlighted in the gallery and may appear on the homepage.
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number"
                                        class="form-control"
                                        id="sort_order"
                                        name="sort_order"
                                        value="<?php echo $image['sort_order']; ?>"
                                        min="0"
                                        placeholder="0">
                                    <div class="form-text">
                                        Lower numbers appear first. Leave 0 for default ordering.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Replace Image -->
                        <div class="form-section mb-4">
                            <h6 class="section-heading mb-3">Replace Image</h6>

                            <div class="mb-3">
                                <label for="new_image" class="form-label">Upload New Image</label>
                                <input type="file"
                                    class="form-control"
                                    id="new_image"
                                    name="new_image"
                                    accept="image/*">
                                <div class="form-text">
                                    <i class="fas fa-info-circle text-info me-1"></i>
                                    Upload a new image to replace the current one. The old image will be permanently deleted.
                                    <br>Supported formats: JPG, PNG, GIF, WebP | Max size: <?php echo format_file_size(MAX_FILE_SIZE); ?>
                                </div>
                            </div>

                            <div id="newImagePreview" class="d-none">
                                <label class="form-label">New Image Preview</label>
                                <div class="border rounded p-3 bg-light">
                                    <img id="previewImage" src="" alt="New image preview" class="img-fluid" style="max-height: 200px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removePreview">
                                        <i class="fas fa-times me-1"></i>Remove
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                            <div>
                                <a href="manage-gallery.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Image
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-bolt text-warning me-2"></i>
                        Quick Actions
                    </h6>
                    <div class="btn-group me-3" role="group">
                        <a href="?action=toggle_featured&id=<?php echo $image['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                            class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-star me-1"></i>
                            <?php echo $image['is_featured'] ? 'Remove from Featured' : 'Mark as Featured'; ?>
                        </a>
                        <a href="upload-images.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-upload me-1"></i>Upload More Images
                        </a>
                    </div>
                    <div class="btn-group" role="group">
                        <a href="?action=delete&id=<?php echo $image['id']; ?>&token=<?php echo get_csrf_token(); ?>"
                            class="btn btn-outline-danger btn-sm"
                            onclick="return confirm('Are you sure you want to delete this image? This action cannot be undone.')">
                            <i class="fas fa-trash me-1"></i>Delete Image
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const newImageInput = document.getElementById('new_image');
        const newImagePreview = document.getElementById('newImagePreview');
        const previewImage = document.getElementById('previewImage');
        const removePreview = document.getElementById('removePreview');
        const currentImage = document.getElementById('currentImage');

        // Handle new image selection
        newImageInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert('Please select a valid image file.');
                    this.value = '';
                    return;
                }

                // Validate file size
                if (file.size > <?php echo MAX_FILE_SIZE; ?>) {
                    alert('File size too large. Maximum size is <?php echo format_file_size(MAX_FILE_SIZE); ?>.');
                    this.value = '';
                    return;
                }

                // Show preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                    newImagePreview.classList.remove('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                newImagePreview.classList.add('d-none');
            }
        });

        // Remove preview
        removePreview.addEventListener('click', function() {
            newImageInput.value = '';
            newImagePreview.classList.add('d-none');
            previewImage.src = '';
        });

        // Form validation
        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Auto-generate alt text from title
        const titleInput = document.getElementById('title');
        const altTextInput = document.getElementById('alt_text');

        titleInput.addEventListener('blur', function() {
            if (!altTextInput.value.trim() && this.value.trim()) {
                altTextInput.value = this.value.trim();
            }
        });

        // Character count for description
        const descriptionTextarea = document.getElementById('description');
        const maxLength = 1000;

        function updateCharCount() {
            const remaining = maxLength - descriptionTextarea.value.length;
            let countElement = document.getElementById('char-count');

            if (!countElement) {
                countElement = document.createElement('div');
                countElement.id = 'char-count';
                countElement.className = 'form-text text-end';
                descriptionTextarea.parentNode.appendChild(countElement);
            }

            countElement.textContent = `${descriptionTextarea.value.length}/${maxLength} characters`;
            countElement.className = remaining < 100 ? 'form-text text-end text-warning' : 'form-text text-end text-muted';
        }

        descriptionTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initialize
    });
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>