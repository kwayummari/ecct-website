<?php

/**
 * ECCT Website Homepage
 * Environmental Conservation Community of Tanzania
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Page variables
$page_title = $db->getSetting('site_name', SITE_NAME);
$meta_description = $db->getSetting('site_description', 'ECCT empowers local communities to create cleaner, greener, resilient and sustainable environments');
$page_class = 'homepage';

// Get hero section data
$hero_title = $db->getSetting('hero_title', 'Together We Can Save Our Environment');
$hero_subtitle = $db->getSetting('hero_subtitle', 'Join ECCT in creating a sustainable future for Tanzania through community-driven environmental conservation.');
$hero_background = $db->getSetting('hero_background', 'assets/images/hero-bg.jpg');

// Get statistics
$successful_campaigns = $db->getSetting('successful_campaigns', '10');
$volunteers_count = $db->getSetting('volunteers_count', '500');
$communities_served = $db->getSetting('communities_served', '25');

// Get featured content
$featured_news = get_featured_content('news', 3);
$featured_campaigns = get_featured_content('campaigns', 3);
$recent_gallery = get_recent_content('gallery', 6);

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Video Background -->
    <div class="hero-video-background">
        <video autoplay muted loop playsinline preload="auto" class="hero-video">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.mp4" type="video/mp4">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.webm" type="video/webm">
            <!-- Fallback image if video doesn't load -->
            <img src="<?php echo SITE_URL . '/' . $hero_background; ?>" alt="ECCT Environmental Conservation" class="hero-fallback-image">
        </video>
        <div class="hero-overlay"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6">
                <div class="hero-content text-white">
                    <h1 class="display-4 fw-bold mb-4 animate-fade-in">
                        <?php echo htmlspecialchars($hero_title); ?>
                    </h1>
                    <p class="lead mb-4 animate-fade-in-delay">
                        <?php echo htmlspecialchars($hero_subtitle); ?>
                    </p>
                    <div class="hero-buttons animate-fade-in-delay-2">
                        <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-hands-helping me-2"></i>
                            Join as Volunteer
                        </a>
                        <a href="<?php echo SITE_URL; ?>/about" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle me-2"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards (optional - you can keep or remove) -->
            <div class="col-lg-6">
                <div class="hero-stats bg-white bg-opacity-10 p-4 rounded-3 backdrop-blur">
                    <div class="row text-center text-white">
                        <div class="col-4">
                            <div class="stat-item">
                                <h3 class="display-6 fw-bold mb-1"><?php echo $successful_campaigns; ?>+</h3>
                                <p class="small mb-0">Successful Campaigns</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h3 class="display-6 fw-bold mb-1"><?php echo $volunteers_count; ?>+</h3>
                                <p class="small mb-0">Volunteers</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h3 class="display-6 fw-bold mb-1"><?php echo $communities_served; ?>+</h3>
                                <p class="small mb-0">Communities</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator position-absolute bottom-0 start-50 translate-middle-x pb-4">
            <div class="scroll-arrow text-white text-center">
                <i class="fas fa-chevron-down fa-2x opacity-75"></i>
                <p class="small mt-2 mb-0">Scroll Down</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="mission-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="mission-content">
                    <h2 class="section-title mb-4">Our Mission</h2>
                    <p class="text-muted mb-4">
                        To empower local communities to create cleaner, greener, resilient and sustainable environments
                        through tackling global environmental pollution from plastic waste, climate change and loss of
                        biodiversity in both marine and terrestrial environments.
                    </p>
                    <div class="mission-points">
                        <div class="point d-flex align-items-start mb-3">
                            <i class="fas fa-leaf text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Environmental Conservation</h6>
                                <p class="text-muted small mb-0">Protecting and preserving Tanzania's natural ecosystems</p>
                            </div>
                        </div>
                        <div class="point d-flex align-items-start mb-3">
                            <i class="fas fa-users text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Community Empowerment</h6>
                                <p class="text-muted small mb-0">Building local capacity for sustainable development</p>
                            </div>
                        </div>
                        <div class="point d-flex align-items-start">
                            <i class="fas fa-recycle text-primary me-3 mt-1"></i>
                            <div>
                                <h6 class="mb-1">Waste Management</h6>
                                <p class="text-muted small mb-0">Innovative solutions for plastic waste reduction</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mission-image">
                    <img src="<?php echo ASSETS_PATH; ?>/images/mission-image.jpg"
                        alt="ECCT Mission" class="img-fluid rounded-4 shadow">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Campaigns Section -->
<?php if ($featured_campaigns): ?>
    <section class="campaigns-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Featured Campaigns</h2>
                    <p class="text-muted">Join our ongoing environmental conservation campaigns</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featured_campaigns as $campaign): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="campaign-card card h-100 shadow-sm border-0">
                            <?php if ($campaign['featured_image']): ?>
                                <img src="<?php echo UPLOADS_URL . '/campaigns/' . $campaign['featured_image']; ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($campaign['title']); ?>"
                                    style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title">
                                    <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                        class="text-decoration-none">
                                        <?php echo htmlspecialchars($campaign['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(truncate_text($campaign['description'], 120)); ?>
                                </p>
                                <div class="campaign-meta">
                                    <?php if ($campaign['location']): ?>
                                        <small class="text-muted">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($campaign['location']); ?>
                                        </small>
                                    <?php endif; ?>
                                    <div class="mt-2">
                                        <span class="badge bg-<?php echo match ($campaign['status']) {
                                                                    'active' => 'success',
                                                                    'completed' => 'primary',
                                                                    'planning' => 'warning',
                                                                    default => 'secondary'
                                                                }; ?>">
                                            <?php echo ucfirst($campaign['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                    class="btn btn-primary btn-sm">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/campaigns.php" class="btn btn-outline-primary">
                    View All Campaigns
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Latest News Section -->
<?php if ($featured_news): ?>
    <section class="news-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Latest News & Updates</h2>
                    <p class="text-muted">Stay informed about our environmental conservation activities</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featured_news as $news): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <article class="news-card card h-100 shadow-sm border-0">
                            <?php if ($news['featured_image']): ?>
                                <img src="<?php echo UPLOADS_URL . '/news/' . $news['featured_image']; ?>"
                                    class="card-img-top" alt="<?php echo htmlspecialchars($news['title']); ?>"
                                    style="height: 200px; object-fit: cover;">
                            <?php endif; ?>
                            <div class="card-body d-flex flex-column">
                                <div class="news-meta mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo format_date($news['publish_date']); ?>
                                    </small>
                                </div>
                                <h5 class="card-title">
                                    <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $news['id']; ?>"
                                        class="text-decoration-none">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(truncate_text($news['excerpt'] ?: strip_tags($news['content']), 120)); ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $news['id']; ?>"
                                    class="btn btn-outline-primary btn-sm">
                                    Read More
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-outline-primary">
                    View All News
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Ready to Make a Difference?</h3>
                <p class="mb-0 lead">
                    Join our community of environmental champions and help create a sustainable future for Tanzania.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-light btn-lg">
                    <i class="fas fa-heart me-2"></i>
                    Become a Volunteer
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Gallery Preview Section -->
<?php if ($recent_gallery): ?>
    <section class="gallery-preview py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <h2 class="section-title">Our Impact in Pictures</h2>
                    <p class="text-muted">See the positive change we're making in communities across Tanzania</p>
                </div>
            </div>
            <div class="row">
                <?php foreach (array_slice($recent_gallery, 0, 6) as $image): ?>
                    <div class="col-lg-2 col-md-4 col-6 mb-3">
                        <div class="gallery-item">
                            <img src="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                alt="<?php echo htmlspecialchars($image['title']); ?>"
                                class="img-fluid rounded shadow-sm gallery-thumb"
                                data-bs-toggle="modal"
                                data-bs-target="#galleryModal"
                                data-bs-image="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                data-bs-title="<?php echo htmlspecialchars($image['title']); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/gallery.php" class="btn btn-outline-primary">
                    View Full Gallery
                    <i class="fas fa-images ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" alt="" class="img-fluid" id="galleryModalImage">
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Hero Video Background with Image Fallback */
.hero-video-background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
    /* Set background image as fallback */
    background-image: url('<?php echo SITE_URL . "/" . $hero_background; ?>');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.hero-video {
    position: absolute;
    top: 50%;
    left: 50%;
    min-width: 100%;
    min-height: 100%;
    width: auto;
    height: auto;
    transform: translate(-50%, -50%);
    object-fit: cover;
    z-index: -1;
    /* Hide video initially until loaded */
    opacity: 0;
    transition: opacity 0.5s ease;
}

/* Show video when loaded */
.hero-video.loaded {
    opacity: 1;
}

/* Loading indicator */
.hero-video-background::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 50px;
    height: 50px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 1;
    opacity: 1;
    transition: opacity 0.3s ease;
}

.hero-video-background.loaded::before {
    opacity: 0;
    pointer-events: none;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
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
    });
</script>
<?php
// Additional JavaScript for homepage
$additional_js = [ASSETS_PATH . '/js/homepage.js'];
include 'includes/footer.php';
?>