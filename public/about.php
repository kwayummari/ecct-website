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

<!-- Page Header -->
<section class="page-header bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">About ECCT</h1>
                <p class="lead mb-0">
                    Empowering communities for sustainable environmental conservation across Tanzania
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-lg-end mb-0">
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">About</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision Section -->
<section class="mission-vision py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mb-5">
                <div class="mission-card h-100">
                    <div class="icon-wrapper bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i class="fas fa-bullseye fa-2x"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Our Mission</h3>
                    <p class="text-muted mb-4">
                        To empower local communities to create cleaner, greener, resilient and sustainable environments
                        through tackling global environmental pollution from plastic waste, climate change and loss of
                        biodiversity in both marine and terrestrial environments.
                    </p>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-start mb-2">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <span>Community-driven environmental solutions</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <span>Sustainable waste management practices</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-check text-success me-3 mt-1"></i>
                            <span>Biodiversity conservation initiatives</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6 mb-5">
                <div class="vision-card h-100">
                    <div class="icon-wrapper bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px;">
                        <i class="fas fa-eye fa-2x"></i>
                    </div>
                    <h3 class="h4 fw-bold mb-3">Our Vision</h3>
                    <p class="text-muted mb-4">
                        Striving for a cleaner, greener, healthier environment and thriving communities across Tanzania
                        and beyond, where environmental conservation is a way of life.
                    </p>
                    <ul class="list-unstyled">
                        <li class="d-flex align-items-start mb-2">
                            <i class="fas fa-leaf text-primary me-3 mt-1"></i>
                            <span>Zero-waste communities</span>
                        </li>
                        <li class="d-flex align-items-start mb-2">
                            <i class="fas fa-leaf text-primary me-3 mt-1"></i>
                            <span>Restored marine and terrestrial ecosystems</span>
                        </li>
                        <li class="d-flex align-items-start">
                            <i class="fas fa-leaf text-primary me-3 mt-1"></i>
                            <span>Climate-resilient communities</span>
                        </li>
                    </ul>
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
<section class="stats-section py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-12 mb-5">
                <h2 class="section-title">Our Impact</h2>
                <p class="text-muted">Making a difference in environmental conservation</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item text-center">
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-calendar-alt fa-3x"></i>
                    </div>
                    <h3 class="display-6 fw-bold text-primary mb-2"><?php echo $stats['years_active']; ?></h3>
                    <p class="text-muted mb-0">Years of Impact</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item text-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-bullhorn fa-3x"></i>
                    </div>
                    <h3 class="display-6 fw-bold text-success mb-2"><?php echo $stats['campaigns_completed']; ?>+</h3>
                    <p class="text-muted mb-0">Successful Campaigns</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item text-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-users fa-3x"></i>
                    </div>
                    <h3 class="display-6 fw-bold text-info mb-2"><?php echo $stats['volunteers']; ?>+</h3>
                    <p class="text-muted mb-0">Active Volunteers</p>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-item text-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 100px; height: 100px;">
                        <i class="fas fa-map-marker-alt fa-3x"></i>
                    </div>
                    <h3 class="display-6 fw-bold text-warning mb-2"><?php echo $stats['communities']; ?>+</h3>
                    <p class="text-muted mb-0">Communities Served</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="team-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Our Team</h2>
                <p class="text-muted">Meet the passionate individuals driving environmental change</p>
            </div>
        </div>
        <div class="row">
            <?php foreach ($team_members as $member): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="team-card card border-0 shadow-sm h-100 text-center">
                        <div class="card-body p-4">
                            <div class="team-image mb-3">
                                <img src="<?php echo ASSETS_PATH; ?>/images/team/<?php echo $member['image']; ?>"
                                    alt="<?php echo htmlspecialchars($member['name']); ?>"
                                    class="rounded-circle"
                                    style="width: 120px; height: 120px; object-fit: cover;">
                            </div>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($member['name']); ?></h5>
                            <p class="text-primary fw-medium mb-3"><?php echo htmlspecialchars($member['position']); ?></p>
                            <p class="text-muted small"><?php echo htmlspecialchars($member['bio']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Values Section -->
<section class="values-section py-5">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h2 class="section-title">Our Values</h2>
                <p class="text-muted">The principles that guide our environmental conservation work</p>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-handshake fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Collaboration</h5>
                    <p class="text-muted">
                        Working together with communities, government, and partners to achieve sustainable environmental solutions.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-seedling fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Sustainability</h5>
                    <p class="text-muted">
                        Promoting long-term environmental and social sustainability in all our conservation efforts.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon bg-info bg-opacity-10 text-info rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-lightbulb fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Innovation</h5>
                    <p class="text-muted">
                        Developing creative and innovative approaches to address environmental challenges.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon bg-warning bg-opacity-10 text-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-heart fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Integrity</h5>
                    <p class="text-muted">
                        Maintaining transparency, honesty, and accountability in all our conservation activities.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-fist-raised fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Empowerment</h5>
                    <p class="text-muted">
                        Building local capacity and empowering communities to lead their own environmental initiatives.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 mb-4">
                <div class="value-card text-center">
                    <div class="value-icon bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                        <i class="fas fa-balance-scale fa-2x"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Equity</h5>
                    <p class="text-muted">
                        Ensuring fair and equitable access to environmental resources and conservation benefits.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h3 class="mb-3">Join Our Environmental Mission</h3>
                <p class="mb-0 lead">
                    Be part of the change you want to see. Together, we can create a sustainable future for Tanzania.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-light btn-lg me-3">
                    <i class="fas fa-heart me-2"></i>Volunteer Now
                </a>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-light btn-lg">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>