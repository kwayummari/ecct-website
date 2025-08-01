<?php

/**
 * ECCT Website Homepage
 * Environmental Conservation Community of Tanzania
 */

if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', __DIR__);
}
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
$featured_partners = get_partners(8, true);

// Include header
include 'includes/header.php';
?>

<style>
    /* Hero Animation Styles */
    .floating-elements {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .floating-icon {
        position: absolute;
        color: rgba(255, 255, 255, 0.1);
        font-size: 2rem;
        animation: float 6s ease-in-out infinite;
    }

    .floating-icon-1 {
        top: 20%;
        left: 10%;
        animation-delay: 0s;
        animation-duration: 8s;
    }

    .floating-icon-2 {
        top: 60%;
        left: 15%;
        animation-delay: 1s;
        animation-duration: 7s;
    }

    .floating-icon-3 {
        top: 30%;
        right: 20%;
        animation-delay: 2s;
        animation-duration: 9s;
    }

    .floating-icon-4 {
        bottom: 40%;
        right: 10%;
        animation-delay: 3s;
        animation-duration: 6s;
    }

    .floating-icon-5 {
        top: 70%;
        left: 60%;
        animation-delay: 4s;
        animation-duration: 8s;
    }

    .floating-icon-6 {
        top: 10%;
        right: 40%;
        animation-delay: 5s;
        animation-duration: 7s;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px) rotate(0deg);
            opacity: 0.1;
        }

        25% {
            transform: translateY(-20px) rotate(5deg);
            opacity: 0.2;
        }

        50% {
            transform: translateY(-10px) rotate(-3deg);
            opacity: 0.15;
        }

        75% {
            transform: translateY(-30px) rotate(8deg);
            opacity: 0.25;
        }
    }

    /* Particle System */
    .particles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .particle {
        position: absolute;
        background: rgba(32, 136, 54, 0.3);
        border-radius: 50%;
        animation: particleFloat 15s linear infinite;
    }

    .particle-1 {
        width: 4px;
        height: 4px;
        left: 20%;
        animation-delay: 0s;
    }

    .particle-2 {
        width: 6px;
        height: 6px;
        left: 40%;
        animation-delay: 2s;
    }

    .particle-3 {
        width: 3px;
        height: 3px;
        left: 60%;
        animation-delay: 4s;
    }

    .particle-4 {
        width: 5px;
        height: 5px;
        left: 80%;
        animation-delay: 6s;
    }

    .particle-5 {
        width: 4px;
        height: 4px;
        left: 30%;
        animation-delay: 8s;
    }

    .particle-6 {
        width: 6px;
        height: 6px;
        left: 70%;
        animation-delay: 10s;
    }

    .particle-7 {
        width: 3px;
        height: 3px;
        left: 50%;
        animation-delay: 12s;
    }

    .particle-8 {
        width: 5px;
        height: 5px;
        left: 10%;
        animation-delay: 14s;
    }

    @keyframes particleFloat {
        0% {
            transform: translateY(100vh) scale(0);
            opacity: 0;
        }

        10% {
            opacity: 1;
            transform: scale(1);
        }

        90% {
            opacity: 1;
        }

        100% {
            transform: translateY(-100px) scale(0);
            opacity: 0;
        }
    }

    /* Enhanced Text Animations */
    .animate-fade-in {
        animation: fadeInUp 1s ease-out;
    }

    .animate-fade-in-delay {
        animation: fadeInUp 1s ease-out 0.3s both;
    }

    .animate-fade-in-delay-2 {
        animation: fadeInUp 1s ease-out 0.6s both;
    }

    .animate-slide-up {
        animation: slideUp 1s ease-out 0.9s both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Button Animations */
    .btn-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 4px 15px rgba(32, 136, 54, 0.3);
        }

        50% {
            box-shadow: 0 4px 25px rgba(32, 136, 54, 0.6);
            transform: translateY(-2px);
        }

        100% {
            box-shadow: 0 4px 15px rgba(32, 136, 54, 0.3);
        }
    }

    .btn-shimmer {
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        animation: shimmer 3s infinite;
    }

    @keyframes shimmer {
        0% {
            left: -100%;
        }

        100% {
            left: 100%;
        }
    }

    .btn-hover-float:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(255, 255, 255, 0.2);
    }

    .icon-bounce {
        animation: iconBounce 2s infinite;
    }

    @keyframes iconBounce {

        0%,
        100% {
            transform: translateX(0);
        }

        50% {
            transform: translateX(5px);
        }
    }

    /* Stats Counter Animation */
    .stat-card-modern {
        animation: statCardFloat 3s ease-in-out infinite;
    }

    .stat-card-modern:nth-child(1) {
        animation-delay: 0s;
    }

    .stat-card-modern:nth-child(2) {
        animation-delay: 0.5s;
    }

    .stat-card-modern:nth-child(3) {
        animation-delay: 1s;
    }

    @keyframes statCardFloat {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-5px);
        }
    }

    /* Enhanced Scroll Indicator */
    .scroll-indicator-modern {
        animation: scrollBounce 2s infinite;
    }

    @keyframes scrollBounce {

        0%,
        100% {
            transform: translateY(0) translateX(-50%);
        }

        50% {
            transform: translateY(-10px) translateX(-50%);
        }
    }

    /* Video Loading Animation */
    .hero-video-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(45deg, #208836, #000000);
        background-size: 400% 400%;
        animation: videoLoading 3s ease infinite;
        z-index: -1;
    }

    @keyframes videoLoading {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    /* Mobile Responsive Animations */
    @media (max-width: 768px) {
        .floating-icon {
            font-size: 1.5rem;
        }

        .particle {
            display: none;
        }

        .btn-pulse {
            animation: none;
        }
    }
</style>

<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Video Background -->
    <div class="hero-video-background">
        <video autoplay muted loop playsinline preload="auto" class="hero-video">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.mp4" type="video/mp4">
            <source src="<?php echo SITE_URL; ?>/assets/videos/MAZINGIRA.webm" type="video/webm">
            <!-- Fallback image if video doesn't load -->
            <img src="<?php echo SITE_URL; ?>/assets/images/green-generation/IMG_3267.JPG" alt="ECCT Environmental Conservation" class="hero-fallback-image">
        </video>
        <div class="hero-overlay-modern" style="background: linear-gradient(135deg, rgba(0, 0, 0, 0.6) 0%, rgba(32, 136, 54, 0.4) 100%);"></div>
    </div>

    <!-- Floating Animation Elements -->
    <div class="floating-elements">
        <div class="floating-icon floating-icon-1">
            <i class="fas fa-leaf"></i>
        </div>
        <div class="floating-icon floating-icon-2">
            <i class="fas fa-tree"></i>
        </div>
        <div class="floating-icon floating-icon-3">
            <i class="fas fa-globe-africa"></i>
        </div>
        <div class="floating-icon floating-icon-4">
            <i class="fas fa-water"></i>
        </div>
        <div class="floating-icon floating-icon-5">
            <i class="fas fa-seedling"></i>
        </div>
        <div class="floating-icon floating-icon-6">
            <i class="fas fa-sun"></i>
        </div>
    </div>

    <!-- Animated Particles -->
    <div class="particles">
        <div class="particle particle-1"></div>
        <div class="particle particle-2"></div>
        <div class="particle particle-3"></div>
        <div class="particle particle-4"></div>
        <div class="particle particle-5"></div>
        <div class="particle particle-6"></div>
        <div class="particle particle-7"></div>
        <div class="particle particle-8"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-7">
                <div class="hero-content-modern text-white">
                    <div class="hero-badge mb-4 animate-fade-in">
                        <span class="badge-pill px-4 py-2 rounded-pill" style="background: #208836; color: #ffffff; border: 2px solid #ffffff;">
                            <i class="fas fa-leaf me-2"></i>Environmental Conservation
                        </span>
                    </div>
                    <h1 class="hero-title-modern fw-bold mb-4 animate-fade-in" style="color: #ffffff;">
                        <?php echo htmlspecialchars($hero_title); ?>
                    </h1>
                    <p class="hero-subtitle-modern mb-5 animate-fade-in-delay" style="color: #ffffff; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);">
                        <?php echo htmlspecialchars($hero_subtitle); ?>
                    </p>
                    <div class="hero-buttons-modern animate-fade-in-delay-2">
                        <a href="<?php echo SITE_URL; ?>/volunteer" class="btn btn-lg me-3 btn-pulse" style="background: #208836; color: #ffffff; border: 2px solid #ffffff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(32, 136, 54, 0.3); position: relative; overflow: hidden;">
                            <span class="btn-shimmer"></span>
                            <i class="fas fa-heart me-2"></i>Join as Volunteer
                        </a>
                        <a href="<?php echo SITE_URL; ?>/about" class="btn btn-lg btn-hover-float" style="background: transparent; color: #ffffff; border: 2px solid #ffffff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600; transition: all 0.3s ease;">
                            <i class="fas fa-arrow-right me-2 icon-bounce"></i>Learn More
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
                                <h3 class="stat-number" data-count="<?php echo $successful_campaigns; ?>"><?php echo $successful_campaigns; ?>+</h3>
                                <p class="stat-label">Successful Campaigns</p>
                            </div>
                        </div>
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number" data-count="<?php echo $volunteers_count; ?>"><?php echo $volunteers_count; ?>+</h3>
                                <p class="stat-label">Active Volunteers</p>
                            </div>
                        </div>
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-globe-africa"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number" data-count="<?php echo $communities_served; ?>"><?php echo $communities_served; ?>+</h3>
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

<script>
    // Counter Animation for Stats
    function animateCounters() {
        const counters = document.querySelectorAll('.stat-number[data-count]');

        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-count'));
            const text = counter.textContent;
            const suffix = text.includes('+') ? '+' : '';
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    counter.textContent = target + suffix;
                    clearInterval(timer);
                } else {
                    counter.textContent = Math.floor(current) + suffix;
                }
            }, 30);
        });
    }

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.3,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Trigger counter animation when stats come into view
                if (entry.target.classList.contains('hero-stats-modern')) {
                    setTimeout(animateCounters, 500);
                }

                // Add visible class for other animations
                entry.target.classList.add('animated-visible');
            }
        });
    }, observerOptions);

    // Observe elements when DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
        const statsSection = document.querySelector('.hero-stats-modern');
        if (statsSection) {
            observer.observe(statsSection);
        }

        // Video loading handler
        const video = document.querySelector('.hero-video');
        const videoBackground = document.querySelector('.hero-video-background');

        if (video) {
            video.addEventListener('loadeddata', function() {
                videoBackground.style.opacity = '1';
            });

            video.addEventListener('error', function() {
                console.log('Video failed to load, showing fallback image');
                // Fallback image is already in place
            });
        }

        // Enhanced button interactions
        const buttons = document.querySelectorAll('.btn-pulse, .btn-hover-float');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px) scale(1.02)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Parallax effect for floating elements
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const rate = scrolled * -0.5;
            const floatingElements = document.querySelector('.floating-elements');

            if (floatingElements) {
                floatingElements.style.transform = `translateY(${rate}px)`;
            }
        });

        // Dynamic particle generation
        function createParticle() {
            const particlesContainer = document.querySelector('.particles');
            if (!particlesContainer) return;

            const particle = document.createElement('div');
            particle.className = 'particle dynamic-particle';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.width = (Math.random() * 4 + 2) + 'px';
            particle.style.height = particle.style.width;
            particle.style.animationDuration = (Math.random() * 10 + 10) + 's';
            particle.style.opacity = Math.random() * 0.5 + 0.1;

            particlesContainer.appendChild(particle);

            // Remove particle after animation
            setTimeout(() => {
                if (particle.parentNode) {
                    particle.parentNode.removeChild(particle);
                }
            }, 15000);
        }

        // Generate new particles every 3 seconds
        setInterval(createParticle, 3000);

        // Initial particles burst
        for (let i = 0; i < 5; i++) {
            setTimeout(createParticle, i * 500);
        }
    });

    // Performance optimization: Reduce animations on low-end devices
    if (navigator.hardwareConcurrency && navigator.hardwareConcurrency < 4) {
        document.body.classList.add('reduced-motion');
    }
</script>

<style>
    /* Additional dynamic styles */
    .dynamic-particle {
        animation: particleFloat 15s linear infinite;
        background: rgba(32, 136, 54, 0.2);
        border-radius: 50%;
        position: absolute;
    }

    .animated-visible {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }

    .reduced-motion .floating-icon,
    .reduced-motion .particle,
    .reduced-motion .btn-pulse {
        animation: none !important;
    }

    /* Hover enhancements */
    .stat-card-modern:hover {
        transform: translateY(-10px) scale(1.05);
        box-shadow: 0 15px 35px rgba(32, 136, 54, 0.2);
        transition: all 0.3s ease;
    }

    .stat-card-modern:hover .stat-icon {
        transform: scale(1.2) rotate(10deg);
        transition: all 0.3s ease;
    }

    /* Enhanced glow effects */
    .hero-badge:hover {
        box-shadow: 0 0 20px rgba(32, 136, 54, 0.8);
        transition: all 0.3s ease;
    }
</style>

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
                        <span class="badge bg-white bg-opacity-20 px-3 py-2 rounded-pill text-success">
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
                            <img src="<?php echo SITE_URL; ?>/<?php echo $image['image_path']; ?>"
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

<!-- Partners Section -->
<section class="partners-section-modern py-5" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-3">
                    <span class="badge-pill px-4 py-2 rounded-pill" style="background: rgba(32, 136, 54, 0.1); color: #208836; border: 2px solid #208836;">
                        <i class="fas fa-handshake me-2"></i>Our Partners
                    </span>
                </div>
                <h2 class="section-title-modern fw-bold mb-4" style="color: #208836;">Collaborating for Impact</h2>
                <p class="section-subtitle text-muted mb-0">Working together with organizations that share our vision for environmental conservation</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($featured_partners)): ?>
                <?php foreach ($featured_partners as $partner): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="partner-card-modern h-100">
                            <div class="partner-logo-container">
                                <?php if (!empty($partner['logo_path']) && file_exists($partner['logo_path'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($partner['logo_path']); ?>"
                                        alt="<?php echo htmlspecialchars($partner['name']); ?>"
                                        class="partner-logo">
                                <?php else: ?>
                                    <div class="partner-logo-placeholder">
                                        <i class="fas fa-building" style="font-size: 2rem; color: #208836;"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="partner-content">
                                <h5 class="partner-name"><?php echo htmlspecialchars($partner['name']); ?></h5>
                                <?php if (!empty($partner['description'])): ?>
                                    <p class="partner-description"><?php echo htmlspecialchars(substr($partner['description'], 0, 100)) . (strlen($partner['description']) > 100 ? '...' : ''); ?></p>
                                <?php endif; ?>
                                <div class="partner-type">
                                    <span class="type-badge"><?php echo ucfirst(htmlspecialchars($partner['partnership_type'])); ?> Partner</span>
                                </div>
                                <?php if (!empty($partner['website_url'])): ?>
                                    <div class="partner-link">
                                        <a href="<?php echo htmlspecialchars($partner['website_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn-partner-link">
                                            <i class="fas fa-external-link-alt me-2"></i>Visit Website
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="no-partners-message py-5">
                        <i class="fas fa-handshake mb-3" style="font-size: 3rem; color: #208836; opacity: 0.5;"></i>
                        <h4 style="color: #666;">Building Partnerships</h4>
                        <p class="text-muted">We're actively seeking partners to join our mission. Contact us to explore collaboration opportunities.</p>
                        <a href="<?php echo SITE_URL; ?>/contact" class="btn" style="background: #208836; color: #ffffff; padding: 12px 30px; border-radius: 50px; text-decoration: none;">
                            <i class="fas fa-envelope me-2"></i>Contact Us
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<style>
    /* Partners Section Styles */
    .partners-section-modern {
        position: relative;
        overflow: hidden;
    }

    .partners-section-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            radial-gradient(circle at 20% 20%, rgba(32, 136, 54, 0.05) 0%, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(32, 136, 54, 0.03) 0%, transparent 50%);
        pointer-events: none;
    }

    .partner-card-modern {
        background: #ffffff;
        border-radius: 20px;
        padding: 30px 20px;
        text-align: center;
        transition: all 0.4s ease;
        border: 1px solid rgba(32, 136, 54, 0.1);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .partner-card-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(90deg, #208836, #2ea043);
        transform: scaleX(0);
        transition: transform 0.3s ease;
    }

    .partner-card-modern:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(32, 136, 54, 0.2);
    }

    .partner-card-modern:hover::before {
        transform: scaleX(1);
    }

    .partner-logo-container {
        height: 80px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
    }

    .partner-logo {
        max-height: 80px;
        max-width: 200px;
        width: auto;
        height: auto;
        object-fit: contain;
        filter: grayscale(100%);
        transition: filter 0.3s ease;
    }

    .partner-card-modern:hover .partner-logo {
        filter: grayscale(0%);
    }

    .partner-logo-placeholder {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(32, 136, 54, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }

    .partner-content {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .partner-name {
        color: #208836;
        font-weight: 600;
        margin-bottom: 15px;
        font-size: 1.1rem;
    }

    .partner-description {
        color: #666;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 15px;
        flex-grow: 1;
    }

    .partner-type {
        margin-bottom: 15px;
    }

    .type-badge {
        background: rgba(32, 136, 54, 0.1);
        color: #208836;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .partner-link {
        margin-top: auto;
    }

    .btn-partner-link {
        color: #208836;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-partner-link:hover {
        color: #1a6b2e;
        text-decoration: none;
        transform: translateX(5px);
    }

    .no-partners-message {
        background: #ffffff;
        border-radius: 20px;
        border: 2px dashed rgba(32, 136, 54, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .partner-card-modern {
            padding: 20px 15px;
        }

        .partner-logo-container {
            height: 60px;
        }

        .partner-logo {
            max-height: 60px;
        }

        .partner-logo-placeholder {
            width: 60px;
            height: 60px;
        }
    }
</style>

<?php
// Additional JavaScript for homepage
$additional_js = [ASSETS_PATH . '/js/homepage.js'];
include 'includes/footer.php';
?>