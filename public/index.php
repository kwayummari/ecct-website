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

// Helper function to get settings
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
    return $db->select($table, ['is_published' => 1], ['order_by' => 'created_at DESC', 'limit' => $limit]);
}

// Helper function to get recent content
function get_recent_content($table, $limit = 6)
{
    global $db;
    return $db->select($table, [], ['order_by' => 'created_at DESC', 'limit' => $limit]);
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
$successful_campaigns = get_setting('successful_campaigns', '10');
$volunteers_count = get_setting('volunteers_count', '500');
$communities_served = get_setting('communities_served', '25');

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
            <img src="<?php echo SITE_URL; ?>/<?php echo $hero_background; ?>" alt="ECCT Environmental Conservation">
        </video>

        <!-- Video Controls -->
        <button class="video-toggle-btn" id="videoToggle" title="Pause video">
            <i class="fas fa-pause"></i>
        </button>
    </div>

    <!-- Hero Content -->
    <div class="hero-content">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8 col-xl-7">
                    <div class="hero-text text-white">
                        <h1 class="hero-title display-3 fw-bold mb-4" data-aos="fade-up">
                            <?php echo htmlspecialchars($hero_title); ?>
                        </h1>
                        <p class="hero-subtitle lead mb-4" data-aos="fade-up" data-aos-delay="200">
                            <?php echo htmlspecialchars($hero_subtitle); ?>
                        </p>
                        <div class="hero-actions" data-aos="fade-up" data-aos-delay="400">
                            <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-success btn-lg me-3 mb-3">
                                <i class="fas fa-hand-holding-heart me-2"></i>
                                Join as Volunteer
                            </a>
                            <a href="<?php echo SITE_URL; ?>/about" class="btn btn-outline-light btn-lg mb-3">
                                <i class="fas fa-info-circle me-2"></i>
                                Learn More
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-xl-5">
                    <div class="hero-stats" data-aos="fade-left" data-aos-delay="600">
                        <div class="stat-card">
                            <div class="stat-number"><?php echo htmlspecialchars($successful_campaigns); ?>+</div>
                            <div class="stat-label">Successful Campaigns</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo htmlspecialchars($volunteers_count); ?>+</div>
                            <div class="stat-label">Active Volunteers</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number"><?php echo htmlspecialchars($communities_served); ?>+</div>
                            <div class="stat-label">Communities Served</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="scroll-indicator">
        <div class="scroll-arrow">
            <i class="fas fa-chevron-down"></i>
        </div>
        <span>Scroll to explore</span>
    </div>

    <!-- Hero Overlay -->
    <div class="hero-overlay"></div>
</section>

<!-- About Section -->
<section class="about-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6" data-aos="fade-right">
                <div class="about-content">
                    <h2 class="section-title mb-4">Who We Are</h2>
                    <p class="lead mb-4">
                        Environmental Conservation Community of Tanzania (ECCT) is dedicated to empowering local communities
                        to create cleaner, greener, resilient and sustainable environments.
                    </p>
                    <p class="mb-4">
                        We tackle global environmental pollution from plastic waste, climate change and loss of biodiversity
                        in both marine and terrestrial environments through community-driven conservation activities.
                    </p>
                    <div class="about-features">
                        <div class="feature-item">
                            <i class="fas fa-leaf text-success me-3"></i>
                            <span>Environmental Conservation</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users text-primary me-3"></i>
                            <span>Community Empowerment</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-recycle text-info me-3"></i>
                            <span>Waste Management</span>
                        </div>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/about" class="btn btn-primary mt-4">
                        <i class="fas fa-arrow-right me-2"></i>
                        Learn More About Us
                    </a>
                </div>
            </div>
            <div class="col-lg-6" data-aos="fade-left">
                <div class="about-image">
                    <img src="<?php echo SITE_URL; ?>/assets/images/about-image.jpg"
                        alt="ECCT Team" class="img-fluid rounded shadow">
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
                <div class="col-12 text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">Latest News & Updates</h2>
                    <p class="section-subtitle">Stay informed about our environmental conservation efforts</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featured_news as $index => $news): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 200; ?>">
                        <article class="news-card">
                            <div class="news-image">
                                <?php if (!empty($news['featured_image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/<?php echo $news['featured_image']; ?>"
                                        alt="<?php echo htmlspecialchars($news['title']); ?>" class="img-fluid">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/news-placeholder.jpg"
                                        alt="<?php echo htmlspecialchars($news['title']); ?>" class="img-fluid">
                                <?php endif; ?>
                                <div class="news-date">
                                    <span><?php echo date('M j', strtotime($news['created_at'])); ?></span>
                                </div>
                            </div>
                            <div class="news-content">
                                <h3 class="news-title">
                                    <a href="<?php echo SITE_URL; ?>/news/<?php echo $news['slug']; ?>">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h3>
                                <p class="news-excerpt">
                                    <?php echo htmlspecialchars(substr(strip_tags($news['content']), 0, 120)) . '...'; ?>
                                </p>
                                <a href="<?php echo SITE_URL; ?>/news/<?php echo $news['slug']; ?>" class="read-more">
                                    Read More <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>/news" class="btn btn-outline-primary">
                        <i class="fas fa-newspaper me-2"></i>
                        View All News
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Featured Campaigns Section -->
<?php if (!empty($featured_campaigns)): ?>
    <section class="campaigns-section py-5">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">Active Campaigns</h2>
                    <p class="section-subtitle">Join our ongoing environmental conservation campaigns</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($featured_campaigns as $index => $campaign): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 200; ?>">
                        <div class="campaign-card">
                            <div class="campaign-image">
                                <?php if (!empty($campaign['featured_image'])): ?>
                                    <img src="<?php echo SITE_URL; ?>/<?php echo $campaign['featured_image']; ?>"
                                        alt="<?php echo htmlspecialchars($campaign['title']); ?>" class="img-fluid">
                                <?php else: ?>
                                    <img src="<?php echo SITE_URL; ?>/assets/images/campaign-placeholder.jpg"
                                        alt="<?php echo htmlspecialchars($campaign['title']); ?>" class="img-fluid">
                                <?php endif; ?>
                                <div class="campaign-status">
                                    <span class="badge bg-<?php echo $campaign['status'] === 'active' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($campaign['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="campaign-content">
                                <h3 class="campaign-title">
                                    <a href="<?php echo SITE_URL; ?>/campaigns/<?php echo $campaign['slug']; ?>">
                                        <?php echo htmlspecialchars($campaign['title']); ?>
                                    </a>
                                </h3>
                                <p class="campaign-description">
                                    <?php echo htmlspecialchars(substr(strip_tags($campaign['description']), 0, 120)) . '...'; ?>
                                </p>
                                <?php if (!empty($campaign['goal_amount']) && !empty($campaign['raised_amount'])): ?>
                                    <div class="campaign-progress">
                                        <div class="progress-info">
                                            <span>Raised: <?php echo number_format($campaign['raised_amount']); ?> TZS</span>
                                            <span>Goal: <?php echo number_format($campaign['goal_amount']); ?> TZS</span>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: <?php echo min(100, ($campaign['raised_amount'] / $campaign['goal_amount']) * 100); ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <a href="<?php echo SITE_URL; ?>/campaigns/<?php echo $campaign['slug']; ?>" class="btn btn-primary">
                                    <i class="fas fa-hand-holding-heart me-2"></i>
                                    Support Campaign
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>/campaigns" class="btn btn-outline-primary">
                        <i class="fas fa-bullhorn me-2"></i>
                        View All Campaigns
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Gallery Section -->
<?php if (!empty($recent_gallery)): ?>
    <section class="gallery-section py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5" data-aos="fade-up">
                    <h2 class="section-title">Our Work in Action</h2>
                    <p class="section-subtitle">See the impact of our environmental conservation efforts</p>
                </div>
            </div>
            <div class="row">
                <?php foreach ($recent_gallery as $index => $image): ?>
                    <div class="col-lg-4 col-md-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                        <div class="gallery-item">
                            <img src="<?php echo SITE_URL; ?>/<?php echo $image['image_path']; ?>"
                                alt="<?php echo htmlspecialchars($image['title'] ?? 'ECCT Gallery'); ?>"
                                class="img-fluid gallery-image"
                                data-bs-toggle="modal"
                                data-bs-target="#galleryModal"
                                data-image="<?php echo SITE_URL; ?>/<?php echo $image['image_path']; ?>"
                                data-title="<?php echo htmlspecialchars($image['title'] ?? ''); ?>">
                            <div class="gallery-overlay">
                                <i class="fas fa-search-plus"></i>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="row">
                <div class="col-12 text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>/gallery" class="btn btn-outline-primary">
                        <i class="fas fa-images me-2"></i>
                        View Full Gallery
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">Gallery Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="" alt="" class="img-fluid" id="galleryModalImage">
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Call to Action Section -->
<section class="cta-section py-5 bg-success text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8" data-aos="fade-right">
                <h2 class="mb-4">Ready to Make a Difference?</h2>
                <p class="lead mb-0">
                    Join our community of environmental champions and help create a sustainable future for Tanzania.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end" data-aos="fade-left">
                <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-light btn-lg">
                    <i class="fas fa-hand-holding-heart me-2"></i>
                    Become a Volunteer
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    /* Hero Video Styles */
    .hero-video-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        overflow: hidden;
    }

    .hero-video {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: opacity 0.3s ease;
    }

    .video-toggle-btn {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 10;
        background: rgba(0, 0, 0, 0.5);
        border: none;
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .video-toggle-btn:hover {
        background: rgba(0, 0, 0, 0.7);
        transform: scale(1.1);
    }

    .hero-content {
        position: relative;
        z-index: 5;
    }

    .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.4);
        z-index: 2;
    }

    /* Fallback for slow connections */
    @media (max-width: 768px) {
        .hero-video {
            display: none !important;
        }

        .hero-video-background {
            background-image: url('<?php echo SITE_URL; ?>/<?php echo $hero_background; ?>');
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
            background-image: url('<?php echo SITE_URL; ?>/<?php echo $hero_background; ?>');
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

        // Gallery modal functionality
        const galleryImages = document.querySelectorAll('.gallery-image');
        const modalImage = document.getElementById('galleryModalImage');
        const modalTitle = document.getElementById('galleryModalLabel');

        galleryImages.forEach(function(img) {
            img.addEventListener('click', function() {
                const imageSrc = this.getAttribute('data-image');
                const imageTitle = this.getAttribute('data-title');

                modalImage.src = imageSrc;
                modalImage.alt = imageTitle;
                modalTitle.textContent = imageTitle || 'Gallery Image';
            });
        });
    });
</script>

<?php
// Additional JavaScript for homepage
$additional_js = [SITE_URL . '/assets/js/homepage.js'];
include 'includes/footer.php';
?>