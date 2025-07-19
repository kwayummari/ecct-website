<?php

/**
 * ECCT Website Homepage - Enhanced with Creative Design
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

<!-- Epic Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Video Background -->
    <div class="hero-video-background">
        <div class="hero-image-placeholder">
            <img src="<?php echo SITE_URL . '/' . $hero_background; ?>"
                alt="ECCT Environmental Conservation"
                class="hero-fallback-image">
        </div>

        <video autoplay muted loop playsinline preload="metadata" class="hero-video">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.webm" type="video/webm">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.mp4" type="video/mp4">
        </video>

        <div class="hero-overlay"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-100">
            <div class="col-lg-6">
                <div class="hero-content">
                    <h1 class="animate-fade-in">
                        <span class="highlight-text">Together We Can</span><br>
                        Save Our Environment
                    </h1>
                    <p class="animate-fade-in-delay">
                        <?php echo htmlspecialchars($hero_subtitle); ?>
                    </p>
                    <div class="hero-buttons animate-fade-in-delay-2">
                        <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-primary btn-lg">
                            <i class="fas fa-hands-helping"></i>
                            Join as Volunteer
                        </a>
                        <a href="<?php echo SITE_URL; ?>/about" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-info-circle"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="hero-stats animate-slide-up">
                    <div class="row text-center">
                        <div class="col-4">
                            <div class="stat-item">
                                <h3><?php echo $successful_campaigns; ?></h3>
                                <p>Successful Campaigns</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h3><?php echo $volunteers_count; ?></h3>
                                <p>Active Volunteers</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="stat-item">
                                <h3><?php echo $communities_served; ?></h3>
                                <p>Communities Served</p>
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
            <p>Scroll to explore</p>
        </div>
    </div>

    <!-- Video Controls -->
    <div class="video-controls">
        <button class="video-toggle-btn" id="videoToggle" title="Pause/Play video">
            <i class="fas fa-pause"></i>
        </button>
    </div>
</section>

<!-- Mission Section -->
<section class="mission-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="mission-content">
                    <h2 class="section-title">
                        <span class="highlight-text">Our Mission</span>
                    </h2>
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
                    <h2 class="section-title">
                        <span class="highlight-text">Featured Campaigns</span>
                    </h2>
                    <p class="section-subtitle">Join our ongoing environmental conservation campaigns</p>
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
                                    <a href="<?php echo SITE_URL; ?>/campaigns?id=<?php echo $campaign['id']; ?>"
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
                                <a href="<?php echo SITE_URL; ?>/campaigns?id=<?php echo $campaign['id']; ?>"
                                    class="btn btn-primary btn-sm">
                                    Learn More
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/campaigns" class="btn btn-outline-primary">
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
                    <h2 class="section-title">
                        <span class="highlight-text">Latest News</span> & Updates
                    </h2>
                    <p class="section-subtitle">Stay informed about our environmental conservation activities</p>
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
                                    <a href="<?php echo SITE_URL; ?>/news?id=<?php echo $news['id']; ?>"
                                        class="text-decoration-none">
                                        <?php echo htmlspecialchars($news['title']); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo htmlspecialchars(truncate_text($news['excerpt'] ?: strip_tags($news['content']), 120)); ?>
                                </p>
                            </div>
                            <div class="card-footer bg-transparent border-0">
                                <a href="<?php echo SITE_URL; ?>/news?id=<?php echo $news['id']; ?>"
                                    class="btn btn-outline-primary btn-sm">
                                    Read More
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="<?php echo SITE_URL; ?>/news" class="btn btn-outline-primary">
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
                <h3 class="mb-3">
                    <span class="highlight-text">Ready to Make</span> a Difference?
                </h3>
                <p class="mb-0 lead">
                    Join our community of environmental champions and help create a sustainable future for Tanzania.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-light btn-lg">
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
                    <h2 class="section-title">
                        <span class="highlight-text">Our Impact</span> in Pictures
                    </h2>
                    <p class="section-subtitle">See the positive change we're making in communities across Tanzania</p>
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
                <a href="<?php echo SITE_URL; ?>/gallery" class="btn btn-outline-primary">
                    View Full Gallery
                    <i class="fas fa-images ms-2"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Enhanced Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">Image Gallery</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <img src="" alt="" class="img-fluid w-100" id="galleryModalImage">
                </div>
                <div class="modal-footer justify-content-center">
                    <div class="image-info">
                        <p class="mb-0 text-muted" id="galleryModalDescription"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Enhanced JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Enhanced video handling with loading states
        const video = document.querySelector('.hero-video');
        const videoBackground = document.querySelector('.hero-video-background');
        const imagePlaceholder = document.querySelector('.hero-image-placeholder');
        const toggleBtn = document.getElementById('videoToggle');
        const toggleIcon = toggleBtn?.querySelector('i');

        let videoLoaded = false;

        // Check connection and device capabilities
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
        const slowConnection = connection && (connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g');
        const prefersReducedData = window.matchMedia('(prefers-reduced-data: reduce)').matches;
        const isMobile = window.innerWidth <= 768;

        function showImageFallback() {
            if (video) video.style.display = 'none';
            if (imagePlaceholder) imagePlaceholder.style.opacity = '1';
            videoBackground.classList.add('loaded');
            if (toggleBtn) toggleBtn.style.display = 'none';
        }

        function showVideo() {
            if (imagePlaceholder) {
                imagePlaceholder.style.opacity = '0';
                setTimeout(() => imagePlaceholder.style.display = 'none', 500);
            }
            video.classList.add('loaded');
            videoBackground.classList.add('loaded');
            if (toggleBtn) toggleBtn.style.display = 'block';
        }

        // Decide whether to load video
        if (slowConnection || prefersReducedData || isMobile) {
            console.log('Using image fallback due to device/connection constraints');
            showImageFallback();
        } else if (video) {
            // Set timeout for video loading (10 seconds max)
            const loadTimeout = setTimeout(() => {
                if (!videoLoaded) {
                    console.log('Video loading timeout, falling back to image');
                    showImageFallback();
                }
            }, 10000);

            // Video loaded successfully
            video.addEventListener('loadeddata', function() {
                videoLoaded = true;
                clearTimeout(loadTimeout);
                console.log('Video loaded successfully');
                showVideo();
            });

            // Video failed to load
            video.addEventListener('error', function(e) {
                clearTimeout(loadTimeout);
                console.log('Video failed to load:', e);
                showImageFallback();
            });

            // Video controls
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (video.paused) {
                        video.play().catch(e => {
                            console.log('Video play failed:', e);
                            showImageFallback();
                        });
                        toggleIcon.className = 'fas fa-pause';
                        toggleBtn.title = 'Pause video';
                    } else {
                        video.pause();
                        toggleIcon.className = 'fas fa-play';
                        toggleBtn.title = 'Play video';
                    }
                });
            }

            // Try to load the video
            video.load();
        }

        // Enhanced navbar scroll effect
        const navbar = document.querySelector('.navbar');
        let lastScrollY = window.scrollY;

        window.addEventListener('scroll', function() {
            const currentScrollY = window.scrollY;

            if (currentScrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }

            // Hide navbar on scroll down, show on scroll up
            if (currentScrollY > lastScrollY && currentScrollY > 100) {
                navbar.style.transform = 'translateY(-100%)';
            } else {
                navbar.style.transform = 'translateY(0)';
            }

            lastScrollY = currentScrollY;
        });

        // Enhanced scroll indicator
        const scrollIndicator = document.querySelector('.scroll-indicator');
        if (scrollIndicator) {
            scrollIndicator.addEventListener('click', function() {
                const nextSection = document.querySelector('.mission-section');
                if (nextSection) {
                    nextSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                } else {
                    window.scrollTo({
                        top: window.innerHeight,
                        behavior: 'smooth'
                    });
                }
            });

            // Hide scroll indicator when scrolled
            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    scrollIndicator.style.opacity = '0';
                    scrollIndicator.style.transform = 'translateX(-50%) translateY(20px)';
                } else {
                    scrollIndicator.style.opacity = '1';
                    scrollIndicator.style.transform = 'translateX(-50%) translateY(0)';
                }
            });
        }

        // Enhanced back to top button (auto-created)
        let backToTop = document.getElementById('backToTop');
        if (!backToTop) {
            backToTop = document.createElement('button');
            backToTop.id = 'backToTop';
            backToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
            backToTop.title = 'Back to top';
            backToTop.setAttribute('aria-label', 'Back to top');
            document.body.appendChild(backToTop);
        }

        window.addEventListener('scroll', function() {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });

        backToTop.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Enhanced highlight text animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate');
                    // Add a slight delay for staggered animation effect
                    const delay = Array.from(entry.target.parentNode.children).indexOf(entry.target) * 100;
                    setTimeout(() => {
                        entry.target.style.animationDelay = '0s';
                    }, delay);
                }
            });
        }, observerOptions);

        // Observe highlight text elements
        document.querySelectorAll('.highlight-text').forEach(el => {
            observer.observe(el);
        });

        // Observe other animated elements
        document.querySelectorAll('.animate-fade-in, .animate-fade-in-delay, .animate-fade-in-delay-2, .animate-slide-up').forEach(el => {
            observer.observe(el);
        });

        // Enhanced gallery modal
        const galleryModal = document.getElementById('galleryModal');
        if (galleryModal) {
            galleryModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-bs-image');
                const imageTitle = button.getAttribute('data-bs-title');

                const modalImage = galleryModal.querySelector('#galleryModalImage');
                const modalTitle = galleryModal.querySelector('#galleryModalLabel');
                const modalDescription = galleryModal.querySelector('#galleryModalDescription');

                modalImage.src = imageSrc;
                modalImage.alt = imageTitle;
                modalTitle.textContent = imageTitle;

                if (modalDescription) {
                    modalDescription.textContent = imageTitle;
                }

                // Add loading state
                modalImage.style.opacity = '0';
                modalImage.onload = function() {
                    modalImage.style.opacity = '1';
                };
            });

            // Keyboard navigation for modal
            galleryModal.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    const modal = bootstrap.Modal.getInstance(galleryModal);
                    modal.hide();
                }
            });
        }

        // Enhanced card hover effects
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-15px) rotateX(5deg)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) rotateX(0)';
            });
        });

        // Smooth scrolling for all internal links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Performance optimization: Lazy load images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.classList.remove('lazy');
                            observer.unobserve(img);
                        }
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Add some interactive elements feedback
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        console.log('🎨 ECCT Enhanced Homepage Loaded Successfully! 🌿');
    });
</script>

<?php
// Additional JavaScript for homepage
$additional_js = [ASSETS_PATH . '/js/homepage.js'];
include 'includes/footer.php';
?>