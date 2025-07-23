<?php

/**
 * ECCT Website Homepage
 * Environmental Conservation Community of Tanzania
 */

define('ECCT_ROOT', dirname(__FILE__, 2));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Get database instance
$db = new Database();

// Helper function to get site settings
function get_setting($key, $default = '')
{
    global $db;
    $setting = $db->selectOne('site_settings', ['setting_key' => $key]);
    return $setting ? $setting['setting_value'] : $default;
}

// Helper function to get featured content
function get_featured_content($table, $limit = 3)
{
    global $db;
    return $db->select($table, ['is_published' => 1], [
        'order_by' => 'created_at DESC',
        'limit' => $limit
    ]);
}

// Helper function to get recent content
function get_recent_content($table, $limit = 6)
{
    global $db;
    return $db->select($table, [], [
        'order_by' => 'created_at DESC',
        'limit' => $limit
    ]);
}

// Page variables
$page_title = get_setting('site_name', SITE_NAME);
$meta_description = get_setting('site_description', 'ECCT empowers local communities to create cleaner, greener, resilient and sustainable environments');
$page_class = 'homepage';

// Get hero section data
$hero_title = get_setting('hero_title', 'Together We Can Save Our Environment');
$hero_subtitle = get_setting('hero_subtitle', 'Join ECCT in creating a sustainable future for Tanzania through community-driven environmental conservation.');
$hero_background = get_setting('hero_background', 'assets/images/hero-bg.jpg');

// Get statistics
$successful_campaigns = get_setting('successful_campaigns', '50');
$volunteers_count = get_setting('volunteers_count', '1200');
$communities_served = get_setting('communities_served', '75');

// Get featured content
$featured_news = get_featured_content('news', 3);
$featured_campaigns = get_featured_content('campaigns', 3);
$recent_gallery = get_recent_content('gallery', 6);

// Include header
include ECCT_ROOT . '/includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Video Background -->
    <div class="hero-video-background">
        <video autoplay muted loop playsinline preload="auto" class="hero-video">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.mp4" type="video/mp4">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.webm" type="video/webm">
            <!-- Fallback image if video doesn't load -->
        </video>

        <!-- Video Controls -->
        <button class="video-toggle-btn" id="videoToggle" title="Pause video">
            <i class="fas fa-pause"></i>
        </button>
    </div>

    <!-- Hero Content Overlay -->
    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8 col-xl-7">
                    <div class="hero-text text-white">
                        <h1 class="display-3 fw-bold mb-4 animate__animated animate__fadeInUp">
                            <?php echo htmlspecialchars($hero_title); ?>
                        </h1>
                        <p class="lead mb-5 animate__animated animate__fadeInUp animate__delay-1s">
                            <?php echo htmlspecialchars($hero_subtitle); ?>
                        </p>

                        <!-- Call to Action Buttons -->
                        <div class="hero-actions animate__animated animate__fadeInUp animate__delay-2s">
                            <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-success btn-lg me-3 mb-3">
                                <i class="fas fa-hands-helping me-2"></i>
                                Join Us Today
                            </a>
                            <a href="<?php echo SITE_URL; ?>/about" class="btn btn-outline-light btn-lg mb-3">
                                <i class="fas fa-leaf me-2"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="col-lg-4 col-xl-5">
                    <div class="hero-stats animate__animated animate__fadeInRight animate__delay-1s">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="stat-card bg-white bg-opacity-90 rounded-3 p-4 text-center">
                                    <div class="stat-icon text-success mb-2">
                                        <i class="fas fa-bullhorn fa-2x"></i>
                                    </div>
                                    <h3 class="stat-number text-primary fw-bold mb-1"><?php echo htmlspecialchars($successful_campaigns); ?>+</h3>
                                    <p class="stat-label text-muted mb-0">Successful Campaigns</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card bg-white bg-opacity-90 rounded-3 p-3 text-center">
                                    <div class="stat-icon text-success mb-2">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>
                                    <h4 class="stat-number text-primary fw-bold mb-1"><?php echo htmlspecialchars($volunteers_count); ?>+</h4>
                                    <p class="stat-label text-muted mb-0 small">Volunteers</p>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-card bg-white bg-opacity-90 rounded-3 p-3 text-center">
                                    <div class="stat-icon text-success mb-2">
                                        <i class="fas fa-map-marker-alt fa-lg"></i>
                                    </div>
                                    <h4 class="stat-number text-primary fw-bold mb-1"><?php echo htmlspecialchars($communities_served); ?>+</h4>
                                    <p class="stat-label text-muted mb-0 small">Communities</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator position-absolute bottom-0 start-50 translate-middle-x mb-4">
            <div class="scroll-down text-white text-center">
                <p class="mb-2 small">Scroll to explore</p>
                <i class="fas fa-chevron-down animate__animated animate__bounce animate__infinite"></i>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="about-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="about-content">
                    <h2 class="section-title mb-4">About ECCT</h2>
                    <p class="lead text-muted mb-4">
                        Environmental Conservation Community of Tanzania (ECCT) is dedicated to empowering local communities
                        to create cleaner, greener, resilient and sustainable environments.
                    </p>
                    <p class="mb-4">
                        We tackle global environmental challenges through community-driven initiatives, focusing on plastic
                        waste reduction, climate change mitigation, and biodiversity conservation in both marine and terrestrial environments.
                    </p>

                    <!-- Mission Points -->
                    <div class="mission-points">
                        <div class="mission-point d-flex align-items-start mb-3">
                            <div class="mission-icon text-success me-3">
                                <i class="fas fa-recycle fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Plastic Waste Reduction</h6>
                                <p class="text-muted mb-0 small">Innovative solutions to tackle plastic pollution in our communities.</p>
                            </div>
                        </div>
                        <div class="mission-point d-flex align-items-start mb-3">
                            <div class="mission-icon text-success me-3">
                                <i class="fas fa-seedling fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Climate Action</h6>
                                <p class="text-muted mb-0 small">Community-based climate change mitigation and adaptation strategies.</p>
                            </div>
                        </div>
                        <div class="mission-point d-flex align-items-start">
                            <div class="mission-icon text-success me-3">
                                <i class="fas fa-fish fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="mb-1">Biodiversity Conservation</h6>
                                <p class="text-muted mb-0 small">Protecting marine and terrestrial ecosystems for future generations.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo SITE_URL; ?>/about" class="btn btn-success">
                            <i class="fas fa-arrow-right me-2"></i>
                            Learn More About Us
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="about-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/about-ecct.jpg"
                        alt="ECCT Team"
                        class="img-fluid rounded-3 shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured News Section -->
<?php if (!empty($featured_news)): ?>
    <section class="news-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-header text-center mb-5">
                        <h2 class="section-title">Latest News & Updates</h2>
                        <p class="section-subtitle text-muted">Stay informed about our latest environmental initiatives and community impact</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($featured_news as $news): ?>
                    <div class="col-lg-4 col-md-6">
                        <article class="news-card card h-100 border-0 shadow-sm">
                            <?php if (!empty($news['featured_image'])): ?>
                                <div class="news-image">
                                    <img src="<?php echo UPLOADS_URL; ?>/<?php echo $news['featured_image']; ?>"
                                        alt="<?php echo htmlspecialchars($news['title']); ?>"
                                        class="card-img-top">
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <div class="news-meta mb-2">
                                    <span class="badge bg-success"><?php echo date('M j, Y', strtotime($news['created_at'])); ?></span>
                                </div>

                                <h5 class="news-title card-title">
                                    <a href="<?php echo SITE_URL; ?>/news/<?php echo $news['slug']; ?>"
                                        class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h5>

                                <p class="news-excerpt text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 120)) . '...'; ?>
                                </p>

                                <div class="news-actions mt-auto">
                                    <a href="<?php echo SITE_URL; ?>/news/<?php echo $news['slug']; ?>"
                                        class="btn btn-outline-success btn-sm">
                                        Read More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?php echo SITE_URL; ?>/news" class="btn btn-success btn-lg">
                    <i class="fas fa-newspaper me-2"></i>
                    View All News
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Featured Campaigns Section -->
<?php if (!empty($featured_campaigns)): ?>
    <section class="campaigns-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-header text-center mb-5">
                        <h2 class="section-title">Active Campaigns</h2>
                        <p class="section-subtitle text-muted">Join our ongoing environmental conservation campaigns</p>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <?php foreach ($featured_campaigns as $campaign): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="campaign-card card h-100 border-0 shadow-sm">
                            <?php if (!empty($campaign['featured_image'])): ?>
                                <div class="campaign-image position-relative">
                                    <img src="<?php echo UPLOADS_URL; ?>/<?php echo $campaign['featured_image']; ?>"
                                        alt="<?php echo htmlspecialchars($campaign['title']); ?>"
                                        class="card-img-top">
                                    <div class="campaign-status position-absolute top-0 end-0 m-3">
                                        <span class="badge bg-<?php echo $campaign['status'] === 'active' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($campaign['status'] ?? 'active'); ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <h5 class="campaign-title card-title">
                                    <a href="<?php echo SITE_URL; ?>/campaigns/<?php echo $campaign['slug']; ?>"
                                        class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($campaign['title']); ?>
                                    </a>
                                </h5>

                                <p class="campaign-excerpt text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(substr(strip_tags($campaign['description']), 0, 120)) . '...'; ?>
                                </p>

                                <!-- Campaign Progress (if applicable) -->
                                <?php if (isset($campaign['goal_amount']) && $campaign['goal_amount'] > 0): ?>
                                    <div class="campaign-progress mb-3">
                                        <?php
                                        $raised = $campaign['raised_amount'] ?? 0;
                                        $goal = $campaign['goal_amount'];
                                        $percentage = ($raised / $goal) * 100;
                                        ?>
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Raised: $<?php echo number_format($raised); ?></small>
                                            <small class="text-muted">Goal: $<?php echo number_format($goal); ?></small>
                                        </div>
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar bg-success"
                                                style="width: <?php echo min(100, $percentage); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="campaign-actions mt-auto">
                                    <a href="<?php echo SITE_URL; ?>/campaigns/<?php echo $campaign['slug']; ?>"
                                        class="btn btn-success btn-sm">
                                        Join Campaign <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?php echo SITE_URL; ?>/campaigns" class="btn btn-success btn-lg">
                    <i class="fas fa-bullhorn me-2"></i>
                    View All Campaigns
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Gallery Section -->
<?php if (!empty($recent_gallery)): ?>
    <section class="gallery-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section-header text-center mb-5">
                        <h2 class="section-title">Our Impact in Action</h2>
                        <p class="section-subtitle text-muted">See our environmental conservation work through images</p>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <?php foreach (array_slice($recent_gallery, 0, 6) as $index => $image): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="gallery-item">
                            <a href="<?php echo UPLOADS_URL; ?>/<?php echo $image['image_path']; ?>"
                                data-bs-toggle="modal"
                                data-bs-target="#galleryModal"
                                data-image="<?php echo UPLOADS_URL; ?>/<?php echo $image['image_path']; ?>"
                                data-title="<?php echo htmlspecialchars($image['title'] ?? ''); ?>">
                                <img src="<?php echo UPLOADS_URL; ?>/<?php echo $image['image_path']; ?>"
                                    alt="<?php echo htmlspecialchars($image['title'] ?? 'Gallery Image'); ?>"
                                    class="img-fluid rounded-3 shadow-sm gallery-img">
                                <div class="gallery-overlay">
                                    <i class="fas fa-search-plus text-white fa-2x"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?php echo SITE_URL; ?>/gallery" class="btn btn-success btn-lg">
                    <i class="fas fa-images me-2"></i>
                    View Full Gallery
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="cta-section py-5 bg-success text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="cta-title mb-3">Ready to Make a Difference?</h2>
                <p class="cta-subtitle mb-4 mb-lg-0">
                    Join thousands of volunteers working together to create a sustainable future for Tanzania.
                    Every action counts, and your contribution can help preserve our environment for future generations.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="cta-actions">
                    <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-light btn-lg me-2 mb-2">
                        <i class="fas fa-hands-helping me-2"></i>
                        Become a Volunteer
                    </a>
                    <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-outline-light btn-lg mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalTitle">Gallery Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="galleryModalImage" src="" alt="" class="img-fluid">
            </div>
        </div>
    </div>
</div>

<style>
    /* Hero Section Styles */
    .hero-section {
        min-height: 100vh;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.8), rgba(25, 135, 84, 0.9));
    }

    .hero-video-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: -1;
        overflow: hidden;
    }

    .hero-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.7;
    }

    .video-toggle-btn {
        position: absolute;
        bottom: 20px;
        right: 20px;
        background: rgba(0, 0, 0, 0.5);
        border: none;
        color: white;
        padding: 10px 12px;
        border-radius: 50%;
        cursor: pointer;
        z-index: 10;
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .stat-card {
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-number {
        font-size: 2rem;
    }

    /* Gallery Styles */
    .gallery-item {
        position: relative;
        overflow: hidden;
        border-radius: 0.5rem;
    }

    .gallery-img {
        transition: transform 0.3s ease;
        height: 250px;
        object-fit: cover;
        width: 100%;
    }

    .gallery-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(40, 167, 69, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-item:hover .gallery-overlay {
        opacity: 1;
    }

    .gallery-item:hover .gallery-img {
        transform: scale(1.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-video {
            display: none !important;
        }

        .hero-video-background {
            background-image: url('<?php echo UPLOADS_URL; ?>/<?php echo $hero_background; ?>');
            background-size: cover;
            background-position: center;
        }

        .display-3 {
            font-size: 2.5rem;
        }

        .hero-stats {
            margin-top: 2rem;
        }
    }

    /* Slow connection detection */
    @media (prefers-reduced-data: reduce) {
        .hero-video {
            display: none !important;
        }

        .hero-video-background {
            background-image: url('<?php echo UPLOADS_URL; ?>/<?php echo $hero_background; ?>');
            background-size: cover;
            background-position: center;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const video = document.querySelector('.hero-video');
        const videoBackground = document.querySelector('.hero-video-background');
        const toggleBtn = document.getElementById('videoToggle');
        const toggleIcon = toggleBtn?.querySelector('i');

        // Check if user prefers reduced data or is on slow connection
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const slowConnection = connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g');
        const prefersReducedData = window.matchMedia('(prefers-reduced-data: reduce)').matches;

        // Only load video if conditions are good
        if (!slowConnection && !prefersReducedData && window.innerWidth > 768) {
            // Video is already in HTML, just add event listeners
            if (video) {
                video.addEventListener('loadeddata', function() {
                    videoBackground.classList.add('loaded');
                });

                video.addEventListener('error', function() {
                    console.log('Video failed to load, falling back to image');
                    video.style.display = 'none';
                });
            }

            // Video controls
            if (toggleBtn && video) {
                toggleBtn.addEventListener('click', function() {
                    if (video.paused) {
                        video.play();
                        toggleIcon.className = 'fas fa-pause';
                        toggleBtn.title = 'Pause video';
                    } else {
                        video.pause();
                        toggleIcon.className = 'fas fa-play';
                        toggleBtn.title = 'Play video';
                    }
                });
            }
        } else {
            // Remove video for slow connections or mobile
            if (video) {
                video.remove();
            }
            if (toggleBtn) {
                toggleBtn.remove();
            }
        }

        // Smooth scroll for scroll indicator
        const scrollIndicator = document.querySelector('.scroll-indicator');
        if (scrollIndicator) {
            scrollIndicator.addEventListener('click', function() {
                window.scrollTo({
                    top: window.innerHeight,
                    behavior: 'smooth'
                });
            });
        }

        // Gallery modal
        const galleryModal = document.getElementById('galleryModal');
        if (galleryModal) {
            galleryModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                const imageTitle = button.getAttribute('data-title');

                const modalImage = galleryModal.querySelector('#galleryModalImage');
                const modalTitle = galleryModal.querySelector('#galleryModalTitle');

                modalImage.src = imageSrc;
                modalImage.alt = imageTitle;
                modalTitle.textContent = imageTitle || 'Gallery Image';
            });
        }

        // Add animation classes when elements come into view
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                }
            });
        }, observerOptions);

        // Observe elements for animation
        document.querySelectorAll('.section-title, .news-card, .campaign-card, .gallery-item').forEach(function(el) {
            observer.observe(el);
        });
    });
</script>

<?php
// Include footer
include ECCT_ROOT . '/includes/footer.php';
?>