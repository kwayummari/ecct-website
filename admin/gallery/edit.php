<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

// Get image ID
$image_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$image_id) {
    header('Location: list.php?error=' . urlencode('Invalid image ID'));
    exit;
}

// Get image data
$image = $db->selectOne('gallery', ['id' => $image_id]);
if (!$image) {
    header('Location: list.php?error=' . urlencode('Image not found'));
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $alt_text = trim($_POST['alt_text'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);

    $errors = [];

    // Validation
    if (empty($title)) {
        $errors[] = 'Title is required';
    }

    if (empty($alt_text)) {
        $errors[] = 'Alt text is required for accessibility';
    }

    // Handle new image upload if provided
    $new_image_path = null;
    $new_thumbnail_path = null;

    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $upload_result = handle_image_upload($_FILES['new_image'], 'gallery');
        if ($upload_result['success']) {
            $new_image_path = $upload_result['image_path'];
            $new_thumbnail_path = $upload_result['thumbnail_path'];

            // Delete old image files
            if ($image['image_path'] && file_exists(ECCT_ROOT . '/' . $image['image_path'])) {
                unlink(ECCT_ROOT . '/' . $image['image_path']);
            }
            if ($image['thumbnail_path'] && file_exists(ECCT_ROOT . '/' . $image['thumbnail_path'])) {
                unlink(ECCT_ROOT . '/' . $image['thumbnail_path']);
            }
        } else {
            $errors[] = $upload_result['error'];
        }
    }

    if (empty($errors)) {
        $update_data = [
            'title' => $title,
            'description' => $description,
            'alt_text' => $alt_text,
            'category' => $category,
            'is_featured' => $is_featured,
            'is_published' => $is_published,
            'sort_order' => $sort_order
        ];

        // Add new image paths if uploaded
        if ($new_image_path) {
            $update_data['image_path'] = $new_image_path;
        }
        if ($new_thumbnail_path) {
            $update_data['thumbnail_path'] = $new_thumbnail_path;
        }

        if ($db->update('gallery', $update_data, ['id' => $image_id])) {
            header('Location: list.php?success=' . urlencode('Image updated successfully'));
            exit;
        } else {
            $errors[] = 'Error updating image';
        }
    }

    // Update image data with form values for redisplay
    $image = array_merge($image, [
        'title' => $title,
        'description' => $description,
        'alt_text' => $alt_text,
        'category' => $category,
        'is_featured' => $is_featured,
        'is_published' => $is_published,
        'sort_order' => $sort_order
    ]);
}

// Get existing categories for dropdown
$categories = $db->raw("SELECT DISTINCT category FROM gallery WHERE category IS NOT NULL AND category != '' ORDER BY category");
$existing_categories = $categories ? $categories->fetchAll(PDO::FETCH_COLUMN) : [];

// Predefined categories
$predefined_categories = [
    'reforestation',
    'marine conservation',
    'education',
    'wildlife protection',
    'waste management',
    'agriculture',
    'community events',
    'research',
    'training',
    'volunteers'
];

$all_categories = array_unique(array_merge($predefined_categories, $existing_categories));
sort($all_categories);

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Edit Gallery Image</h1>
                <div>
                    <a href="list.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-2"></i>Back to Gallery
                    </a>
                    <a href="<?php echo SITE_URL . '/' . $image['image_path']; ?>" target="_blank" class="btn btn-outline-info">
                        <i class="fas fa-external-link-alt me-2"></i>View Image
                    </a>
                </div>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Current Image Preview -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Current Image</h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="<?php echo SITE_URL . '/' . $image['image_path']; ?>"
                                class="img-fluid rounded mb-3"
                                alt="<?php echo htmlspecialchars($image['title']); ?>"
                                style="max-height: 300px; object-fit: cover;">

                            <div class="row text-start">
                                <div class="col-12 mb-2">
                                    <small class="text-muted">File:</small><br>
                                    <small><?php echo basename($image['image_path']); ?></small>
                                </div>
                                <div class="col-12 mb-2">
                                    <small class="text-muted">Uploaded:</small><br>
                                    <small><?php echo date('M j, Y g:i A', strtotime($image['created_at'])); ?></small>
                                </div>
                                <div class="col-12 mb-2">
                                    <small class="text-muted">Status:</small><br>
                                    <span class="badge bg-<?php echo $image['is_published'] ? 'success' : 'secondary'; ?>">
                                        <?php echo $image['is_published'] ? 'Published' : 'Unpublished'; ?>
                                    </span>
                                    <?php if ($image['is_featured']): ?>
                                        <span class="badge bg-warning">Featured</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Image Details</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="title" name="title"
                                            value="<?php echo htmlspecialchars($image['title']); ?>" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="sort_order" class="form-label">Sort Order</label>
                                        <input type="number" class="form-control" id="sort_order" name="sort_order"
                                            value="<?php echo $image['sort_order']; ?>" min="0">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($image['description'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="alt_text" class="form-label">Alt Text <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="alt_text" name="alt_text"
                                            value="<?php echo htmlspecialchars($image['alt_text'] ?? ''); ?>" required>
                                        <div class="form-text">Describe the image for accessibility</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" id="category" name="category">
                                            <option value="">Select Category</option>
                                            <?php foreach ($all_categories as $cat): ?>
                                                <option value="<?php echo htmlspecialchars($cat); ?>"
                                                    <?php echo ($image['category'] === $cat) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $cat))); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="new_image" class="form-label">Replace Image (Optional)</label>
                                    <input type="file" class="form-control" id="new_image" name="new_image" accept="image/*">
                                    <div class="form-text">Leave empty to keep current image. Supported: JPG, PNG, GIF (max 5MB)</div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_published" name="is_published"
                                                <?php echo $image['is_published'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_published">
                                                <strong>Published</strong>
                                                <div class="form-text">Show this image on the public website</div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                                <?php echo $image['is_featured'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="is_featured">
                                                <strong>Featured</strong>
                                                <div class="form-text">Highlight this image prominently</div>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-save me-2"></i>Update Image
                                        </button>
                                        <a href="list.php" class="btn btn-secondary ms-2">Cancel</a>
                                    </div>
                                    <a href="list.php?delete=<?php echo $image['id']; ?>"
                                        class="btn btn-outline-danger"
                                        onclick="return confirm('Are you sure you want to delete this image? This action cannot be undone.')">
                                        <i class="fas fa-trash me-2"></i>Delete Image
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
    // Auto-generate alt text from title if empty
    document.getElementById('title').addEventListener('input', function() {
        const altField = document.getElementById('alt_text');
        if (!altField.value.trim()) {
            altField.value = this.value;
        }
    });

    // Preview new image before upload
    document.getElementById('new_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.querySelector('.card-body img');
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
</script>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>