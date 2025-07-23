<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/admin/includes/config.php';
require_once ECCT_ROOT . '/admin/includes/database.php';
require_once ECCT_ROOT . '/admin/includes/auth.php';
require_once ECCT_ROOT . '/admin/includes/helpers.php';

require_login();

$db = new Database();
$current_user = get_admin_user();

// Get news article
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$news = $db->selectOne('news', ['id' => $id]);

if (!$news) {
    header('Location: list.php?error=News article not found');
    exit;
}

if ($_POST) {
    $title = trim($_POST['title']);
    $excerpt = trim($_POST['excerpt']);
    $content = trim($_POST['content']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $featured_image = $news['featured_image'];

    // Handle image upload
    if (!empty($_FILES['featured_image']['name'])) {
        $upload_dir = ECCT_ROOT . '/assets/uploads/news/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['featured_image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $upload_path)) {
            // Delete old image
            if ($featured_image && file_exists(ECCT_ROOT . '/' . $featured_image)) {
                unlink(ECCT_ROOT . '/' . $featured_image);
            }
            $featured_image = 'assets/uploads/news/' . $filename;
        }
    }

    if (!empty($title) && !empty($content)) {
        $slug = create_slug($title);

        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt,
            'content' => $content,
            'featured_image' => $featured_image,
            'is_published' => $is_published,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($db->update('news', $data, ['id' => $id])) {
            header('Location: list.php?success=News article updated successfully');
            exit;
        } else {
            $error = 'Error updating news article';
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
                <h1 class="h2">Edit News Article</h1>
                <a href="list.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control" id="title" name="title"
                                value="<?php echo htmlspecialchars($_POST['title'] ?? $news['title']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="excerpt" class="form-label">Excerpt</label>
                            <textarea class="form-control" id="excerpt" name="excerpt" rows="3"
                                placeholder="Brief summary of the article"><?php echo htmlspecialchars($_POST['excerpt'] ?? $news['excerpt']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label">Content *</label>
                            <textarea class="form-control" id="content" name="content" rows="10" required><?php echo htmlspecialchars($_POST['content'] ?? $news['content']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="featured_image" class="form-label">Featured Image</label>
                            <?php if ($news['featured_image']): ?>
                                <div class="mb-2">
                                    <img src="<?php echo SITE_URL . '/' . $news['featured_image']; ?>"
                                        alt="Current image" class="img-thumbnail" style="max-width: 200px;">
                                    <p class="small text-muted">Current image</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                            <small class="form-text text-muted">Leave empty to keep current image. Supported formats: JPG, PNG, GIF</small>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_published" name="is_published"
                                <?php echo (isset($_POST['is_published']) ? $_POST['is_published'] : $news['is_published']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="is_published">
                                Published
                            </label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save me-2"></i>Update Article
                            </button>
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php require_once ECCT_ROOT . '/admin/includes/footer.php'; ?>