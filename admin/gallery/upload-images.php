<?php

/**
 * Upload Images - Admin Panel
 * ECCT Website Gallery Upload
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

// Page variables
$page_title = 'Upload Images - ECCT Admin';
$current_user = get_current_user();

$success_message = '';
$error_message = '';
$uploaded_images = [];

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token.';
    } else {
        $upload_dir = UPLOADS_PATH . '/gallery';

        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $uploaded_count = 0;
        $failed_count = 0;
        $errors = [];

        // Handle multiple file upload
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            $file_count = count($_FILES['images']['name']);

            for ($i = 0; $i < $file_count; $i++) {
                // Skip empty files
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                // Prepare file array for upload_image function
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];

                // Upload image
                $upload_result = upload_image($file, $upload_dir);

                if ($upload_result['success']) {
                    // Get form data for this image
                    $titles = $_POST['titles'] ?? [];
                    $descriptions = $_POST['descriptions'] ?? [];
                    $alt_texts = $_POST['alt_texts'] ?? [];
                    $categories = $_POST['categories'] ?? [];
                    $featured = $_POST['featured'] ?? [];

                    // Prepare database data
                    $image_data = [
                        'title' => sanitize_input($titles[$i] ?? pathinfo($file['name'], PATHINFO_FILENAME)),
                        'description' => sanitize_input($descriptions[$i] ?? ''),
                        'image_path' => $upload_result['filename'],
                        'alt_text' => sanitize_input($alt_texts[$i] ?? ''),
                        'category' => sanitize_input($categories[$i] ?? ''),
                        'is_featured' => isset($featured[$i]) ? 1 : 0,
                        'uploaded_by' => $current_user['id']
                    ];

                    // Save to database
                    $image_id = $db->insert('gallery', $image_data);

                    if ($image_id) {
                        $uploaded_count++;
                        $uploaded_images[] = [
                            'id' => $image_id,
                            'filename' => $upload_result['filename'],
                            'title' => $image_data['title']
                        ];

                        // Create thumbnails
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

                        log_activity('gallery_upload', "Image uploaded: {$image_data['title']}");
                    } else {
                        $failed_count++;
                        $errors[] = "Failed to save image: {$file['name']}";
                        // Delete uploaded file if database insert failed
                        unlink($upload_result['path']);
                    }
                } else {
                    $failed_count++;
                    $errors[] = "Failed to upload {$file['name']}: {$upload_result['message']}";
                }
            }

            // Set result message
            if ($uploaded_count > 0) {
                $success_message = "$uploaded_count image(s) uploaded successfully.";
                if ($failed_count > 0) {
                    $success_message .= " $failed_count image(s) failed to upload.";
                }
            } else {
                $error_message = "No images were uploaded. " . implode(' ', $errors);
            }
        } else {
            $error_message = 'No images selected for upload.';
        }
    }
}

// Get existing categories for dropdown
$categories = $db->raw(
    "SELECT DISTINCT category 
     FROM gallery 
     WHERE category IS NOT NULL AND category != '' 
     ORDER BY category ASC"
);
$existing_categories = $categories ? array_column($categories->fetchAll(), 'category') : [];

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Upload Images</h1>
                    <p class="text-muted">Add new images to your gallery</p>
                </div>
                <div class="page-actions">
                    <a href="manage-gallery.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Gallery
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Form -->
    <div class="row">
        <div class="col-12">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <?php if ($uploaded_images): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-check me-2"></i>
                                Successfully Uploaded Images
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($uploaded_images as $uploaded): ?>
                                    <div class="col-md-3 mb-3">
                                        <div class="uploaded-preview text-center">
                                            <img src="<?php echo UPLOADS_URL . '/gallery/' . $uploaded['filename']; ?>"
                                                alt="<?php echo htmlspecialchars($uploaded['title']); ?>"
                                                class="img-thumbnail mb-2"
                                                style="width: 100px; height: 100px; object-fit: cover;">
                                            <p class="small mb-0"><?php echo htmlspecialchars($uploaded['title']); ?></p>
                                            <a href="edit-gallery.php?id=<?php echo $uploaded['id']; ?>"
                                                class="btn btn-sm btn-outline-primary mt-1">
                                                Edit Details
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
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
                    <h5 class="mb-0">Upload New Images</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" id="uploadForm">
                        <?php echo csrf_field(); ?>

                        <!-- File Upload Area -->
                        <div class="upload-area mb-4">
                            <div class="form-group">
                                <label for="images" class="form-label">Select Images</label>
                                <div class="upload-dropzone border-2 border-dashed rounded p-5 text-center" id="dropzone">
                                    <div class="upload-icon mb-3">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted"></i>
                                    </div>
                                    <h6 class="mb-2">Drag and drop images here</h6>
                                    <p class="text-muted mb-3">or</p>
                                    <input type="file"
                                        class="form-control"
                                        id="images"
                                        name="images[]"
                                        multiple
                                        accept="image/*"
                                        style="display: none;">
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('images').click();">
                                        <i class="fas fa-plus me-2"></i>Choose Images
                                    </button>
                                    <div class="mt-3">
                                        <small class="text-muted">
                                            Supported formats: JPG, PNG, GIF, WebP | Max size: <?php echo format_file_size(MAX_FILE_SIZE); ?> per image
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Images Preview -->
                        <div id="imagePreviewContainer" class="mb-4" style="display: none;">
                            <h6 class="mb-3">Selected Images</h6>
                            <div id="imagePreviewList" class="row"></div>
                        </div>

                        <!-- Global Settings -->
                        <div class="global-settings mb-4">
                            <h6 class="mb-3">Global Settings (Applied to all images)</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="globalCategory" class="form-label">Category</label>
                                        <div class="input-group">
                                            <input type="text"
                                                class="form-control"
                                                id="globalCategory"
                                                placeholder="Enter category name"
                                                list="categoryList">
                                            <button type="button" class="btn btn-outline-secondary" id="applyCategoryBtn">
                                                Apply to All
                                            </button>
                                        </div>
                                        <datalist id="categoryList">
                                            <?php foreach ($existing_categories as $category): ?>
                                                <option value="<?php echo htmlspecialchars($category); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Global Actions</label>
                                        <div class="d-flex gap-2">
                                            <button type="button" class="btn btn-outline-warning btn-sm" id="markAllFeaturedBtn">
                                                <i class="fas fa-star me-1"></i>Mark All Featured
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="autoFillTitlesBtn">
                                                <i class="fas fa-magic me-1"></i>Auto-fill Titles
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span id="selectedCount" class="text-muted">No images selected</span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" id="clearAllBtn" disabled>
                                    <i class="fas fa-times me-2"></i>Clear All
                                </button>
                                <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                    <i class="fas fa-upload me-2"></i>
                                    <span id="uploadBtnText">Upload Images</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Guidelines -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 bg-light">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Upload Guidelines
                    </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Use high-quality images (minimum 800x600 pixels)
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Supported formats: JPG, PNG, GIF, WebP
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    Maximum file size: <?php echo format_file_size(MAX_FILE_SIZE); ?> per image
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    Use descriptive titles and alt text for accessibility
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    Organize images with categories for better management
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-lightbulb text-warning me-2"></i>
                                    Mark important images as featured to highlight them
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .upload-dropzone {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .upload-dropzone:hover,
    .upload-dropzone.dragover {
        border-color: #28a745 !important;
        background-color: rgba(40, 167, 69, 0.05);
    }

    .image-preview-item {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .image-preview-item:hover {
        border-color: #28a745;
        transform: translateY(-2px);
    }

    .image-preview-item .preview-image {
        height: 150px;
        object-fit: cover;
    }

    .remove-image {
        position: absolute;
        top: 5px;
        right: 5px;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        background: rgba(220, 53, 69, 0.9);
        color: white;
        border: none;
        font-size: 12px;
        cursor: pointer;
        display: none;
    }

    .image-preview-item:hover .remove-image {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .upload-progress {
        height: 4px;
        background: #28a745;
        border-radius: 2px;
        transition: width 0.3s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('images');
        const imagePreviewContainer = document.getElementById('imagePreviewContainer');
        const imagePreviewList = document.getElementById('imagePreviewList');
        const selectedCount = document.getElementById('selectedCount');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const clearAllBtn = document.getElementById('clearAllBtn');
        const uploadForm = document.getElementById('uploadForm');

        let selectedFiles = [];
        let fileCounter = 0;

        // Drag and drop functionality
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            dropzone.classList.remove('dragover');

            const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
            addFiles(files);
        });

        // Click to select files
        dropzone.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            addFiles(files);
        });

        function addFiles(files) {
            files.forEach(file => {
                if (file.size > <?php echo MAX_FILE_SIZE; ?>) {
                    alert(`File "${file.name}" is too large. Maximum size is <?php echo format_file_size(MAX_FILE_SIZE); ?>.`);
                    return;
                }

                const fileId = 'file_' + (fileCounter++);
                selectedFiles.push({
                    id: fileId,
                    file: file,
                    title: file.name.replace(/\.[^/.]+$/, ""), // Remove extension
                    description: '',
                    altText: '',
                    category: '',
                    featured: false
                });

                createImagePreview(selectedFiles[selectedFiles.length - 1]);
            });

            updateUI();
        }

        function createImagePreview(fileData) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const col = document.createElement('div');
                col.className = 'col-md-6 col-lg-4 mb-3';
                col.innerHTML = `
                <div class="image-preview-item p-3 h-100" data-file-id="${fileData.id}">
                    <button type="button" class="remove-image btn" onclick="removeFile('${fileData.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="text-center mb-3">
                        <img src="${e.target.result}" alt="Preview" class="preview-image img-fluid rounded">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label small">Title</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="titles[]" value="${fileData.title}" 
                               data-field="title" data-file-id="${fileData.id}">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label small">Description</label>
                        <textarea class="form-control form-control-sm" rows="2" 
                                  name="descriptions[]" placeholder="Optional description"
                                  data-field="description" data-file-id="${fileData.id}"></textarea>
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label small">Alt Text</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="alt_texts[]" placeholder="Accessibility description"
                               data-field="altText" data-file-id="${fileData.id}">
                    </div>
                    <div class="form-group mb-2">
                        <label class="form-label small">Category</label>
                        <input type="text" class="form-control form-control-sm" 
                               name="categories[]" placeholder="Image category"
                               data-field="category" data-file-id="${fileData.id}"
                               list="categoryList">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="featured[]" value="${fileData.id}"
                               data-field="featured" data-file-id="${fileData.id}">
                        <label class="form-check-label small">
                            Mark as featured
                        </label>
                    </div>
                </div>
            `;

                imagePreviewList.appendChild(col);

                // Add event listeners for form fields
                const inputs = col.querySelectorAll('input, textarea');
                inputs.forEach(input => {
                    input.addEventListener('change', function() {
                        updateFileData(this.dataset.fileId, this.dataset.field,
                            this.type === 'checkbox' ? this.checked : this.value);
                    });
                });
            };
            reader.readAsDataURL(fileData.file);
        }

        function updateFileData(fileId, field, value) {
            const fileData = selectedFiles.find(f => f.id === fileId);
            if (fileData) {
                fileData[field] = value;
            }
        }

        function removeFile(fileId) {
            selectedFiles = selectedFiles.filter(f => f.id !== fileId);
            const element = document.querySelector(`[data-file-id="${fileId}"]`).closest('.col-md-6');
            element.remove();
            updateUI();
        }

        function updateUI() {
            const count = selectedFiles.length;
            selectedCount.textContent = count === 0 ? 'No images selected' : `${count} image(s) selected`;
            uploadBtn.disabled = count === 0;
            clearAllBtn.disabled = count === 0;
            uploadBtnText.textContent = count === 0 ? 'Upload Images' : `Upload ${count} Image(s)`;

            imagePreviewContainer.style.display = count > 0 ? 'block' : 'none';

            // Update file input
            const dt = new DataTransfer();
            selectedFiles.forEach(fileData => dt.items.add(fileData.file));
            fileInput.files = dt.files;
        }

        // Global actions
        document.getElementById('applyCategoryBtn').addEventListener('click', function() {
            const globalCategory = document.getElementById('globalCategory').value.trim();
            if (globalCategory) {
                const categoryInputs = document.querySelectorAll('input[data-field="category"]');
                categoryInputs.forEach(input => {
                    input.value = globalCategory;
                    updateFileData(input.dataset.fileId, 'category', globalCategory);
                });
            }
        });

        document.getElementById('markAllFeaturedBtn').addEventListener('click', function() {
            const featuredCheckboxes = document.querySelectorAll('input[data-field="featured"]');
            featuredCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
                updateFileData(checkbox.dataset.fileId, 'featured', true);
            });
        });

        document.getElementById('autoFillTitlesBtn').addEventListener('click', function() {
            const titleInputs = document.querySelectorAll('input[data-field="title"]');
            titleInputs.forEach(input => {
                const fileData = selectedFiles.find(f => f.id === input.dataset.fileId);
                if (fileData) {
                    // Create a more readable title from filename
                    const title = fileData.file.name
                        .replace(/\.[^/.]+$/, "") // Remove extension
                        .replace(/[-_]/g, ' ') // Replace dashes and underscores with spaces
                        .replace(/\b\w/g, char => char.toUpperCase()); // Capitalize first letter of each word

                    input.value = title;
                    updateFileData(fileData.id, 'title', title);
                }
            });
        });

        document.getElementById('clearAllBtn').addEventListener('click', function() {
            if (confirm('Are you sure you want to remove all selected images?')) {
                selectedFiles = [];
                imagePreviewList.innerHTML = '';
                fileInput.value = '';
                updateUI();
            }
        });

        // Form submission with progress
        uploadForm.addEventListener('submit', function(e) {
            if (selectedFiles.length === 0) {
                e.preventDefault();
                alert('Please select at least one image to upload.');
                return;
            }

            // Show loading state
            uploadBtn.disabled = true;
            uploadBtnText.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Uploading...';

            // Add progress indicators to each image preview
            const previewItems = document.querySelectorAll('.image-preview-item');
            previewItems.forEach(item => {
                const progressBar = document.createElement('div');
                progressBar.className = 'upload-progress mt-2';
                progressBar.style.width = '0%';
                item.appendChild(progressBar);
            });

            // Simulate progress (in real implementation, you'd use XMLHttpRequest for actual progress)
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress >= 95) {
                    progress = 95;
                    clearInterval(progressInterval);
                }

                previewItems.forEach(item => {
                    const progressBar = item.querySelector('.upload-progress');
                    if (progressBar) {
                        progressBar.style.width = progress + '%';
                    }
                });
            }, 200);
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + V to paste images from clipboard
            if ((e.ctrlKey || e.metaKey) && e.key === 'v') {
                navigator.clipboard.read().then(items => {
                    const imageItems = items.filter(item =>
                        item.types.some(type => type.startsWith('image/'))
                    );

                    if (imageItems.length > 0) {
                        imageItems.forEach(item => {
                            const imageType = item.types.find(type => type.startsWith('image/'));
                            if (imageType) {
                                item.getType(imageType).then(blob => {
                                    const file = new File([blob], 'pasted-image.png', {
                                        type: blob.type
                                    });
                                    addFiles([file]);
                                });
                            }
                        });
                    }
                }).catch(err => {
                    console.log('Could not read clipboard: ', err);
                });
            }

            // Escape key to clear selection
            if (e.key === 'Escape' && selectedFiles.length > 0) {
                if (confirm('Clear all selected images?')) {
                    clearAllBtn.click();
                }
            }
        });

        // Auto-save form data to localStorage
        function saveFormData() {
            const formData = {
                globalCategory: document.getElementById('globalCategory').value,
                selectedFiles: selectedFiles.map(f => ({
                    name: f.file.name,
                    title: f.title,
                    description: f.description,
                    altText: f.altText,
                    category: f.category,
                    featured: f.featured
                }))
            };
            localStorage.setItem('ecct_upload_form_data', JSON.stringify(formData));
        }

        function loadFormData() {
            const savedData = localStorage.getItem('ecct_upload_form_data');
            if (savedData) {
                try {
                    const formData = JSON.parse(savedData);
                    document.getElementById('globalCategory').value = formData.globalCategory || '';
                    // Note: We can't restore files due to security restrictions,
                    // but we can show a message about the saved form data
                    if (formData.selectedFiles && formData.selectedFiles.length > 0) {
                        console.log('Found saved form data for', formData.selectedFiles.length, 'files');
                    }
                } catch (e) {
                    console.log('Could not load saved form data:', e);
                }
            }
        }

        // Save form data periodically
        setInterval(saveFormData, 30000); // Save every 30 seconds

        // Load form data on page load
        loadFormData();

        // Clear saved data on successful upload
        window.addEventListener('beforeunload', function() {
            if (selectedFiles.length === 0) {
                localStorage.removeItem('ecct_upload_form_data');
            }
        });

        // Make removeFile function global
        window.removeFile = removeFile;
    });
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>