<?php
define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Initialize database
$db = new Database();

// Page meta information
$page_title = "About Us - " . $db->getSetting('site_name', 'ECCT');
$meta_description = "Learn about the Environmental Conservation Community of Tanzania (ECCT), our mission, vision, and the dedicated team working towards sustainable environmental conservation.";
$page_class = 'about-page';

// Get statistics for about page
$stats = [
    'years_active' => '5+',
    'campaigns_completed' => $db->count('campaigns', ['status' => 'completed']),
    'volunteers' => $db->count('volunteers', ['status' => 'active']),
    'communities' => $db->getSetting('communities_served', '25')
];

// Add about page specific CSS
$additional_css = [
    ASSETS_PATH . '/css/about.css'
];

include 'includes/header.php';
?>

<!-- About Hero Section -->
<section class="about-hero">
    <div class="about-hero-bg">
        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="ECCT About" class="hero-bg-image">
        <div class="hero-overlay"></div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb justify-content-center bg-transparent">
                        <li class="breadcrumb-item">
                            <a href="<?php echo SITE_URL; ?>" class="text-white-50">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-white" aria-current="page">About Us</li>
                    </ol>
                </nav>

                <div class="hero-badge mb-3">
                    <span class="badge bg-success px-3 py-2 rounded-pill">
                        <i class="fas fa-users me-2"></i>Who We Are
                    </span>
                </div>

                <h1 class="hero-title text-white mb-4">About ECCT</h1>
                <p class="hero-subtitle text-white-75 mb-0">
                    Environmental Conservation Community of Tanzania - Empowering communities
                    for sustainable environmental conservation across Tanzania
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="mission-vision py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <span class="section-badge badge bg-primary px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-compass me-2"></i>Our Purpose
                </span>
                <h2 class="section-title mb-4">Mission & Vision</h2>
                <p class="section-subtitle text-muted">
                    Guided by purpose, driven by passion - discover the principles that fuel our environmental mission
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="mission-card h-100">
                    <div class="card-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG"
                            alt="ECCT Mission" class="img-fluid">
                        <div class="image-overlay">
                            <i class="fas fa-bullseye"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Our Mission</h3>
                        <p class="card-description">
                            To empower local communities to create cleaner, greener, resilient and sustainable environments
                            through tackling global environmental pollution from plastic waste, climate change and loss of
                            biodiversity in both marine and terrestrial environments.
                        </p>
                        <div class="features-list">
                            <div class="feature-item">
                                <i class="fas fa-users text-success me-2"></i>
                                <span>Community-driven solutions</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-recycle text-success me-2"></i>
                                <span>Sustainable waste management</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-seedling text-success me-2"></i>
                                <span>Biodiversity conservation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="vision-card h-100">
                    <div class="card-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/she-lead/LUC06465.JPG"
                            alt="ECCT Vision" class="img-fluid">
                        <div class="image-overlay">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                    <div class="card-content">
                        <h3 class="card-title">Our Vision</h3>
                        <p class="card-description">
                            Striving for a cleaner, greener, healthier environment and thriving communities across Tanzania
                            and beyond, where environmental conservation is a way of life.
                        </p>
                        <div class="features-list">
                            <div class="feature-item">
                                <i class="fas fa-leaf text-success me-2"></i>
                                <span>Zero-waste communities</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-globe-africa text-success me-2"></i>
                                <span>Restored ecosystems</span>
                            </div>
                            <div class="feature-item">
                                <i class="fas fa-shield-alt text-success me-2"></i>
                                <span>Climate resilience</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="statistics py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <span class="section-badge badge bg-success px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-chart-line me-2"></i>Our Impact
                </span>
                <h2 class="section-title mb-4">Making a Difference</h2>
                <p class="section-subtitle text-muted">
                    See the tangible impact we've made in communities across Tanzania
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['years_active']; ?></h3>
                    <p class="stat-label">Years of Impact</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['campaigns_completed']; ?>+</h3>
                    <p class="stat-label">Successful Campaigns</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['volunteers']; ?>+</h3>
                    <p class="stat-label">Active Volunteers</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="stat-card text-center">
                    <div class="stat-icon mb-3">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['communities']; ?>+</h3>
                    <p class="stat-label">Communities Reached</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <span class="section-badge badge bg-info px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-users me-2"></i>Our Team
                </span>
                <h2 class="section-title mb-4">Meet Our Leaders</h2>
                <p class="section-subtitle text-muted">
                    Dedicated individuals passionate about environmental conservation and community empowerment
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="team-card text-center">
                    <div class="team-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/team/IMG_3264.JPG" alt="Team Member" class="img-fluid">
                        <div class="team-overlay">
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-content">
                        <h4 class="team-name">John Doe</h4>
                        <p class="team-role text-muted">Executive Director</p>
                        <p class="team-bio">Leading ECCT's mission with over 10 years of experience in environmental conservation.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-card text-center">
                    <div class="team-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="Team Member" class="img-fluid">
                        <div class="team-overlay">
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-content">
                        <h4 class="team-name">Jane Smith</h4>
                        <p class="team-role text-muted">Program Manager</p>
                        <p class="team-bio">Coordinating community programs and ensuring sustainable impact across all initiatives.</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="team-card text-center">
                    <div class="team-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2674.jpg" alt="Team Member" class="img-fluid">
                        <div class="team-overlay">
                            <div class="social-links">
                                <a href="#" class="social-link"><i class="fab fa-linkedin"></i></a>
                                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                                <a href="#" class="social-link"><i class="fas fa-envelope"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="team-content">
                        <h4 class="team-name">David Wilson</h4>
                        <p class="team-role text-muted">Conservation Specialist</p>
                        <p class="team-bio">Expert in biodiversity conservation and sustainable environmental practices.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <span class="section-badge badge bg-warning px-3 py-2 rounded-pill mb-3">
                    <i class="fas fa-heart me-2"></i>Our Values
                </span>
                <h2 class="section-title mb-4">What Drives Us</h2>
                <p class="section-subtitle text-muted">
                    The core principles that guide our work and shape our approach to environmental conservation
                </p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="value-card text-center h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4 class="value-title">Collaboration</h4>
                    <p class="value-description">Working together with communities, partners, and stakeholders to achieve shared environmental goals.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card text-center h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 class="value-title">Sustainability</h4>
                    <p class="value-description">Ensuring all our initiatives create lasting positive impact for future generations.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card text-center h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4 class="value-title">Innovation</h4>
                    <p class="value-description">Embracing creative solutions and new approaches to environmental challenges.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card text-center h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="value-title">Integrity</h4>
                    <p class="value-description">Maintaining transparency, accountability, and ethical practices in all our work.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card text-center h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="value-title">Empowerment</h4>
                    <p class="value-description">Building capacity and empowering communities to lead their own conservation efforts.</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card text-center h-100">
                    <div class="value-icon mb-3">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <h4 class="value-title">Equity</h4>
                    <p class="value-description">Ensuring fair access to environmental benefits and inclusive participation in conservation.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta py-5">
    <div class="cta-bg">
        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2829.jpg" alt="Join ECCT" class="cta-bg-image">
        <div class="cta-overlay"></div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="cta-title text-white mb-4">Join Our Mission</h2>
                <p class="cta-subtitle text-white-75 mb-5">
                    Be part of the change you want to see. Together, we can create a more sustainable future for Tanzania and beyond.
                </p>

                <div class="cta-buttons">
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-success btn-lg me-3">
                        <i class="fas fa-heart me-2"></i>Become a Volunteer
                    </a>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Get in Touch
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>