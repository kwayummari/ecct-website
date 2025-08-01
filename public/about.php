<?php
if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', __DIR__);
}
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Fetch team members
$leadership_team = get_leadership_team();
$all_team_members = get_team_members();

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

<style>
    /* About Page Styles */
    .about-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.8), rgba(0, 0, 0, 0.6)), url('<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
    }

    .about-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 1.5rem;
    }

    .about-hero p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 2rem;
    }

    .hero-badge {
        background: #208836;
        border: 2px solid #ffffff;
        border-radius: 25px;
        padding: 8px 20px;
        display: inline-block;
        margin-bottom: 2rem;
        font-weight: 500;
        color: #ffffff;
    }

    .mission-vision {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .mission-card,
    .vision-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }

    .mission-card:hover,
    .vision-card:hover {
        transform: translateY(-10px);
    }

    .card-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(40, 167, 69, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: white;
        font-size: 3rem;
    }

    .mission-card:hover .image-overlay,
    .vision-card:hover .image-overlay {
        opacity: 1;
    }

    .card-content {
        padding: 30px;
    }

    .card-title {
        font-size: 1.8rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .card-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 20px;
    }

    .feature-list {
        list-style: none;
        padding: 0;
    }

    .feature-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        color: #6c757d;
    }

    .feature-item i {
        color: #28a745;
        margin-right: 10px;
    }

    .statistics {
        padding: 80px 0;
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(0, 0, 0, 0.7)), url('<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2674.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        transition: transform 0.3s ease;
        height: 100%;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.15);
    }

    .stat-icon {
        font-size: 3rem;
        color: #28a745;
        margin-bottom: 20px;
    }

    .stat-number {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 10px;
        color: white;
    }

    .stat-label {
        font-size: 1.1rem;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 10px;
    }

    .stat-description {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
    }

    .team {
        padding: 80px 0;
        background: white;
    }

    .team-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }

    .team-card:hover {
        transform: translateY(-10px);
    }

    .team-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .team-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .team-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(40, 167, 69, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .team-card:hover .team-overlay {
        opacity: 1;
    }

    .social-links {
        display: flex;
        gap: 15px;
    }

    .social-link {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .social-link:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
    }

    .team-content {
        padding: 25px;
    }

    .team-name {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }

    .team-role {
        color: #28a745;
        font-weight: 500;
        margin-bottom: 15px;
    }

    .team-bio {
        color: #6c757d;
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .values {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .value-card {
        background: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
    }

    .value-card:hover {
        transform: translateY(-5px);
    }

    .value-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #28a745, #20c997);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 1.8rem;
        color: white;
    }

    .value-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 15px;
    }

    .value-description {
        color: #6c757d;
        line-height: 1.6;
    }

    .cta {
        padding: 80px 0;
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(0, 0, 0, 0.7)), url('<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2829.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
        text-align: center;
    }

    .cta h2 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .cta p {
        font-size: 1.1rem;
        margin-bottom: 30px;
        opacity: 0.9;
    }

    .cta-buttons .btn {
        margin: 0 10px 10px 0;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 25px;
        transition: all 0.3s ease;
    }

    .cta-buttons .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }

    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .section-subtitle {
        font-size: 1.1rem;
        color: #6c757d;
        margin-bottom: 50px;
    }

    .section-badge {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        display: inline-block;
        margin-bottom: 20px;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .about-hero h1 {
            font-size: 2.5rem;
        }

        .about-hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .card-image,
        .team-image {
            height: 200px;
        }

        .card-content,
        .team-content {
            padding: 20px;
        }

        .cta h2 {
            font-size: 2rem;
        }

        .cta-buttons .btn {
            display: block;
            width: 100%;
            margin: 10px 0;
        }
    }
</style>

<!-- About Hero Section -->
<section class="about-hero">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <div class="hero-badge">
                    <i class="fas fa-users me-2"></i>Who We Are
                </div>
                <h1 style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);">About ECCT</h1>
                <p style="color: #ffffff; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);">Environmental Conservation Community of Tanzania - Empowering communities for sustainable environmental conservation across Tanzania</p>
                <a href="#mission-vision" class="btn btn-lg me-3" style="background: #208836; color: #ffffff; border: 2px solid #ffffff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-arrow-down me-2"></i>Learn More
                </a>
                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-lg" style="background: transparent; color: #ffffff; border: 2px solid #ffffff; padding: 12px 30px; border-radius: 50px; text-decoration: none; font-weight: 600;">
                    <i class="fas fa-heart me-2"></i>Join Us Today
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section id="mission-vision" class="mission-vision">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <div class="section-badge">
                    <i class="fas fa-compass me-2"></i>Our Purpose
                </div>
                <h2 class="section-title">Mission & Vision</h2>
                <p class="section-subtitle">Guided by purpose, driven by passion - discover the principles that fuel our environmental mission</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="mission-card">
                    <div class="card-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="ECCT Mission">
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
                        <ul class="feature-list">
                            <li class="feature-item">
                                <i class="fas fa-users"></i>
                                <span>Community-driven solutions</span>
                            </li>
                            <li class="feature-item">
                                <i class="fas fa-recycle"></i>
                                <span>Sustainable waste management</span>
                            </li>
                            <li class="feature-item">
                                <i class="fas fa-seedling"></i>
                                <span>Biodiversity conservation</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="vision-card">
                    <div class="card-image">
                        <img src="<?php echo ASSETS_PATH; ?>/images/she-lead/LUC06465.JPG" alt="ECCT Vision">
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
                        <ul class="feature-list">
                            <li class="feature-item">
                                <i class="fas fa-leaf"></i>
                                <span>Zero-waste communities</span>
                            </li>
                            <li class="feature-item">
                                <i class="fas fa-globe-africa"></i>
                                <span>Restored ecosystems</span>
                            </li>
                            <li class="feature-item">
                                <i class="fas fa-shield-alt"></i>
                                <span>Climate resilience</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Statistics Section -->
<section class="statistics">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <div class="section-badge">
                    <i class="fas fa-chart-line me-2"></i>Our Impact
                </div>
                <h2 class="section-title text-white">Making a Real Difference</h2>
                <p class="section-subtitle text-white-50">See the tangible impact we've made in communities across Tanzania</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['years_active']; ?></h3>
                    <p class="stat-label">Years of Impact</p>
                    <p class="stat-description">Consistently delivering environmental solutions</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['campaigns_completed']; ?>+</h3>
                    <p class="stat-label">Successful Campaigns</p>
                    <p class="stat-description">Community-driven environmental initiatives</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['volunteers']; ?>+</h3>
                    <p class="stat-label">Active Volunteers</p>
                    <p class="stat-description">Passionate individuals making change</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="stat-number"><?php echo $stats['communities']; ?>+</h3>
                    <p class="stat-label">Communities Reached</p>
                    <p class="stat-description">Empowered for sustainable futures</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <div class="section-badge">
                    <i class="fas fa-users me-2"></i>Our Team
                </div>
                <h2 class="section-title">Meet Our Leaders</h2>
                <p class="section-subtitle">Dedicated individuals passionate about environmental conservation and community empowerment</p>
            </div>
        </div>

        <div class="row g-4">
            <?php if (!empty($leadership_team)): ?>
                <?php foreach ($leadership_team as $member): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="team-card">
                            <div class="team-image">
                                <?php if (!empty($member['image_path']) && file_exists($member['image_path'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($member['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($member['name']); ?>">
                                <?php else: ?>
                                    <div class="team-image-placeholder">
                                        <i class="fas fa-user" style="font-size: 3rem; color: #208836;"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="team-overlay">
                                    <div class="social-links">
                                        <?php if (!empty($member['linkedin_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($member['linkedin_url']); ?>"
                                                target="_blank" rel="noopener noreferrer" class="social-link">
                                                <i class="fab fa-linkedin"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['twitter_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($member['twitter_url']); ?>"
                                                target="_blank" rel="noopener noreferrer" class="social-link">
                                                <i class="fab fa-twitter"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['facebook_url'])): ?>
                                            <a href="<?php echo htmlspecialchars($member['facebook_url']); ?>"
                                                target="_blank" rel="noopener noreferrer" class="social-link">
                                                <i class="fab fa-facebook"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (!empty($member['email'])): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>" class="social-link">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="team-content">
                                <h4 class="team-name"><?php echo htmlspecialchars($member['name']); ?></h4>
                                <p class="team-role"><?php echo htmlspecialchars($member['position']); ?></p>
                                <?php if (!empty($member['bio'])): ?>
                                    <p class="team-bio"><?php echo htmlspecialchars($member['bio']); ?></p>
                                <?php endif; ?>
                                <div class="team-department">
                                    <span class="department-badge"><?php echo ucfirst(htmlspecialchars($member['department'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <div class="no-team-message py-5">
                        <i class="fas fa-users mb-3" style="font-size: 3rem; color: #208836; opacity: 0.5;"></i>
                        <h4 style="color: #666;">Building Our Team</h4>
                        <p class="text-muted">We're assembling a dedicated team of environmental conservation professionals. Check back soon!</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($all_team_members) && count($all_team_members) > count($leadership_team)): ?>
            <!-- Extended Team Section -->
            <div class="row mt-5">
                <div class="col-12 text-center mb-4">
                    <h3 class="section-title">Our Extended Team</h3>
                    <p class="section-subtitle">Meet the dedicated professionals who make our work possible</p>
                </div>
            </div>

            <div class="row g-3">
                <?php
                $extended_team = array_filter($all_team_members, function ($member) {
                    return !$member['is_leadership'];
                });
                foreach ($extended_team as $member):
                ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="team-card-compact">
                            <div class="team-image-compact">
                                <?php if (!empty($member['image_path']) && file_exists($member['image_path'])): ?>
                                    <img src="<?php echo SITE_URL . '/' . htmlspecialchars($member['image_path']); ?>"
                                        alt="<?php echo htmlspecialchars($member['name']); ?>">
                                <?php else: ?>
                                    <div class="team-image-placeholder-compact">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="team-content-compact">
                                <h5 class="team-name-compact"><?php echo htmlspecialchars($member['name']); ?></h5>
                                <p class="team-role-compact"><?php echo htmlspecialchars($member['position']); ?></p>
                                <span class="department-badge-compact"><?php echo ucfirst(htmlspecialchars($member['department'])); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Values Section -->
<section class="values">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-12">
                <div class="section-badge">
                    <i class="fas fa-heart me-2"></i>Our Values
                </div>
                <h2 class="section-title">What Drives Us</h2>
                <p class="section-subtitle">The core principles that guide our work and shape our approach to environmental conservation</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h4 class="value-title">Collaboration</h4>
                    <p class="value-description">Working together with communities, partners, and stakeholders to achieve shared environmental goals</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h4 class="value-title">Sustainability</h4>
                    <p class="value-description">Ensuring all our initiatives create lasting positive impact for future generations</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h4 class="value-title">Innovation</h4>
                    <p class="value-description">Embracing creative solutions and new approaches to environmental challenges</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="value-title">Integrity</h4>
                    <p class="value-description">Maintaining transparency, accountability, and ethical practices in all our work</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="value-title">Empowerment</h4>
                    <p class="value-description">Building capacity and empowering communities to lead their own conservation efforts</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="value-card">
                    <div class="value-icon">
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
<section class="cta">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <h2>Join Our Mission for a Sustainable Future</h2>
                <p>Be part of the change you want to see. Together, we can create a more sustainable future for Tanzania and beyond.</p>
                <div class="cta-buttons">
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-success btn-lg">
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