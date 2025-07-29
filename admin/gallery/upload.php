<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

$success_count = 0;
$errors = [];

if ($_POST && isset($_FILES['images'])) {
    $upload_dir = ECCT_ROOT . '/assets/uploads/gallery/';

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle multiple file uploads
    $files = $_FILES['images'];
    $total_files = count($files['name']);

    for ($i = 0; $i < $total_files; $i++) {
        if ($files['error'][$i] === UPLOAD_ERR_OK) {
            $file_name = $files['name'][$i];
            $file_tmp = $files['tmp_name'][$i];
            $file_size = $files['size'][$i];
            $file_type = $files['type'][$i];

            // Validate file type
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            if (!in_array($file_type, $allowed_types)) {
                $errors[] = "File {$file_name}: Invalid file type. Only JPG, PNG, and GIF allowed.";
                continue;
            }

            // Validate file size (5MB max)
            if ($file_size > 5 * 1024 * 1024) {
                $errors[] = "File {$file_name}: File too large. Maximum size is 5MB.";
                continue;
            }

            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Get image dimensions
                $image_info = getimagesize($upload_path);
                $width = $image_info[0] ?? 0;
                $height = $image_info[1] ?? 0;

                // Use individual title or filename as fallback
                $image_title = $title ?: pathinfo($file_name, PATHINFO_FILENAME);

                $data = [
                    'title' => $image_title,
                    'description' => $description,
                    'image_path' => 'assets/uploads/gallery/' . $new_filename,
                    'category' => $category,
                    // 'width' => $width,
                    // 'height' => $height,
                    // 'file_size' => $file_size,
                    // 'is_active' => $is_active,
                    'uploaded_by' => $current_user['id'],
                    'uploaded_at' => date('Y-m-d H:i:s')
                ];

                if ($db->insert('gallery', $data)) {
                    $success_count++;
                } else {
                    $errors[] = "File {$file_name}: Database error occurred.";
                    // Delete uploaded file if database insert failed
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }
                }
            } else {
                $errors[] = "File {$file_name}: Failed to upload file.";
            }
        } else {
            $errors[] = "File {$files['name'][$i]}: Upload error occurred.";
        }
    }

    if ($success_count > 0) {
        $success = "{$success_count} image(s) uploaded successfully!";
        if (!empty($errors)) {
            $success .= " Some files had errors (see below).";
        }
    }
}

// Get existing categories for dropdown
$categories = $db->raw("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL AND category != '' ORDER BY category");
$categories = $categories ? $categories->fetchAll(PDO::FETCH_COLUMN) : [];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Upload Images</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Gallery
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Errors occurred:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                <div class="mb-4">
                                    <label for="images" class="form-label">Select Images *</label>
                                    <input type="file" class="form-control" id="images" name="images[]"
                                        accept="image/*" multiple required>
                                    <div class="form-text">
                                        Select multiple images (JPG, PNG, GIF). Maximum size: 5MB per image.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                                        placeholder="Leave empty to use filename">
                                    <div class="form-text">
                                        If uploading multiple images, this title will be used for all. Leave empty to use individual filenames.
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        placeholder="Optional description for the images"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label">Category</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="category" name="category"
                                            value="<?php echo htmlspecialchars($_POST['category'] ?? ''); ?>"
                                            placeholder="e.g., Beach Cleanup, Tree Planting"
                                            list="categoryList">
                                        <datalist id="categoryList">
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>">
                                                <?php endforeach; ?>
                                        </datalist>
                                    </div>
                                </div>

                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                        <?php echo isset($_POST['is_active']) || !$_POST ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_active">
                                        Active (visible on website)
                                    </label>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-upload me-2"></i>Upload Images
                                    </button>
                                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Upload Guidelines -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Upload Guidelines</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Supported formats:</strong> JPG, PNG, GIF
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Maximum size:</strong> 5MB per image
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Recommended size:</strong> 1200x800px or higher
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-check text-success me-2"></i>
                                    <strong>Multiple upload:</strong> Select multiple files at once
                                </li>
                                <li class="mb-2">
                                    <i class="fas fa-info text-info me-2"></i>
                                    Images will be automatically resized for web display
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Image Preview -->
                    <div class="card mt-3" id="previewCard" style="display: none;">
                        <div class="card-header">
                            <h5 class="mb-0">Preview</h5>
                        </div>
                        <div class="card-body" id="imagePreview">
                            <!-- Preview images will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    document.getElementById('images').addEventListener('change', function(e) {
        const files = e.target.files;
        const previewCard = document.getElementById('previewCard');
        const previewContainer = document.getElementById('imagePreview');

        if (files.length > 0) {
            previewContainer.innerHTML = '';
            previewCard.style.display = 'block';

            // Show first 3 images as preview
            const maxPreviews = Math.min(files.length, 3);

            for (let i = 0; i < maxPreviews; i++) {
                const file = files[i];
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'img-thumbnail mb-2';
                        img.style.width = '100%';
                        img.style.height = '100px';
                        img.style.objectFit = 'cover';
                        previewContainer.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                }
            }

            if (files.length > 3) {
                const moreText = document.createElement('p');
                moreText.className = 'text-muted small';
                moreText.textContent = `+${files.length - 3} more images`;
                previewContainer.appendChild(moreText);
            }
        } else {
            previewCard.style.display = 'none';
        }
    });
</script>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>