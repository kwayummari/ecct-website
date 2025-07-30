<?php
define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Page variables
$page_title = "About Us - " . $db->getSetting('site_name', SITE_NAME);
$meta_description = "Learn about the Environmental Conservation Community of Tanzania (ECCT), our mission, vision, and the dedicated team working towards sustainable environmental conservation.";
$page_class = 'about-page';

// Get statistics for about page
$stats = [
    'years_active' => '5+',
    'campaigns_completed' => $db->count('campaigns', ['status' => 'completed']),
    'volunteers' => $db->count('volunteers', ['status' => 'active']),
    'communities' => $db->getSetting('communities_served', '25')
];

include 'includes/header.php';
?>

<!-- About Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Background Image -->
    <div class="hero-video-background">
        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="ECCT About" class="hero-fallback-image">
        <div class="hero-overlay-modern"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-8">
                <div class="hero-content-modern text-white">
                    <div class="hero-badge mb-4 animate-fade-in">
                        <span class="badge-pill bg-success-gradient text-white px-4 py-2 rounded-pill">
                            <i class="fas fa-users me-2"></i>Who We Are
                        </span>
                    </div>
                    <h1 class="hero-title-modern fw-bold mb-4 animate-fade-in text-white">
                        About ECCT
                    </h1>
                    <p class="hero-subtitle-modern mb-5 animate-fade-in-delay">
                        Environmental Conservation Community of Tanzania - Empowering communities
                        for sustainable environmental conservation across Tanzania.
                    </p>
                    <div class="hero-buttons-modern animate-fade-in-delay-2">
                        <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-success-modern btn-lg me-3">
                            <i class="fas fa-heart me-2"></i>Join Us Today
                        </a>
                        <a href="#mission-vision" class="btn btn-glass btn-lg">
                            <i class="fas fa-arrow-down me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="hero-stats-modern animate-slide-up">
                    <div class="stats-grid">
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number"><?php echo $stats['years_active']; ?></h3>
                                <p class="stat-label">Years of Impact</p>
                            </div>
                        </div>
                        <div class="stat-card-modern">
                            <div class="stat-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="stat-content">
                                <h3 class="stat-number"><?php echo $stats['communities']; ?>+</h3>
                                <p class="stat-label">Communities Reached</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator-modern position-absolute bottom-0 start-50 translate-middle-x pb-4">
            <div class="scroll-arrow-modern text-white text-center">
                <div class="scroll-mouse">
                    <div class="scroll-wheel"></div>
                </div>
                <p class="small mt-3 mb-0 text-uppercase tracking-wide">Discover Our Story</p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section id="mission-vision" class="mission-section-modern py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="mission-content-modern">
                    <div class="section-badge mb-4">
                        <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                            <i class="fas fa-bullseye me-2"></i>Our Mission
                        </span>
                    </div>
                    <h2 class="section-title-modern mb-4">Empowering Communities for Sustainable Environment</h2>
                    <p class="mission-description mb-5">
                        To empower local communities to create cleaner, greener, resilient and sustainable environments
                        through tackling global environmental pollution from plastic waste, climate change and loss of
                        biodiversity in both marine and terrestrial environments.
                    </p>
                    <div class="mission-points-modern">
                        <div class="mission-point-card mb-4">
                            <div class="point-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="point-content">
                                <h5 class="point-title mb-2">Community-Driven Solutions</h5>
                                <p class="point-description mb-0">Building local capacity for environmental stewardship and sustainable development</p>
                            </div>
                        </div>
                        <div class="mission-point-card mb-4">
                            <div class="point-icon">
                                <i class="fas fa-recycle"></i>
                            </div>
                            <div class="point-content">
                                <h5 class="point-title mb-2">Sustainable Waste Management</h5>
                                <p class="point-description mb-0">Innovative solutions for plastic waste reduction and circular economy</p>
                            </div>
                        </div>
                        <div class="mission-point-card">
                            <div class="point-icon">
                                <i class="fas fa-seedling"></i>
                            </div>
                            <div class="point-content">
                                <h5 class="point-title mb-2">Biodiversity Conservation</h5>
                                <p class="point-description mb-0">Protecting Tanzania's natural ecosystems and marine environments</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mission-image-modern">
                    <div class="image-container">
                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG"
                            alt="ECCT Mission" class="img-fluid mission-img">
                        <div class="image-overlay">
                            <div class="overlay-content">
                                <i class="fas fa-bullseye fa-3x"></i>
                                <p class="mt-3 mb-0">Our Mission in Action</p>
                            </div>
                        </div>
                    </div>
                    <div class="floating-stats">
                        <div class="floating-stat">
                            <span class="stat-value">100%</span>
                            <span class="stat-text">Community Focused</span>
                        </div>
                        <div class="floating-stat">
                            <span class="stat-value">15+</span>
                            <span class="stat-text">Active Programs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Vision Section -->
<section class="campaigns-section-modern py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-success-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-eye me-2"></i>Our Vision
                    </span>
                </div>
                <h2 class="section-title-modern mb-4">Our Vision for the Future</h2>
                <p class="section-subtitle mb-5">
                    Striving for a cleaner, greener, healthier environment and thriving communities across Tanzania
                    and beyond, where environmental conservation is a way of life.
                </p>
            </div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <div class="mission-image-modern">
                    <div class="image-container">
                        <img src="<?php echo ASSETS_PATH; ?>/images/she-lead/LUC06465.JPG"
                            alt="ECCT Vision" class="img-fluid mission-img">
                        <div class="image-overlay">
                            <div class="overlay-content">
                                <i class="fas fa-eye fa-3x"></i>
                                <p class="mt-3 mb-0">Our Vision for Tomorrow</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="mission-points-modern">
                    <div class="mission-point-card mb-4">
                        <div class="point-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="point-content">
                            <h5 class="point-title mb-2">Zero-Waste Communities</h5>
                            <p class="point-description mb-0">Creating communities that eliminate waste through sustainable practices</p>
                        </div>
                    </div>
                    <div class="mission-point-card mb-4">
                        <div class="point-icon">
                            <i class="fas fa-globe-africa"></i>
                        </div>
                        <div class="point-content">
                            <h5 class="point-title mb-2">Restored Ecosystems</h5>
                            <p class="point-description mb-0">Revitalizing degraded environments and protecting biodiversity</p>
                        </div>
                    </div>
                    <div class="mission-point-card">
                        <div class="point-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="point-content">
                            <h5 class="point-title mb-2">Climate Resilience</h5>
                            <p class="point-description mb-0">Building adaptive capacity to address climate change impacts</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="stats-section-modern py-5">
    <div class="stats-background">
        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2674.jpg" alt="ECCT Impact" class="stats-bg-image">
        <div class="stats-overlay"></div>
    </div>

    <div class="container position-relative">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-warning-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-chart-line me-2"></i>Our Impact
                    </span>
                </div>
                <h2 class="section-title-modern text-white mb-4">Making a Real Difference</h2>
                <p class="section-subtitle text-white-75">
                    See the tangible impact we've made in communities across Tanzania
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card-modern text-center">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['years_active']; ?></h3>
                        <p class="stat-label">Years of Impact</p>
                        <p class="stat-description">Consistently delivering environmental solutions</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card-modern text-center">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['campaigns_completed']; ?>+</h3>
                        <p class="stat-label">Successful Campaigns</p>
                        <p class="stat-description">Community-driven environmental initiatives</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card-modern text-center">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['volunteers']; ?>+</h3>
                        <p class="stat-label">Active Volunteers</p>
                        <p class="stat-description">Passionate individuals making change</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card-modern text-center">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?php echo $stats['communities']; ?>+</h3>
                        <p class="stat-label">Communities Reached</p>
                        <p class="stat-description">Empowered for sustainable futures</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section-modern py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-info-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-users me-2"></i>Our Team
                    </span>
                </div>
                <h2 class="section-title-modern mb-4">Meet Our Leaders</h2>
                <p class="section-subtitle">
                    Dedicated individuals passionate about environmental conservation and community empowerment
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-5">
                <div class="team-card-modern">
                    <div class="team-image-container">
                        <img src="<?php echo ASSETS_PATH; ?>/images/team/IMG_3264.JPG" alt="Team Member" class="team-image">
                        <div class="team-overlay">
                            <div class="team-social">
                                <a href="#" class="team-social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="team-social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="team-social-link"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-content">
                        <div class="team-badge mb-2">
                            <span class="badge bg-success-soft">Leadership</span>
                        </div>
                        <h4 class="team-name">John Doe</h4>
                        <p class="team-role">Executive Director</p>
                        <p class="team-bio">Leading ECCT's mission with over 10 years of experience in environmental conservation and community development.</p>
                        <div class="team-stats">
                            <div class="team-stat">
                                <span class="stat-value">10+</span>
                                <span class="stat-label">Years Experience</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="team-card-modern">
                    <div class="team-image-container">
                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="Team Member" class="team-image">
                        <div class="team-overlay">
                            <div class="team-social">
                                <a href="#" class="team-social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="team-social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="team-social-link"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-content">
                        <div class="team-badge mb-2">
                            <span class="badge bg-primary-soft">Programs</span>
                        </div>
                        <h4 class="team-name">Jane Smith</h4>
                        <p class="team-role">Program Manager</p>
                        <p class="team-bio">Coordinating community programs and ensuring sustainable impact across all environmental initiatives.</p>
                        <div class="team-stats">
                            <div class="team-stat">
                                <span class="stat-value">15+</span>
                                <span class="stat-label">Programs Led</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="team-card-modern">
                    <div class="team-image-container">
                        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2674.jpg" alt="Team Member" class="team-image">
                        <div class="team-overlay">
                            <div class="team-social">
                                <a href="#" class="team-social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="team-social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="team-social-link"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-content">
                        <div class="team-badge mb-2">
                            <span class="badge bg-warning-soft">Conservation</span>
                        </div>
                        <h4 class="team-name">David Wilson</h4>
                        <p class="team-role">Conservation Specialist</p>
                        <p class="team-bio">Expert in biodiversity conservation and sustainable environmental practices with field experience.</p>
                        <div class="team-stats">
                            <div class="team-stat">
                                <span class="stat-value">8+</span>
                                <span class="stat-label">Years Field Work</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center">
                <div class="team-cta">
                    <h4 class="team-cta-title">Join Our Team</h4>
                    <p class="team-cta-description">Passionate about environmental conservation? We're always looking for dedicated individuals.</p>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-success-modern btn-lg">
                        <i class="fas fa-envelope me-2"></i>Get in Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section-modern py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-warning-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-heart me-2"></i>Our Values
                    </span>
                </div>
                <h2 class="section-title-modern mb-4">What Drives Us</h2>
                <p class="section-subtitle">
                    The core principles that guide our work and shape our approach to environmental conservation
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 col-md-6 mb-5">
                <div class="value-card-modern text-center">
                    <div class="value-icon-modern collaboration">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4 class="value-title">Collaboration</h4>
                    <p class="value-description">Working together with communities, partners, and stakeholders to achieve shared environmental goals</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="value-card-modern text-center">
                    <div class="value-icon-modern sustainability">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 class="value-title">Sustainability</h4>
                    <p class="value-description">Ensuring all our initiatives create lasting positive impact for future generations</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="value-card-modern text-center">
                    <div class="value-icon-modern innovation">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4 class="value-title">Innovation</h4>
                    <p class="value-description">Embracing creative solutions and new approaches to environmental challenges</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="value-card-modern text-center">
                    <div class="value-icon-modern integrity">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="value-title">Integrity</h4>
                    <p class="value-description">Maintaining transparency, accountability, and ethical practices in all our work</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="value-card-modern text-center">
                    <div class="value-icon-modern empowerment">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="value-title">Empowerment</h4>
                    <p class="value-description">Building capacity and empowering communities to lead their own conservation efforts</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-5">
                <div class="value-card-modern text-center">
                    <div class="value-icon-modern equity">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4 class="value-title">Equity</h4>
                    <p class="value-description">Ensuring fair access to environmental benefits and inclusive participation in conservation</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="about-cta-section-modern py-5">
    <div class="cta-background-about">
        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2829.jpg" alt="Join ECCT" class="cta-bg-image">
        <div class="cta-overlay-about"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="cta-content-about text-white">
                    <h2 class="cta-title-about mb-4">Join Our Mission for a Sustainable Future</h2>
                    <p class="cta-description-about mb-5">
                        Be part of the change you want to see. Together, we can create a more sustainable future for Tanzania and beyond.
                        Your support helps us empower communities and protect our environment.
                    </p>

                    <div class="cta-features-about mb-5">
                        <div class="feature-highlight">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Make a real environmental impact</span>
                        </div>
                        <div class="feature-highlight">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Connect with like-minded individuals</span>
                        </div>
                        <div class="feature-highlight">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Develop valuable skills and experience</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="cta-actions-about text-center">
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-cta-modern-about btn-lg mb-3">
                        <i class="fas fa-heart me-2"></i>Become a Volunteer
                    </a>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-glass-about btn-lg">
                        <i class="fas fa-envelope me-2"></i>Get in Touch
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Elements -->
    <div class="floating-element-about" style="top: 20%; left: 10%;">🌱</div>
    <div class="floating-element-about" style="top: 60%; right: 15%;">🌍</div>
    <div class="floating-element-about" style="bottom: 30%; left: 5%;">♻️</div>
</section>

<?php include 'includes/footer.php'; ?>