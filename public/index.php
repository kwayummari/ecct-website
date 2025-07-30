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
<section class="hero-section position-relative overflow-hidden">
    <!-- Video Background -->
    <div class="hero-video-background">
        <video autoplay muted loop playsinline preload="auto" class="hero-video">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.mp4" type="video/mp4">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.webm" type="video/webm">
            <!-- Fallback image if video doesn't load -->
            <img src="<?php echo SITE_URL . '/' . $hero_background; ?>" alt="ECCT Environmental Conservation" class="hero-fallback-image">
        </video>
        <div class="hero-overlay-modern"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-7">
                <div class="hero-content-modern text-white">
                    <div class="hero-badge mb-4 animate-fade-in">
                        <span class="badge-pill bg-success-gradient text-white px-4 py-2 rounded-pill">
                            <i class="fas fa-leaf me-2"></i>Environmental Conservation
                        </span>
                    </div>
                    <h1 class="hero-title-modern fw-bold mb-4 animate-fade-in text-white">
                        <?php echo htmlspecialchars($hero_title); ?>
                    </h1>
                    <p class="hero-subtitle-modern mb-5 animate-fade-in-delay">
                        <?php echo htmlspecialchars($hero_subtitle); ?>
                    </p>
                    <div class="hero-buttons-modern animate-fade-in-delay-2">
                        <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-success-modern btn-lg me-3">
                            <i class="fas fa-heart me-2"></i>Join as Volunteer
                        </a>
                        <a href="<?php echo SITE_URL; ?>/about" class="btn btn-glass btn-lg">
                            <i class="fas fa-arrow-right me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>

            <!-- Enhanced Statistics Cards -->
            <div class="col-lg-5">
                <div class="hero-stats-modern animate-slide-up">
                    <div class="stats-grid">
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number"><?php echo $successful_campaigns; ?>+</h3>
                                <p class="stat-label">Successful Campaigns</p>
                            </div>
                        </div>
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number"><?php echo $volunteers_count; ?>+</h3>
                                <p class="stat-label">Active Volunteers</p>
                            </div>
                        </div>
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-globe-africa"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number"><?php echo $communities_served; ?>+</h3>
                                <p class="stat-label">Communities Served</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Scroll Indicator -->
        <div class="scroll-indicator-modern position-absolute bottom-0 start-50 translate-middle-x pb-4">
            <div class="scroll-arrow-modern text-white text-center">
                <div class="scroll-mouse">
                    <div class="scroll-wheel"></div>
                </div>
                <p class="small mt-3 mb-0 text-uppercase tracking-wide">Scroll to Explore</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission Section -->
<section class="mission-section-modern py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="mission-content-modern">
                    <div class="section-badge mb-4">
                        <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                            <i class="fas fa-heart me-2"></i>Our Mission
                        </span>
                    </div>
                    <h2 class="section-title-modern mb-4">Empowering Communities for a Sustainable Future</h2>
                    <p class="mission-description mb-5">
                        To empower local communities to create cleaner, greener, resilient and sustainable environments
                        through tackling global environmental pollution from plastic waste, climate change and loss of
                        biodiversity in both marine and terrestrial environments.
                    </p>
                    <div class="mission-points-modern">
                        <div class="mission-point-card mb-4">
                            <div class="point-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <div class="point-content">
                                <h5 class="point-title mb-2">Environmental Conservation</h5>
                                <p class="point-description mb-0">Protecting and preserving Tanzania's natural ecosystems through innovative conservation strategies</p>
                            </div>
                        </div>
                        <div class="mission-point-card mb-4">
                            <div class="point-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="point-content">
                                <h5 class="point-title mb-2">Community Empowerment</h5>
                                <p class="point-description mb-0">Building local capacity for sustainable development and environmental stewardship</p>
                            </div>
                        </div>
                        <div class="mission-point-card">
                            <div class="point-icon">
                                <i class="fas fa-recycle"></i>
                            </div>
                            <div class="point-content">
                                <h5 class="point-title mb-2">Waste Management</h5>
                                <p class="point-description mb-0">Innovative solutions for plastic waste reduction and circular economy implementation</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mission-image-modern">
                    <div class="image-container">
                        <img src="<?php echo ASSETS_PATH; ?>/images/_X4A8064.jpg"
                            alt="ECCT Mission" class="img-fluid mission-img">
                        <div class="image-overlay">
                            <div class="overlay-content">
                                <i class="fas fa-play-circle fa-3x"></i>
                                <p class="mt-3 mb-0">Watch Our Impact Story</p>
                            </div>
                        </div>
                    </div>
                    <div class="floating-stats">
                        <div class="floating-stat">
                            <span class="stat-value">15+</span>
                            <span class="stat-text">Years Experience</span>
                        </div>
                        <div class="floating-stat">
                            <span class="stat-value">100%</span>
                            <span class="stat-text">Community Focused</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Featured Campaigns Section -->
<?php if ($featured_campaigns): ?>
    <section class="campaigns-section-modern py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="section-badge mb-4">
                        <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                            <i class="fas fa-bullhorn me-2"></i>Active Campaigns
                        </span>
                    </div>
                    <h2 class="section-title-modern mb-4">Join Our Environmental Campaigns</h2>
                    <p class="section-subtitle">Be part of the change. Join our active environmental conservation campaigns and make a lasting impact on Tanzania's future.</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featured_campaigns as $index => $campaign): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="campaign-card-modern h-100" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="campaign-image-container">
                                <?php if ($campaign['featured_image']): ?>
                                    <img src="<?php echo SITE_URL; ?>/<?php echo $campaign['featured_image']; ?>"
                                        class="campaign-image" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                <?php else: ?>
                                    <div class="campaign-image-placeholder">
                                        <i class="fas fa-leaf fa-3x"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="campaign-overlay">
                                    <div class="overlay-content">
                                        <i class="fas fa-arrow-right"></i>
                                    </div>
                                </div>
                                <div class="campaign-status">
                                    <span class="status-badge status-<?php echo $campaign['status']; ?>">
                                        <?php echo ucfirst($campaign['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="campaign-content">
                                <div class="campaign-header">
                                    <h5 class="campaign-title">
                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>">
                                            <?php echo htmlspecialchars($campaign['title']); ?>
                                        </a>
                                    </h5>
                                    <?php if ($campaign['location']): ?>
                                        <div class="campaign-location">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($campaign['location']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <p class="campaign-description">
                                    <?php echo htmlspecialchars(truncate_text($campaign['description'], 120)); ?>
                                </p>
                                <div class="campaign-footer">
                                    <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                        class="btn-campaign-modern">
                                        <span>Learn More</span>
                                        <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-5">
                <a href="<?php echo SITE_URL; ?>/campaigns.php" class="btn btn-primary-modern btn-lg">
                    <i class="fas fa-binoculars me-2"></i>
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
                                <img src="<?php echo SITE_URL; ?>/<?php echo $news['featured_image']; ?>"
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
<section class="cta-section-modern py-5 position-relative overflow-hidden">
    <div class="cta-background"></div>
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="cta-content">
                    <div class="cta-badge mb-3">
                        <span class="badge bg-white bg-opacity-20 px-3 py-2 rounded-pill text-white">
                            <i class="fas fa-rocket me-2"></i>Join the Movement
                        </span>
                    </div>
                    <h3 class="cta-title mb-4">Ready to Make a Real Difference?</h3>
                    <p class="cta-description mb-0">
                        Join our community of passionate environmental champions and help create a sustainable,
                        greener future for Tanzania. Every action counts, every volunteer matters.
                    </p>
                    <div class="cta-stats mt-4">
                        <div class="row">
                            <div class="col-6 col-md-3">
                                <div class="mini-stat">
                                    <span class="mini-stat-number">500+</span>
                                    <span class="mini-stat-label">Volunteers</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mini-stat">
                                    <span class="mini-stat-number">25+</span>
                                    <span class="mini-stat-label">Communities</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mini-stat">
                                    <span class="mini-stat-number">10+</span>
                                    <span class="mini-stat-label">Projects</span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mini-stat">
                                    <span class="mini-stat-number">100%</span>
                                    <span class="mini-stat-label">Impact</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="cta-action">
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-cta-modern btn-lg mb-3">
                        <i class="fas fa-heart me-2"></i>
                        <span>Become a Volunteer</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    <p class="cta-note">
                        <small><i class="fas fa-shield-alt me-1"></i>100% free to join</small>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="cta-floating-elements">
        <div class="floating-element element-1">🌱</div>
        <div class="floating-element element-2">🌍</div>
        <div class="floating-element element-3">♻️</div>
        <div class="floating-element element-4">🌿</div>
    </div>
</section>

<!-- Gallery Preview Section -->
<?php if ($recent_gallery): ?>
    <section class="gallery-preview-modern py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5">
                    <div class="section-badge mb-4">
                        <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                            <i class="fas fa-camera me-2"></i>Our Impact Gallery
                        </span>
                    </div>
                    <h2 class="section-title-modern mb-4">Witness the Change in Action</h2>
                    <p class="section-subtitle">
                        Explore our visual journey of environmental conservation and community empowerment across Tanzania
                    </p>
                </div>
            </div>

            <div class="gallery-modern-grid">
                <?php
                $displayImages = array_slice($recent_gallery, 0, 6);
                foreach ($displayImages as $index => $image):
                    $gridClass = '';
                    if ($index === 0) $gridClass = 'gallery-item-large';
                    elseif ($index === 1) $gridClass = 'gallery-item-tall';
                    else $gridClass = 'gallery-item-regular';
                ?>
                    <div class="gallery-item-modern <?php echo $gridClass; ?>" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="gallery-image-container">
                            <img src="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                alt="<?php echo htmlspecialchars($image['title']); ?>"
                                class="gallery-image-modern"
                                data-bs-toggle="modal"
                                data-bs-target="#galleryModal"
                                data-bs-image="<?php echo UPLOADS_URL . '/gallery/' . $image['image_path']; ?>"
                                data-bs-title="<?php echo htmlspecialchars($image['title']); ?>">
                            <div class="gallery-overlay-modern">
                                <div class="gallery-overlay-content">
                                    <div class="gallery-icon">
                                        <i class="fas fa-search-plus"></i>
                                    </div>
                                    <h6 class="gallery-title"><?php echo htmlspecialchars($image['title']); ?></h6>
                                    <p class="gallery-category">Environmental Impact</p>
                                </div>
                            </div>
                            <div class="gallery-hover-effect"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-5">
                <a href="<?php echo SITE_URL; ?>/gallery.php" class="btn btn-primary-modern btn-lg">
                    <i class="fas fa-images me-2"></i>
                    Explore Full Gallery
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
                <p class="mt-3 text-muted">
                    <small>Over 100+ photos documenting our environmental conservation journey</small>
                </p>
            </div>
        </div>
    </section>

    <!-- Enhanced Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content gallery-modal-modern">
                <div class="modal-header border-0">
                    <h5 class="modal-title text-white" id="galleryModalLabel">Image Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" alt="" class="img-fluid gallery-modal-image" id="galleryModalImage">
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<style>
    /* Optimized Video Background */
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
        z-index: -2;
        /* Optimize video performance */
        will-change: transform;
        filter: brightness(0.8);
        /* Slightly darken for better text readability */
    }

    /* Loading state for video */
    .hero-video-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, #2c5f2d, #4a9c4f);
        z-index: -1;
        opacity: 1;
        transition: opacity 0.5s ease;
    }

    .hero-video-background.loaded::before {
        opacity: 0;
    }

    /* Mobile optimizations - disable video on smaller screens */
    @media (max-width: 768px) {
        .hero-video {
            display: none !important;
        }

        .hero-video-background {
            background-image: url('<?php echo SITE_URL . '/' . $hero_background; ?>');
            background-size: cover;
            background-position: center;
        }
    }

    /* Slow connection detection */
    @media (prefers-reduced-data: reduce) {
        .hero-video {
            display: none !important;
        }

        .hero-video-background {
            background-image: url('<?php echo SITE_URL . '/' . $hero_background; ?>');
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
    });
</script>
<?php
// Additional JavaScript for homepage
$additional_js = [ASSETS_PATH . '/js/homepage.js'];
include 'includes/footer.php';
?>
?>