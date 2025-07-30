<?php

/**
 * Photo Gallery Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Get parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';

// Page variables
$page_title = 'Photo Gallery - ECCT';
$meta_description = 'Explore our photo gallery showcasing ECCT environmental conservation activities, community events, and impact across Tanzania.';
$page_class = 'gallery-page';

// Build conditions for gallery query
$conditions = [];
if ($category) {
    $conditions['category'] = $category;
}

// Get gallery images with pagination
$pagination_result = $db->paginate('gallery', $page, GALLERY_PER_PAGE, $conditions, [
    'order_by' => 'created_at DESC'
]);
$gallery_images = $pagination_result['data'];
$pagination = $pagination_result['pagination'];

// Get featured images
$featured_images = $db->select('gallery', ['is_featured' => 1], [
    'order_by' => 'created_at DESC',
    'limit' => 6
]);

// Get available categories
$categories = $db->raw(
    "SELECT category, COUNT(*) as image_count 
     FROM gallery 
     WHERE category IS NOT NULL AND category != '' 
     GROUP BY category 
     ORDER BY image_count DESC, category ASC"
);
$categories = $categories ? $categories->fetchAll() : [];

// Gallery statistics
$gallery_stats = [
    'total_images' => $db->count('gallery'),
    'categories' => count($categories),
    'featured' => $db->count('gallery', ['is_featured' => 1])
];

include 'includes/header.php';
?>

<style>
    /* Gallery Page Styles */
    .gallery-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(32, 136, 54, 0.7)),
            url('<?php echo SITE_URL; ?>/assets/images/green-generation/IMG_3264.JPG');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: #ffffff;
    }

    .gallery-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 1.5rem;
    }

    .gallery-hero p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 2rem;
    }

    .hero-badge {
        background: #208836;
        border: 2px solid #ffffff;
        border-radius: 25px;
        padding: 8px 20px;
        display: inline-block;
        margin-bottom: 2rem;
        font-weight: 500;
        color: #ffffff;
    }

    .gallery-stats {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 30px;
        margin-top: 3rem;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: white;
        display: block;
    }

    .stat-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .featured-section {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .featured-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
        position: relative;
        cursor: pointer;
    }

    .featured-card:hover {
        transform: translateY(-10px);
    }

    .featured-card::before {
        content: 'FEATURED';
        position: absolute;
        top: 15px;
        right: 15px;
        background: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
    }

    .featured-image {
        position: relative;
        overflow: hidden;
    }

    .featured-image img {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .featured-card:hover .featured-image img {
        transform: scale(1.05);
    }

    .featured-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.7));
        display: flex;
        align-items: flex-end;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .featured-card:hover .featured-overlay {
        opacity: 1;
    }

    .featured-content {
        color: white;
        width: 100%;
    }

    .featured-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .featured-category {
        background: #28a745;
        color: white;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        display: inline-block;
    }

    .gallery-content {
        padding: 80px 0;
        background: white;
    }

    .filter-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .filter-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .category-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .category-btn {
        background: white;
        border: 2px solid #e9ecef;
        color: #495057;
        padding: 8px 16px;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .category-btn:hover,
    .category-btn.active {
        background: #28a745;
        border-color: #28a745;
        color: white;
        transform: translateY(-2px);
    }

    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }

    .gallery-item {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        cursor: pointer;
        position: relative;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
    }

    .gallery-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .gallery-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .gallery-image img {
        transform: scale(1.05);
    }

    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.8));
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }

    .gallery-zoom {
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 2px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        width: 60px;
        height: 60px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .gallery-zoom {
        transform: scale(1.1);
    }

    .gallery-info {
        padding: 20px;
    }

    .gallery-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
        line-height: 1.4;
    }

    .gallery-description {
        color: #6c757d;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 10px;
    }

    .gallery-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }

    .gallery-category {
        background: #e9ecef;
        color: #495057;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .gallery-category:hover {
        background: #28a745;
        color: white;
    }

    .gallery-date {
        color: #6c757d;
        font-size: 0.8rem;
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 50px;
    }

    .section-badge {
        background: linear-gradient(135deg, #28a745, rgb(23, 113, 44));
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        display: inline-block;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .no-images {
        background: white;
        border-radius: 15px;
        padding: 60px 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .pagination .page-link {
        border-radius: 25px;
        margin: 0 3px;
        border: none;
        padding: 8px 16px;
        color: #28a745;
    }

    .pagination .page-item.active .page-link {
        background: #28a745;
        border-color: #28a745;
    }

    /* Modal Styles */
    .image-modal .modal-dialog {
        max-width: 90vw;
        max-height: 90vh;
    }

    .image-modal .modal-content {
        background: rgba(0, 0, 0, 0.95);
        border: none;
        border-radius: 15px;
        backdrop-filter: blur(20px);
    }

    .image-modal .modal-header {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
    }

    .image-modal .modal-title {
        color: white;
        font-weight: 600;
    }

    .image-modal .btn-close {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        width: 40px;
        height: 40px;
        opacity: 1;
    }

    .image-modal .modal-body {
        padding: 0;
        text-align: center;
    }

    .image-modal .modal-image {
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
        border-radius: 10px;
    }

    .image-modal .modal-footer {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding: 20px;
        justify-content: center;
    }

    .image-modal .modal-description {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 10px;
    }

    .image-modal .modal-category {
        background: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.8rem;
        display: inline-block;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .gallery-hero h1 {
            font-size: 2.5rem;
        }

        .gallery-hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .gallery-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .filter-section {
            padding: 20px;
        }

        .category-buttons {
            justify-content: center;
        }

        .gallery-image {
            height: 200px;
        }

        .gallery-info {
            padding: 15px;
        }

        .image-modal .modal-dialog {
            max-width: 95vw;
        }

        .image-modal .modal-image {
            max-height: 60vh;
        }
    }
</style>

<!-- Gallery Hero -->
<section class="gallery-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="hero-badge">
                    <i class="fas fa-camera me-2"></i>Photo Gallery
                </div>
                <h1 style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);">Visual Stories</h1>
                <p style="color: #ffffff; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);">Discover our environmental conservation journey through captivating images of our work, impact, and community across Tanzania.</p>
            </div>
        </div>

        <div class="gallery-stats">
            <div class="row">
                <div class="col-md-4 col-6">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $gallery_stats['total_images']; ?></span>
                        <span class="stat-label">Total Images</span>
                    </div>
                </div>
                <div class="col-md-4 col-6">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $gallery_stats['categories']; ?></span>
                        <span class="stat-label">Categories</span>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $gallery_stats['featured']; ?></span>
                        <span class="stat-label">Featured Photos</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Images -->
<?php if ($featured_images && !$category): ?>
    <section class="featured-section">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-12">
                    <div class="section-badge">
                        <i class="fas fa-star me-2"></i>Featured Gallery
                    </div>
                    <h2 class="section-title">Highlight Moments</h2>
                    <p class="section-subtitle">Showcasing our most impactful environmental conservation moments and community achievements</p>
                </div>
            </div>

            <div class="row">
                <?php foreach ($featured_images as $index => $featured): ?>
                    <div class="col-lg-<?php echo ($index < 2) ? '6' : '4'; ?> col-md-6 mb-4">
                        <div class="featured-card"
                            data-bs-toggle="modal"
                            data-bs-target="#imageModal"
                            data-image="<?php echo UPLOADS_URL . '/gallery/' . $featured['image_path']; ?>"
                            data-title="<?php echo htmlspecialchars($featured['title']); ?>"
                            data-description="<?php echo htmlspecialchars($featured['description'] ?? ''); ?>"
                            data-category="<?php echo htmlspecialchars($featured['category'] ?? ''); ?>">
                            <div class="featured-image">
                                <img src="<?php echo SITE_URL; ?>/<?php echo $featured['image_path']; ?>"
                                    alt="<?php echo htmlspecialchars($featured['alt_text'] ?: $featured['title']); ?>">
                                <div class="featured-overlay">
                                    <div class="featured-content">
                                        <div class="featured-title"><?php echo htmlspecialchars($featured['title']); ?></div>
                                        <?php if ($featured['category']): ?>
                                            <div class="featured-category"><?php echo htmlspecialchars($featured['category']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Main Gallery -->
<section class="gallery-content">
    <div class="container">
        <!-- Filters -->
        <div class="filter-section">
            <h3 class="filter-title">
                <?php if ($category): ?>
                    <i class="fas fa-folder me-2"></i>Category: <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $category))); ?>
                <?php else: ?>
                    <i class="fas fa-th me-2"></i>Browse All Photos
                <?php endif; ?>
            </h3>

            <div class="category-buttons">
                <a href="<?php echo SITE_URL; ?>/gallery.php"
                    class="category-btn <?php echo !$category ? 'active' : ''; ?>">
                    <i class="fas fa-th me-1"></i>All Photos
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?php echo SITE_URL; ?>/gallery.php?category=<?php echo urlencode($cat['category']); ?>"
                        class="category-btn <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                        <i class="fas fa-folder me-1"></i>
                        <?php echo htmlspecialchars(ucwords($cat['category'])); ?>
                        <span class="badge bg-secondary ms-1"><?php echo $cat['image_count']; ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gallery Grid -->
        <?php if ($gallery_images): ?>
            <div class="gallery-grid">
                <?php foreach ($gallery_images as $image): ?>
                    <div class="gallery-item"
                        data-bs-toggle="modal"
                        data-bs-target="#imageModal"
                        data-image="<?php echo SITE_URL; ?>/<?php echo $featured['image_path']; ?>"
                        data-title="<?php echo htmlspecialchars($image['title']); ?>"
                        data-description="<?php echo htmlspecialchars($image['description'] ?? ''); ?>"
                        data-category="<?php echo htmlspecialchars($image['category'] ?? ''); ?>">

                        <div class="gallery-image">
                            <img src="<?php echo SITE_URL; ?>/<?php echo $featured['image_path']; ?>"
                                alt="<?php echo htmlspecialchars($image['alt_text'] ?: $image['title']); ?>"
                                loading="lazy">
                            <div class="gallery-overlay">
                                <div class="gallery-zoom">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        </div>

                        <div class="gallery-info">
                            <h5 class="gallery-title"><?php echo htmlspecialchars($image['title']); ?></h5>
                            <?php if ($image['description']): ?>
                                <p class="gallery-description">
                                    <?php echo htmlspecialchars(truncate_text($image['description'], 100)); ?>
                                </p>
                            <?php endif; ?>

                            <div class="gallery-meta">
                                <?php if ($image['category']): ?>
                                    <a href="<?php echo SITE_URL; ?>/gallery.php?category=<?php echo urlencode($image['category']); ?>"
                                        class="gallery-category">
                                        <?php echo htmlspecialchars($image['category']); ?>
                                    </a>
                                <?php endif; ?>
                                <span class="gallery-date">
                                    <?php echo format_date($image['created_at'], 'M j, Y'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Gallery pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['current_page'] > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($pagination['current_page'] - 1); ?><?php echo ($category ? '&category=' . urlencode($category) : ''); ?>">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                            <li class="page-item <?php echo ($i === $pagination['current_page']) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo ($category ? '&category=' . urlencode($category) : ''); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo ($pagination['current_page'] + 1); ?><?php echo ($category ? '&category=' . urlencode($category) : ''); ?>">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-images">
                <i class="fas fa-images fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">
                    <?php echo $category ? 'No images in this category' : 'No images available'; ?>
                </h4>
                <p class="text-muted mb-4">
                    <?php if ($category): ?>
                        Try browsing other categories or view all photos.
                    <?php else: ?>
                        Check back later for updates on our conservation activities.
                    <?php endif; ?>
                </p>
                <?php if ($category): ?>
                    <a href="<?php echo SITE_URL; ?>/gallery.php" class="btn btn-primary">
                        <i class="fas fa-th me-2"></i>View All Photos
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Image Modal -->
<div class="modal fade image-modal" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalTitle">Image Title</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <img src="" alt="" class="modal-image" id="modalImage">
            </div>
            <div class="modal-footer">
                <div class="text-center">
                    <p class="modal-description" id="imageModalDescription"></p>
                    <span class="modal-category" id="imageModalCategory"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('imageModalTitle');
        const modalDescription = document.getElementById('imageModalDescription');
        const modalCategory = document.getElementById('imageModalCategory');

        // Handle gallery item clicks
        document.querySelectorAll('[data-bs-toggle="modal"]').forEach(item => {
            item.addEventListener('click', function() {
                const imageSrc = this.getAttribute('data-image');
                const title = this.getAttribute('data-title');
                const description = this.getAttribute('data-description');
                const category = this.getAttribute('data-category');

                modalImage.src = imageSrc;
                modalImage.alt = title;
                modalTitle.textContent = title;
                modalDescription.textContent = description || 'No description available';

                if (category) {
                    modalCategory.textContent = category;
                    modalCategory.style.display = 'inline-block';
                } else {
                    modalCategory.style.display = 'none';
                }
            });
        });

        // Keyboard navigation for modal
        imageModal.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(imageModal);
                modal.hide();
            }
        });

        // Lazy loading for images
        const images = document.querySelectorAll('.gallery-item img');

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.add('loaded');
                        observer.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        }
    });
</script>

<?php include 'includes/footer.php'; ?>