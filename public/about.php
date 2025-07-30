<?php

/**
 * About Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Get about page content
$about_page = get_content_by_slug('pages', 'about');

// Page variables
$page_title = $about_page ? $about_page['title'] . ' - ECCT' : 'About Us - ECCT';
$meta_description = $about_page ? $about_page['meta_description'] : 'Learn about ECCT mission, vision and environmental conservation work in Tanzania';
$page_class = 'about-page';

// Get team members (you can create a team table or use a simple array)
$team_members = [
    [
        'name' => 'Dr. Sarah Mwangi',
        'position' => 'Executive Director',
        'bio' => 'Environmental scientist with 15+ years experience in conservation',
        'image' => 'team-1.jpg'
    ],
    [
        'name' => 'James Mkuu',
        'position' => 'Program Manager',
        'bio' => 'Community development specialist focused on sustainable practices',
        'image' => 'team-2.jpg'
    ],
    [
        'name' => 'Grace Kimani',
        'position' => 'Education Coordinator',
        'bio' => 'Environmental educator passionate about youth engagement',
        'image' => 'team-3.jpg'
    ]
];

// Get statistics for about page
$stats = [
    'years_active' => '5+',
    'campaigns_completed' => $db->count('campaigns', ['status' => 'completed']),
    'volunteers' => $db->count('volunteers', ['status' => 'active']),
    'communities' => $db->getSetting('communities_served', '25')
];

include 'includes/header.php';
?>

<!-- About Page Specific Styles -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/about.css">

<!-- About Page Header -->
<section class="about-page-header">
    <div class="about-header-bg">
        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG"
            alt="ECCT About" class="about-header-image">
        <div class="about-header-overlay"></div>
    </div>

    <div class="container position-relative">
        <div class="row">
            <div class="col-12">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb breadcrumb-custom">
                        <li class="breadcrumb-item">
                            <a href="<?php echo SITE_URL; ?>" class="text-white-50">
                                <i class="fas fa-home me-1"></i> Home
                            </a>
                        </li>
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            About Us
                        </li>
                    </ol>
                </nav>

                <!-- Header Content -->
                <div class="about-header-content text-white text-center">
                    <div class="about-header-badge mb-3">
                        <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                            <i class="fas fa-users me-2"></i>Who We Are
                        </span>
                    </div>

                    <h1 class="about-page-title mb-4">About ECCT</h1>
                    <p class="about-page-subtitle mb-0">
                        Environmental Conservation Community of Tanzania - Empowering communities
                        for sustainable environmental conservation across Tanzania
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section id="mission-vision" class="mission-vision-modern py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-compass me-2"></i>Our Purpose
                    </span>
                </div>
                <h2 class="section-title-modern mb-4">Mission & Vision</h2>
                <p class="section-subtitle">
                    Guided by purpose, driven by passion - discover the principles that fuel our environmental mission
                </p>
            </div>
        </div>

        <div class="row align-items-stretch">
            <div class="col-lg-6 mb-5">
                <div class="mission-vision-card mission-card-modern h-100">
                    <div class="mission-vision-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG"
                            alt="ECCT Mission" class="img-fluid">
                        <div class="mission-vision-overlay">
                            <div class="mission-vision-icon">
                                <i class="fas fa-bullseye"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mission-vision-content">
                        <h3 class="mission-vision-title">Our Mission</h3>
                        <p class="mission-vision-description">
                            To empower local communities to create cleaner, greener, resilient and sustainable environments
                            through tackling global environmental pollution from plastic waste, climate change and loss of
                            biodiversity in both marine and terrestrial environments.
                        </p>

                        <div class="mission-vision-features">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <span>Community-driven solutions</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-recycle"></i>
                                </div>
                                <span>Sustainable waste management</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-seedling"></i>
                                </div>
                                <span>Biodiversity conservation</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-5">
                <div class="mission-vision-card vision-card-modern h-100">
                    <div class="mission-vision-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/she-lead/LUC06465.JPG"
                            alt="ECCT Vision" class="img-fluid">
                        <div class="mission-vision-overlay">
                            <div class="mission-vision-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mission-vision-content">
                        <h3 class="mission-vision-title">Our Vision</h3>
                        <p class="mission-vision-description">
                            Striving for a cleaner, greener, healthier environment and thriving communities across Tanzania
                            and beyond, where environmental conservation is a way of life.
                        </p>

                        <div class="mission-vision-features">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-leaf"></i>
                                </div>
                                <span>Zero-waste communities</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-globe-africa"></i>
                                </div>
                                <span>Restored ecosystems</span>
                            </div>
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <span>Climate-resilient communities</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<?php if ($about_page && $about_page['content']): ?>
    <section class="about-content py-5 bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="content-wrapper">
                        <?php echo $about_page['content']; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- Statistics Section -->
<section class="stats-section-modern">
    <div class="stats-background">
        <div class="stats-overlay"></div>
        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2674.jpg"
            alt="ECCT Impact" class="stats-bg-image">
    </div>

    <div class="container position-relative">
        <div class="row text-center">
            <div class="col-12 mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-white bg-opacity-20 px-3 py-2 rounded-pill text-white">
                        <i class="fas fa-chart-line me-2"></i>Our Impact
                    </span>
                </div>
                <h2 class="section-title-modern text-white mb-4">Making a Real Difference</h2>
                <p class="section-subtitle text-white-50">
                    Measurable impact across Tanzania through community-driven environmental conservation
                </p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="100">
                <div class="stat-card-inner">
                    <div class="stat-card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number" data-count="<?php echo str_replace('+', '', $stats['years_active']); ?>"><?php echo $stats['years_active']; ?></h3>
                        <p class="stat-card-label">Years of Impact</p>
                        <div class="stat-card-description">
                            Dedicated to environmental conservation since our founding
                        </div>
                    </div>
                </div>
                <div class="stat-card-glow"></div>
            </div>

            <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="200">
                <div class="stat-card-inner">
                    <div class="stat-card-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number" data-count="<?php echo $stats['campaigns_completed']; ?>"><?php echo $stats['campaigns_completed']; ?>+</h3>
                        <p class="stat-card-label">Successful Campaigns</p>
                        <div class="stat-card-description">
                            Environmental initiatives completed successfully
                        </div>
                    </div>
                </div>
                <div class="stat-card-glow"></div>
            </div>

            <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="300">
                <div class="stat-card-inner">
                    <div class="stat-card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number" data-count="<?php echo $stats['volunteers']; ?>"><?php echo $stats['volunteers']; ?>+</h3>
                        <p class="stat-card-label">Active Volunteers</p>
                        <div class="stat-card-description">
                            Passionate individuals driving change
                        </div>
                    </div>
                </div>
                <div class="stat-card-glow"></div>
            </div>

            <div class="stat-card-modern" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-card-inner">
                    <div class="stat-card-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="stat-card-content">
                        <h3 class="stat-card-number" data-count="<?php echo $stats['communities']; ?>"><?php echo $stats['communities']; ?>+</h3>
                        <p class="stat-card-label">Communities Served</p>
                        <div class="stat-card-description">
                            Local communities empowered for sustainability
                        </div>
                    </div>
                </div>
                <div class="stat-card-glow"></div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section-modern py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <div class="section-badge mb-4">
                    <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-users me-2"></i>Our Team
                    </span>
                </div>
                <h2 class="section-title-modern mb-4">Meet Our Changemakers</h2>
                <p class="section-subtitle">
                    Passionate individuals dedicated to creating a sustainable future for Tanzania
                </p>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php foreach ($team_members as $index => $member): ?>
                <div class="col-lg-4 col-md-6 mb-5">
                    <div class="team-card-modern" data-aos="fade-up" data-aos-delay="<?php echo ($index + 1) * 100; ?>">
                        <div class="team-card-inner">
                            <div class="team-image-container">
                                <div class="team-image-wrapper">
                                    <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_<?php echo 3264 + $index; ?>.JPG"
                                        alt="<?php echo htmlspecialchars($member['name']); ?>"
                                        class="team-image">
                                    <div class="team-image-overlay">
                                        <div class="team-social">
                                            <a href="#" class="team-social-link">
                                                <i class="fab fa-linkedin"></i>
                                            </a>
                                            <a href="#" class="team-social-link">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="team-badge">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            </div>

                            <div class="team-content">
                                <h5 class="team-name"><?php echo htmlspecialchars($member['name']); ?></h5>
                                <p class="team-position"><?php echo htmlspecialchars($member['position']); ?></p>
                                <p class="team-bio"><?php echo htmlspecialchars($member['bio']); ?></p>

                                <div class="team-stats">
                                    <div class="team-stat">
                                        <span class="stat-number"><?php echo 3 + $index; ?>+</span>
                                        <span class="stat-label">Years</span>
                                    </div>
                                    <div class="team-stat">
                                        <span class="stat-number"><?php echo 15 + ($index * 5); ?>+</span>
                                        <span class="stat-label">Projects</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="team-card-glow"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <div class="join-team-cta">
                <h4 class="mb-3">Want to Join Our Team?</h4>
                <p class="text-muted mb-4">
                    We're always looking for passionate environmental advocates to join our mission
                </p>
                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-primary-modern btn-lg">
                    <i class="fas fa-handshake me-2"></i>
                    Join Our Mission
                    <i class="fas fa-arrow-right ms-2"></i>
                </a>
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
                    <span class="badge bg-primary-soft px-3 py-2 rounded-pill">
                        <i class="fas fa-heart me-2"></i>Our Values
                    </span>
                </div>
                <h2 class="section-title-modern mb-4">Principles That Guide Us</h2>
                <p class="section-subtitle">
                    The core values that drive our environmental conservation mission and community impact
                </p>
            </div>
        </div>

        <div class="values-grid">
            <div class="value-card-modern" data-aos="fade-up" data-aos-delay="100">
                <div class="value-card-inner">
                    <div class="value-icon-container">
                        <div class="value-icon-modern collaboration">
                            <i class="fas fa-handshake"></i>
                        </div>
                    </div>
                    <div class="value-content">
                        <h5 class="value-title">Collaboration</h5>
                        <p class="value-description">
                            Working together with communities, government, and partners to achieve sustainable environmental solutions.
                        </p>
                    </div>
                    <div class="value-card-bg"></div>
                </div>
            </div>

            <div class="value-card-modern" data-aos="fade-up" data-aos-delay="200">
                <div class="value-card-inner">
                    <div class="value-icon-container">
                        <div class="value-icon-modern sustainability">
                            <i class="fas fa-seedling"></i>
                        </div>
                    </div>
                    <div class="value-content">
                        <h5 class="value-title">Sustainability</h5>
                        <p class="value-description">
                            Promoting long-term environmental and social sustainability in all our conservation efforts.
                        </p>
                    </div>
                    <div class="value-card-bg"></div>
                </div>
            </div>

            <div class="value-card-modern" data-aos="fade-up" data-aos-delay="300">
                <div class="value-card-inner">
                    <div class="value-icon-container">
                        <div class="value-icon-modern innovation">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                    </div>
                    <div class="value-content">
                        <h5 class="value-title">Innovation</h5>
                        <p class="value-description">
                            Developing creative and innovative approaches to address environmental challenges.
                        </p>
                    </div>
                    <div class="value-card-bg"></div>
                </div>
            </div>

            <div class="value-card-modern" data-aos="fade-up" data-aos-delay="400">
                <div class="value-card-inner">
                    <div class="value-icon-container">
                        <div class="value-icon-modern integrity">
                            <i class="fas fa-heart"></i>
                        </div>
                    </div>
                    <div class="value-content">
                        <h5 class="value-title">Integrity</h5>
                        <p class="value-description">
                            Maintaining transparency, honesty, and accountability in all our conservation activities.
                        </p>
                    </div>
                    <div class="value-card-bg"></div>
                </div>
            </div>

            <div class="value-card-modern" data-aos="fade-up" data-aos-delay="500">
                <div class="value-card-inner">
                    <div class="value-icon-container">
                        <div class="value-icon-modern empowerment">
                            <i class="fas fa-fist-raised"></i>
                        </div>
                    </div>
                    <div class="value-content">
                        <h5 class="value-title">Empowerment</h5>
                        <p class="value-description">
                            Building local capacity and empowering communities to lead their own environmental initiatives.
                        </p>
                    </div>
                    <div class="value-card-bg"></div>
                </div>
            </div>

            <div class="value-card-modern" data-aos="fade-up" data-aos-delay="600">
                <div class="value-card-inner">
                    <div class="value-icon-container">
                        <div class="value-icon-modern equity">
                            <i class="fas fa-balance-scale"></i>
                        </div>
                    </div>
                    <div class="value-content">
                        <h5 class="value-title">Equity</h5>
                        <p class="value-description">
                            Ensuring fair and equitable access to environmental resources and conservation benefits.
                        </p>
                    </div>
                    <div class="value-card-bg"></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="about-cta-section-modern py-5 position-relative overflow-hidden">
    <div class="cta-background-about">
        <img src="<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2829.jpg"
            alt="Join ECCT" class="cta-bg-image-about">
        <div class="cta-overlay-about"></div>
    </div>

    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <div class="cta-content-about">
                    <div class="cta-badge-about mb-3">
                        <span class="badge bg-white bg-opacity-20 px-3 py-2 rounded-pill text-white">
                            <i class="fas fa-hands-helping me-2"></i>Join the Movement
                        </span>
                    </div>
                    <h3 class="cta-title-about mb-4">Ready to Make an Impact?</h3>
                    <p class="cta-description-about mb-4">
                        Be part of the change you want to see. Together, we can create a sustainable,
                        greener future for Tanzania. Your contribution matters, your voice counts.
                    </p>

                    <div class="cta-features-about">
                        <div class="cta-feature-item">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Make a real environmental impact</span>
                        </div>
                        <div class="cta-feature-item">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Connect with like-minded individuals</span>
                        </div>
                        <div class="cta-feature-item">
                            <i class="fas fa-check-circle me-2"></i>
                            <span>Develop new skills and experience</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 text-center text-lg-end">
                <div class="cta-actions-about">
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-cta-modern-about btn-lg mb-3 d-block d-lg-inline-block">
                        <i class="fas fa-heart me-2"></i>
                        <span>Volunteer Now</span>
                        <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-glass-about btn-lg d-block d-lg-inline-block">
                        <i class="fas fa-envelope me-2"></i>
                        <span>Contact Us</span>
                    </a>

                    <div class="cta-note-about mt-3">
                        <small class="text-white-50">
                            <i class="fas fa-users me-1"></i>
                            Join 500+ volunteers making a difference
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="cta-floating-elements-about">
        <div class="floating-element-about element-1">🌱</div>
        <div class="floating-element-about element-2">🌍</div>
        <div class="floating-element-about element-3">♻️</div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>