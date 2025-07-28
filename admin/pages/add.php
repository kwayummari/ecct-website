<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_current_user();

if ($_POST) {
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']);
    $content = trim($_POST['content']);
    $meta_description = trim($_POST['meta_description']);
    $meta_keywords = trim($_POST['meta_keywords']);
    $sort_order = (int)$_POST['sort_order'];
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    // Auto-generate slug if empty
    if (empty($slug)) {
        $slug = create_slug($title);
    } else {
        $slug = create_slug($slug);
    }

    if (!empty($title) && !empty($content)) {
        // Check if slug exists
        if ($db->exists('pages', ['slug' => $slug])) {
            $error = 'A page with this slug already exists. Please use a different slug.';
        } else {
            $data = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'meta_description' => $meta_description,
                'meta_keywords' => $meta_keywords,
                'sort_order' => $sort_order,
                'is_published' => $is_published,
                'created_by' => $current_user['id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($db->insert('pages', $data)) {
                header('Location: list.php?success=Page added successfully');
                exit;
            } else {
                $error = 'Error adding page';
            }
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
                <h1 class="h2">Add New Page</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Pages
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Page Content</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Page Title *</label>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="slug" class="form-label">Page Slug</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><?php echo SITE_URL; ?>/</span>
                                        <input type="text" class="form-control" id="slug" name="slug"
                                            value="<?php echo htmlspecialchars($_POST['slug'] ?? ''); ?>"
                                            placeholder="leave-empty-for-auto-generation">
                                    </div>
                                    <div class="form-text">URL-friendly version of the title. Leave empty to auto-generate.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label">Page Content *</label>
                                    <textarea class="form-control" id="content" name="content" rows="15" required><?php echo htmlspecialchars($_POST['content'] ?? ''); ?></textarea>
                                    <div class="form-text">You can use HTML tags for formatting.</div>
                                </div>
                            </div>
                        </div>

                        <!-- SEO Settings -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">SEO Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="meta_description" class="form-label">Meta Description</label>
                                    <textarea class="form-control" id="meta_description" name="meta_description" rows="3"
                                        maxlength="160" placeholder="Brief description for search engines (max 160 characters)"><?php echo htmlspecialchars($_POST['meta_description'] ?? ''); ?></textarea>
                                    <div class="form-text"><span id="metaDescCount">0</span>/160 characters</div>
                                </div>

                                <div class="mb-3">
                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                    <input type="text" class="form-control" id="meta_keywords" name="meta_keywords"
                                        value="<?php echo htmlspecialchars($_POST['meta_keywords'] ?? ''); ?>"
                                        placeholder="keyword1, keyword2, keyword3">
                                    <div class="form-text">Separate keywords with commas</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Publishing Options -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Publishing</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="is_published" name="is_published"
                                        <?php echo isset($_POST['is_published']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="is_published">
                                        Publish immediately
                                    </label>
                                </div>

                                <div class="mb-3">
                                    <label for="sort_order" class="form-label">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                        value="<?php echo htmlspecialchars($_POST['sort_order'] ?? '0'); ?>" min="0">
                                    <div class="form-text">Lower numbers appear first in navigation</div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Save Page
                                    </button>
                                    <a href="list.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </div>

                        <!-- Page Guidelines -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Guidelines</h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled small">
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Use descriptive page titles
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Keep meta descriptions under 160 characters
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Use simple, readable URLs
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-check text-success me-2"></i>
                                        Structure content with headings
                                    </li>
                                    <li class="mb-2">
                                        <i class="fas fa-info text-info me-2"></i>
                                        HTML tags are allowed in content
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    // Auto-generate slug from title
    document.getElementById('title').addEventListener('input', function() {
        const title = this.value;
        const slug = title.toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim('-');

        if (document.getElementById('slug').value === '') {
            document.getElementById('slug').value = slug;
        }
    });

    // Count meta description characters
    document.getElementById('meta_description').addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('metaDescCount').textContent = count;

        if (count > 160) {
            document.getElementById('metaDescCount').style.color = 'red';
        } else {
            document.getElementById('metaDescCount').style.color = 'inherit';
        }
    });

    // Initial count
    document.addEventListener('DOMContentLoaded', function() {
        const metaDesc = document.getElementById('meta_description');
        if (metaDesc.value) {
            document.getElementById('metaDescCount').textContent = metaDesc.value.length;
        }
    });
</script>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>