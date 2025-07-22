<?php

/**
 * Edit News Article - ECCT Admin Panel
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_permission('manage_content');

// Get database instance
$db = new Database();

// Get article ID
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$article_id) {
    set_flash('error', 'Invalid article ID.');
    redirect(SITE_URL . '/admin/news/manage-news.php');
}

// Get existing article
$article = $db->selectOne('news', ['id' => $article_id]);

if (!$article) {
    set_flash('error', 'Article not found.');
    redirect(SITE_URL . '/admin/news/manage-news.php');
}

// Get article tags
$article_tags = $db->raw(
    "SELECT t.name FROM tags t 
     JOIN news_tags nt ON t.id = nt.tag_id 
     WHERE nt.news_id = ?",
    [$article_id]
);
$existing_tags = $article_tags ? array_column($article_tags->fetchAll(), 'name') : [];

$success_message = '';
$error_message = '';
$form_data = $article; // Initialize with existing data
$form_data['tags'] = implode(', ', $existing_tags);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        // Validation rules
        $validation_rules = [
            'title' => ['required' => true, 'min_length' => 5, 'max_length' => 200, 'label' => 'Title'],
            'content' => ['required' => true, 'min_length' => 50, 'label' => 'Content']
        ];

        $form_data = [
            'title' => sanitize_input($_POST['title'] ?? ''),
            'slug' => create_slug($_POST['title'] ?? ''),
            'excerpt' => sanitize_input($_POST['excerpt'] ?? ''),
            'content' => clean_html($_POST['content'] ?? ''),
            'meta_title' => sanitize_input($_POST['meta_title'] ?? ''),
            'meta_description' => sanitize_input($_POST['meta_description'] ?? ''),
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'publish_date' => !empty($_POST['publish_date']) ? $_POST['publish_date'] : $article['publish_date'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Keep existing tags for form repopulation
        $form_data['tags'] = sanitize_input($_POST['tags'] ?? '');

        $validation_errors = validate_form($form_data, $validation_rules);

        // Check if slug already exists (but allow current article)
        if (empty($validation_errors)) {
            $existing_slug = $db->selectOne('news', ['slug' => $form_data['slug']]);
            if ($existing_slug && $existing_slug['id'] != $article_id) {
                $validation_errors['title'] = 'An article with similar title already exists.';
            }
        }

        // Handle file upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOADS_PATH . '/news';
            $upload_result = upload_image($_FILES['featured_image'], $upload_dir);

            if ($upload_result['success']) {
                // Delete old image if exists
                if ($article['featured_image']) {
                    delete_image($upload_dir . '/' . $article['featured_image']);
                }

                $form_data['featured_image'] = $upload_result['filename'];

                // Create thumbnails
                $image_path = $upload_result['path'];
                $thumb_dir = $upload_dir . '/thumbs';
                foreach (THUMB_SIZES as $size => $dimensions) {
                    $thumb_path = $thumb_dir . '/' . pathinfo($upload_result['filename'], PATHINFO_FILENAME) . '_' . $size . '.' . pathinfo($upload_result['filename'], PATHINFO_EXTENSION);
                    create_thumbnail($image_path, $thumb_path, $dimensions['width'], $dimensions['height']);
                }
            } else {
                $validation_errors['featured_image'] = $upload_result['message'];
            }
        } else {
            // Keep existing image
            $form_data['featured_image'] = $article['featured_image'];
        }

        // Handle image removal
        if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1' && $article['featured_image']) {
            delete_image(UPLOADS_PATH . '/news/' . $article['featured_image']);
            $form_data['featured_image'] = null;
        }

        if (empty($validation_errors)) {
            // Auto-generate meta fields if empty
            if (empty($form_data['meta_title'])) {
                $form_data['meta_title'] = $form_data['title'];
            }
            if (empty($form_data['meta_description'])) {
                $form_data['meta_description'] = generate_meta_description($form_data['content']);
            }

            // Remove tags from update data
            $update_data = $form_data;
            unset($update_data['tags']);

            if ($db->update('news', $update_data, ['id' => $article_id])) {
                log_activity('news_update', "News article updated: {$form_data['title']}", 'news', $article_id);

                // Handle tags
                // First, remove existing tags
                $db->delete('news_tags', ['news_id' => $article_id]);

                // Then add new tags
                if (!empty($_POST['tags'])) {
                    $tags = array_map('trim', explode(',', $_POST['tags']));
                    foreach ($tags as $tag_name) {
                        if (!empty($tag_name)) {
                            // Find or create tag
                            $tag = $db->selectOne('tags', ['name' => $tag_name]);
                            if (!$tag) {
                                $tag_id = $db->insert('tags', [
                                    'name' => $tag_name,
                                    'slug' => create_slug($tag_name)
                                ]);
                            } else {
                                $tag_id = $tag['id'];
                            }

                            // Link tag to news
                            if ($tag_id) {
                                $db->insert('news_tags', [
                                    'news_id' => $article_id,
                                    'tag_id' => $tag_id
                                ]);
                            }
                        }
                    }
                }

                set_flash('success', 'Article updated successfully!');
                redirect(SITE_URL . '/admin/news/manage-news.php');
            } else {
                $error_message = 'Failed to update article. Please try again.';
            }
        } else {
            $error_message = 'Please correct the following errors: ' . implode(', ', $validation_errors);
        }
    }
}

// Get available tags for suggestions
$available_tags = $db->select('tags', [], ['order_by' => 'name ASC']);

// Page variables
$page_title = 'Edit Article: ' . htmlspecialchars($article['title']) . ' - ECCT Admin';
$breadcrumbs = [
    ['title' => 'News Management', 'url' => 'manage-news.php'],
    ['title' => 'Edit Article']
];

// Additional CSS for rich text editor
$additional_css = [
    'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css'
];

// Additional JS for rich text editor
$additional_js = [
    'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js'
];

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit News Article</h1>
                    <p class="text-muted">Modify article: <?php echo htmlspecialchars(truncate_text($article['title'], 50)); ?></p>
                </div>
                <div>
                    <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article_id; ?>" target="_blank"
                        class="btn btn-outline-info me-2">
                        <i class="fas fa-external-link-alt me-2"></i>Preview
                    </a>
                    <a href="manage-news.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Articles
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Article Info Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3"></i>
                            <div>
                                <strong>Article Status:</strong>
                                <span class="badge bg-<?php echo $article['is_published'] ? 'success' : 'warning'; ?> ms-1">
                                    <?php echo $article['is_published'] ? 'Published' : 'Draft'; ?>
                                </span>
                                <?php if ($article['is_featured']): ?>
                                    <span class="badge bg-primary ms-1">Featured</span>
                                <?php endif; ?>
                                <br>
                                <small class="text-muted">
                                    Created: <?php echo format_date($article['created_at']); ?> |
                                    Last Updated: <?php echo format_date($article['updated_at']); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-md-end">
                        <small class="text-muted">Article ID: #<?php echo $article['id']; ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
        <?php echo csrf_field(); ?>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Article Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="<?php echo htmlspecialchars($form_data['title']); ?>"
                                required maxlength="200">
                            <div class="form-text">This will be the main headline for your article</div>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"
                                maxlength="500"><?php echo htmlspecialchars($form_data['excerpt']); ?></textarea>
                            <div class="form-text">Brief summary of the article (optional, will be auto-generated if empty)</div>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($form_data['content']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label">Tags</label>
                            <input type="text" class="form-control" id="tags" name="tags"
                                value="<?php echo htmlspecialchars($form_data['tags']); ?>"
                                placeholder="Enter tags separated by commas">
                            <div class="form-text">
                                Available tags:
                                <?php foreach ($available_tags as $tag): ?>
                                    <span class="badge bg-light text-dark me-1 tag-suggestion" data-tag="<?php echo htmlspecialchars($tag['name']); ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEO Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">SEO Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="meta_title" class="form-label">Meta Title</label>
                            <input type="text" class="form-control" id="meta_title" name="meta_title"
                                value="<?php echo htmlspecialchars($form_data['meta_title']); ?>"
                                maxlength="200">
                            <div class="form-text">Leave empty to use article title</div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_description" class="form-label">Meta Description</label>
                            <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                maxlength="160"><?php echo htmlspecialchars($form_data['meta_description']); ?></textarea>
                            <div class="form-text">Leave empty to auto-generate from content</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Publish Settings -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Publish Settings</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published"
                                    <?php echo $form_data['is_published'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_published">
                                    Publish article
                                </label>
                            </div>
                            <div class="form-text">Uncheck to save as draft</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured"
                                    <?php echo $form_data['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_featured">
                                    Mark as featured
                                </label>
                            </div>
                            <div class="form-text">Featured articles appear on homepage</div>
                        </div>

                        <div class="mb-3">
                            <label for="publish_date" class="form-label">Publish Date</label>
                            <input type="datetime-local" class="form-control" id="publish_date" name="publish_date"
                                value="<?php echo date('Y-m-d\TH:i', strtotime($form_data['publish_date'])); ?>">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Article
                            </button>
                            <a href="manage-news.php" class="btn btn-outline-secondary">
                                Cancel Changes
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Featured Image</h5>
                    </div>
                    <div class="card-body">
                        <?php if ($article['featured_image']): ?>
                            <div class="current-image mb-3">
                                <img src="<?php echo UPLOADS_URL . '/news/' . $article['featured_image']; ?>"
                                    alt="Current featured image" class="img-fluid rounded" style="max-height: 200px;">
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image" value="1">
                                        <label class="form-check-label text-danger" for="remove_image">
                                            Remove current image
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="featured_image" class="form-label">
                                <?php echo $article['featured_image'] ? 'Replace Image' : 'Upload Image'; ?>
                            </label>
                            <input type="file" class="form-control" id="featured_image" name="featured_image"
                                accept="image/*">
                            <div class="form-text">Recommended size: 800x600px. Max size: 5MB</div>
                        </div>

                        <div id="image-preview" class="d-none">
                            <img src="" alt="Preview" class="img-fluid rounded" style="max-height: 200px;">
                        </div>
                    </div>
                </div>

                <!-- Article Actions -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Quick Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="button" class="btn btn-outline-info btn-sm" onclick="previewArticle()">
                                <i class="fas fa-eye me-2"></i>Preview Changes
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="saveAsDraft()">
                                <i class="fas fa-save me-2"></i>Save as Draft
                            </button>
                            <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article_id; ?>" target="_blank"
                                class="btn btn-outline-success btn-sm">
                                <i class="fas fa-external-link-alt me-2"></i>View Live
                            </a>
                        </div>

                        <hr>

                        <div class="text-center">
                            <small class="text-muted">
                                <strong>Slug:</strong> <?php echo htmlspecialchars($article['slug']); ?><br>
                                <strong>Created:</strong> <?php echo format_date($article['created_at']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Summernote rich text editor
        $('#content').summernote({
            height: 400,
            placeholder: 'Write your article content here...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });

        // Auto-generate slug from title
        document.getElementById('title').addEventListener('input', function() {
            // Auto-fill meta title if empty
            const metaTitle = document.getElementById('meta_title');
            if (!metaTitle.value || metaTitle.value === metaTitle.defaultValue) {
                metaTitle.value = this.value;
            }
        });

        // Tag suggestions
        document.querySelectorAll('.tag-suggestion').forEach(function(tag) {
            tag.addEventListener('click', function() {
                const tagsInput = document.getElementById('tags');
                const currentTags = tagsInput.value.split(',').map(t => t.trim()).filter(t => t);
                const newTag = this.dataset.tag;

                if (!currentTags.includes(newTag)) {
                    currentTags.push(newTag);
                    tagsInput.value = currentTags.join(', ');
                }
            });
        });

        // Image preview
        document.getElementById('featured_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    const img = preview.querySelector('img');
                    img.src = e.target.result;
                    preview.classList.remove('d-none');

                    // Hide current image section when new image is selected
                    const currentImage = document.querySelector('.current-image');
                    if (currentImage) {
                        currentImage.style.opacity = '0.5';
                    }
                };
                reader.readAsDataURL(file);
            }
        });

        // Handle remove image checkbox
        document.getElementById('remove_image')?.addEventListener('change', function() {
            const currentImage = document.querySelector('.current-image img');
            if (currentImage) {
                currentImage.style.opacity = this.checked ? '0.3' : '1';
                currentImage.style.filter = this.checked ? 'grayscale(100%)' : 'none';
            }
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

        // Warn about unsaved changes
        let formChanged = false;
        form.addEventListener('input', function() {
            formChanged = true;
        });

        window.addEventListener('beforeunload', function(e) {
            if (formChanged) {
                e.preventDefault();
                e.returnValue = '';
            }
        });

        // Reset change flag on form submit
        form.addEventListener('submit', function() {
            formChanged = false;
        });
    });

    function previewArticle() {
        const title = document.getElementById('title').value;
        const content = $('#content').summernote('code');

        if (!title || !content) {
            alert('Please enter title and content to preview');
            return;
        }

        const previewWindow = window.open('', '_blank');
        previewWindow.document.write(`
        <html>
            <head>
                <title>Preview: ${title}</title>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body class="bg-light">
                <div class="container py-5">
                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <strong>Preview Mode:</strong> This is a preview of your changes. Save the article to make changes live.
                                    </div>
                                    <h1 class="card-title">${title}</h1>
                                    <div class="card-text">${content}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
        </html>
    `);
    }

    function saveAsDraft() {
        document.getElementById('is_published').checked = false;
        document.querySelector('form').submit();
    }
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>