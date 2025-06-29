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

<!-- Page Header -->
<section class="page-header bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Photo Gallery</h1>
                <p class="lead mb-0">
                    Discover our environmental conservation journey through captivating images of our work across Tanzania
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Gallery</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Stats -->
<section class="gallery-stats py-5 bg-light">
    <div class="container">
        <div class="row text-center">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-primary mb-3">
                        <i class="fas fa-images fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-primary"><?php echo $gallery_stats['total_images']; ?></h3>
                    <p class="text-muted mb-0">Total Images</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-success mb-3">
                        <i class="fas fa-folder fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-success"><?php echo $gallery_stats['categories']; ?></h3>
                    <p class="text-muted mb-0">Categories</p>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="stat-item">
                    <div class="stat-icon text-warning mb-3">
                        <i class="fas fa-star fa-3x"></i>
                    </div>
                    <h3 class="fw-bold text-warning"><?php echo $gallery_stats['featured']; ?></h3>
                    <p class="text-muted mb-0">Featured Photos</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Images -->
<?php if ($featured_images && !$category): ?>
    <section class="featured-gallery py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 mb-4">
                    <h2 class="h4 fw-bold">Featured Photos</h2>
                    <p class="text-muted">Highlights from our environmental conservation activities</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featured_images as $index => $featured): ?>
                    <div class="col-lg-<?php echo ($index < 2) ? '6' : '3'; ?> col-md-6 mb-4">
                        <div class="featured-image gallery-item"
                            data-bs-toggle="modal"
                            data-bs-target="#imageModal"
                            data-image="<?php echo UPLOADS_URL . '/gallery/' . $featured['image_path']; ?>"
                            data-title="<?php echo htmlspecialchars($featured['title']); ?>"
                            data-description="<?php echo htmlspecialchars($featured['description'] ?? ''); ?>"
                            data-category="<?php echo htmlspecialchars($featured['category'] ?? ''); ?>">
                            <div class="image-container position-relative overflow-hidden rounded shadow">
                                <img src="<?php echo UPLOADS_URL . '/gallery/' . $featured['image_path']; ?>"
                                    alt="<?php echo htmlspecialchars($featured['alt_text'] ?: $featured['title']); ?>"
                                    class="img-fluid w-100"
                                    style="height: <?php echo ($index < 2) ? '300px' : '200px'; ?>; object-fit: cover;">
                                <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <div class="overlay-content text-center text-white">
                                        <i class="fas fa-search-plus fa-2x mb-2"></i>
                                        <h6 class="mb-1"><?php echo htmlspecialchars($featured['title']); ?></h6>
                                        <?php if ($featured['category']): ?>
                                            <small class="badge bg-warning text-dark"><?php echo htmlspecialchars($featured['category']); ?></small>
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

<!-- Gallery Filters -->
<section class="gallery-filters py-5 <?php echo (!$featured_images || $category) ? 'bg-light' : ''; ?>">
    <div class="container">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h3 class="h4 fw-bold mb-0">
                    <?php if ($category): ?>
                        Category: <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $category))); ?>
                    <?php else: ?>
                        All Photos
                    <?php endif; ?>
                </h3>
            </div>
            <div class="col-md-6">
                <div class="d-flex justify-content-md-end mt-3 mt-md-0">
                    <div class="dropdown">
                        <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-filter me-2"></i>
                            <?php echo $category ? ucwords(str_replace('-', ' ', $category)) : 'All Categories'; ?>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item <?php echo !$category ? 'active' : ''; ?>"
                                    href="<?php echo SITE_URL; ?>/gallery.php">
                                    <i class="fas fa-th me-2"></i>All Categories
                                </a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <?php foreach ($categories as $cat): ?>
                                <li>
                                    <a class="dropdown-item <?php echo $category === $cat['category'] ? 'active' : ''; ?>"
                                        href="<?php echo SITE_URL; ?>/gallery.php?category=<?php echo urlencode($cat['category']); ?>">
                                        <i class="fas fa-folder me-2"></i>
                                        <?php echo htmlspecialchars(ucwords($cat['category'])); ?>
                                        <span class="badge bg-light text-dark ms-1"><?php echo $cat['image_count']; ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($category): ?>
            <div class="active-filters">
                <span class="badge bg-primary me-2">
                    Category: <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $category))); ?>
                    <a href="<?php echo SITE_URL; ?>/gallery.php" class="text-white text-decoration-none ms-1">×</a>
                </span>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Gallery Grid -->
<section class="gallery-grid py-5">
    <div class="container">
        <?php if ($gallery_images): ?>
            <div class="row">
                <?php foreach ($gallery_images as $image): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="gallery-item"
                            data-bs-toggle="modal"
                            data-bs-target="#imageModal"
                            data-image="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                            data-title="<?php echo htmlspecialchars($image['title']); ?>"
                            data-description="<?php echo htmlspecialchars($image['description'] ?? ''); ?>"
                            data-category="<?php echo htmlspecialchars($image['category'] ?? ''); ?>"
                            data-date="<?php echo format_date($image['created_at']); ?>">
                            <div class="image-container position-relative overflow-hidden rounded shadow-sm">
                                <img src="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                    alt="<?php echo htmlspecialchars($image['alt_text'] ?: $image['title']); ?>"
                                    class="img-fluid w-100"
                                    style="height: 250px; object-fit: cover;">
                                <div class="image-overlay position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
                                    <div class="overlay-content text-center text-white">
                                        <i class="fas fa-search-plus fa-lg mb-2"></i>
                                        <h6 class="mb-1"><?php echo htmlspecialchars(truncate_text($image['title'], 30)); ?></h6>
                                        <?php if ($image['category']): ?>
                                            <small class="badge bg-primary"><?php echo htmlspecialchars($image['category']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($image['is_featured']): ?>
                                    <div class="position-absolute top-0 end-0 m-2">
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-star"></i>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($pagination['total_pages'] > 1): ?>
                <nav aria-label="Gallery pagination" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($pagination['has_prev']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, $pagination['current_page'] - 2);
                        $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

                        if ($start_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=1<?php echo $category ? '&category=' . urlencode($category) : ''; ?>">1</a>
                            </li>
                            <?php if ($start_page > 2): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                            <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($end_page < $pagination['total_pages']): ?>
                            <?php if ($end_page < $pagination['total_pages'] - 1): ?>
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            <?php endif; ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['total_pages']; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                                    <?php echo $pagination['total_pages']; ?>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($pagination['has_next']): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <!-- No Images -->
            <div class="no-images text-center py-5">
                <i class="fas fa-images fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">
                    <?php echo $category ? 'No images found in this category' : 'No images available'; ?>
                </h4>
                <p class="text-muted mb-4">
                    <?php if ($category): ?>
                        Try browsing other categories or view all images.
                    <?php else: ?>
                        Check back later for photos from our environmental conservation activities.
                    <?php endif; ?>
                </p>
                <?php if ($category): ?>
                    <a href="<?php echo SITE_URL; ?>/gallery.php" class="btn btn-primary">
                        <i class="fas fa-th me-2"></i>View All Images
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Want to Be Part of Our Story?</h3>
                <p class="mb-0 lead">
                    Join our environmental conservation efforts and help us create more positive impact stories to share.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-hands-helping me-2"></i>Volunteer
                </a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-camera me-2"></i>Share Photos
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="imageModalLabel">Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <img src="" alt="" class="img-fluid w-100" id="modalImage">
                <div class="p-4">
                    <h6 id="modalTitle" class="fw-bold mb-2"></h6>
                    <p id="modalDescription" class="text-muted mb-2"></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span id="modalCategory" class="badge bg-primary"></span>
                        </div>
                        <small id="modalDate" class="text-muted"></small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <div class="d-flex justify-content-between w-100">
                    <div class="social-share">
                        <span class="text-muted me-3">Share:</span>
                        <a href="#" id="shareWhatsApp" target="_blank" class="btn btn-outline-success btn-sm me-2">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="#" id="shareFacebook" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" id="shareTwitter" target="_blank" class="btn btn-outline-info btn-sm">
                            <i class="fab fa-twitter"></i>
                        </a>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .gallery-item {
        cursor: pointer;
        transition: transform 0.3s ease;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
    }

    .image-overlay {
        background: rgba(0, 0, 0, 0.7);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-item:hover .image-overlay {
        opacity: 1;
    }

    .featured-image .image-overlay {
        background: linear-gradient(45deg, rgba(40, 167, 69, 0.8), rgba(40, 167, 69, 0.6));
    }

    .image-container {
        overflow: hidden;
    }

    .image-container img {
        transition: transform 0.3s ease;
    }

    .gallery-item:hover .image-container img {
        transform: scale(1.05);
    }

    .modal-body img {
        max-height: 70vh;
        object-fit: contain;
    }

    @media (max-width: 768px) {
        .gallery-item {
            margin-bottom: 1rem;
        }

        .featured-image {
            margin-bottom: 1.5rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const imageModal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalTitle = document.getElementById('modalTitle');
        const modalDescription = document.getElementById('modalDescription');
        const modalCategory = document.getElementById('modalCategory');
        const modalDate = document.getElementById('modalDate');
        const shareWhatsApp = document.getElementById('shareWhatsApp');
        const shareFacebook = document.getElementById('shareFacebook');
        const shareTwitter = document.getElementById('shareTwitter');

        imageModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const imageSrc = button.getAttribute('data-image');
            const title = button.getAttribute('data-title');
            const description = button.getAttribute('data-description');
            const category = button.getAttribute('data-category');
            const date = button.getAttribute('data-date');

            modalImage.src = imageSrc;
            modalImage.alt = title;
            modalTitle.textContent = title;
            modalDescription.textContent = description || '';
            modalCategory.textContent = category || '';
            modalDate.textContent = date || '';

            // Hide elements if no data
            modalDescription.style.display = description ? 'block' : 'none';
            modalCategory.style.display = category ? 'inline' : 'none';
            modalDate.style.display = date ? 'block' : 'none';

            // Update share links
            const currentUrl = window.location.href;
            const shareText = `Check out this photo from ECCT: ${title}`;

            shareWhatsApp.href = `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + currentUrl)}`;
            shareFacebook.href = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`;
            shareTwitter.href = `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(currentUrl)}`;
        });

        // Keyboard navigation for modal
        imageModal.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(imageModal);
                modal.hide();
            }
        });

        // Lazy loading for images (optional enhancement)
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