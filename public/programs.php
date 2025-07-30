<?php

/**
 * Programs Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Get parameters
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$program_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

// If viewing single program
if ($program_id) {
    $program = $db->selectOne('programs', [
        'id' => $program_id,
        'is_active' => 1
    ]);

    if (!$program) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit;
    }

    // Page variables for single program
    $page_title = $program['title'] . ' - ECCT Programs';
    $meta_description = $program['description'] ? truncate_text(strip_tags($program['description']), 150) : 'Learn about this environmental conservation program from ECCT Tanzania.';
    $page_class = 'program-single';

    // Get related programs
    $related_programs = $db->select('programs', [
        'is_active' => 1,
        'category' => $program['category']
    ], [
        'order_by' => 'created_at DESC',
        'limit' => 3
    ]);
} else {
    // Programs listing page
    $page_title = 'Environmental Programs - ECCT';
    $meta_description = 'Discover ECCT environmental conservation programs including tree planting, wildlife protection, community education, and sustainable development initiatives across Tanzania.';
    $page_class = 'programs-listing';

    // Build conditions for programs query
    $conditions = ['is_active' => 1];
    if ($category) {
        $conditions['category'] = $category;
    }

    // Get programs with pagination
    $pagination_result = $db->paginate('programs', $page, 6, $conditions, [
        'order_by' => 'created_at DESC'
    ]);
    $programs = $pagination_result['data'];
    $pagination = $pagination_result['pagination'];

    // Get featured programs
    $featured_programs = $db->select('programs', [
        'is_active' => 1,
        'is_featured' => 1
    ], [
        'order_by' => 'created_at DESC',
        'limit' => 3
    ]);

    // Get program categories
    $categories = $db->raw(
        "SELECT category, COUNT(*) as program_count 
         FROM programs 
         WHERE is_active = 1 AND category IS NOT NULL AND category != '' 
         GROUP BY category 
         ORDER BY program_count DESC, category ASC"
    );
    $categories = $categories ? $categories->fetchAll() : [];

    // Program statistics
    $program_stats = [
        'total_programs' => $db->count('programs', ['is_active' => 1]),
        'categories' => count($categories),
        'featured' => $db->count('programs', ['is_active' => 1, 'is_featured' => 1])
    ];
}

include 'includes/header.php';
?>

<style>
    /* Programs Page Styles */
    .programs-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(32, 136, 54, 0.7)),
            url('<?php echo SITE_URL; ?>/assets/images/green-generation/IMG_3265.JPG');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: #ffffff;
    }

    .programs-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 1.5rem;
    }

    .programs-hero p {
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

    .programs-stats {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 30px;
        margin-top: 3rem;
    }

    .stat-item {
        text-align: center;
        padding: 15px;
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 800;
        color: white;
        display: block;
    }

    .stat-label {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.8);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .featured-programs {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .featured-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
        position: relative;
    }

    .featured-card:hover {
        transform: translateY(-10px);
    }

    .featured-card::before {
        content: 'FEATURED';
        position: absolute;
        top: 15px;
        right: 15px;
        background: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
    }

    .program-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .program-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .featured-card:hover .program-image img {
        transform: scale(1.05);
    }

    .program-category-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: #28a745;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .program-content {
        padding: 25px;
    }

    .program-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .program-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .program-title a:hover {
        color: #28a745;
    }

    .program-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .program-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        font-size: 0.85rem;
    }

    .program-duration {
        color: #28a745;
        font-weight: 600;
    }

    .program-location {
        color: #6c757d;
    }

    .program-status {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-upcoming {
        background: #cce7ff;
        color: #004085;
    }

    .status-completed {
        background: #f8d7da;
        color: #721c24;
    }

    .programs-content {
        padding: 80px 0;
        background: white;
    }

    .filter-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .filter-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .category-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .category-btn {
        background: white;
        border: 2px solid #e9ecef;
        color: #495057;
        padding: 8px 16px;
        border-radius: 25px;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .category-btn:hover,
    .category-btn.active {
        background: #28a745;
        border-color: #28a745;
        color: white;
        transform: translateY(-2px);
    }

    .programs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 30px;
        margin-bottom: 50px;
    }

    .program-card {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .program-card:hover {
        transform: translateY(-5px);
    }

    .program-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to bottom, rgba(0, 0, 0, 0), rgba(0, 0, 0, 0.7));
        display: flex;
        align-items: flex-end;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .program-card:hover .program-overlay {
        opacity: 1;
    }

    .overlay-content {
        color: white;
        width: 100%;
    }

    .overlay-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .overlay-description {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .single-program-hero {
        background: linear-gradient(135deg, #28a745, rgba(0, 0, 0, 0.7));
        color: white;
        padding: 80px 0;
    }

    .program-content-wrapper {
        background: white;
        border-radius: 20px;
        padding: 40px;
        margin-top: -50px;
        position: relative;
        z-index: 2;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .program-features {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px;
        margin: 30px 0;
    }

    .feature-item {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        padding: 15px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
    }

    .feature-icon {
        width: 50px;
        height: 50px;
        background: linear-gradient(135deg, #28a745, rgb(19, 117, 42));
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.2rem;
    }

    .feature-content h6 {
        margin-bottom: 5px;
        color: #2c3e50;
        font-weight: 600;
    }

    .feature-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .program-cta {
        background: linear-gradient(135deg, #28a745, rgb(19, 117, 42));
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        color: white;
        margin-top: 40px;
    }

    .cta-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .cta-description {
        margin-bottom: 25px;
        opacity: 0.9;
    }

    .cta-buttons {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .cta-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 12px 25px;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .cta-btn:hover {
        background: white;
        color: #28a745;
        transform: translateY(-2px);
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
        background: linear-gradient(135deg, #28a745, #17a2b8);
        color: white;
        padding: 8px 20px;
        border-radius: 25px;
        display: inline-block;
        margin-bottom: 20px;
        font-weight: 500;
    }

    .no-programs {
        background: white;
        border-radius: 20px;
        padding: 60px 30px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        text-align: center;
    }

    .pagination .page-link {
        border-radius: 25px;
        margin: 0 3px;
        border: none;
        padding: 8px 16px;
        color: #28a745;
    }

    .pagination .page-item.active .page-link {
        background: #28a745;
        border-color: #28a745;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .programs-hero h1 {
            font-size: 2.5rem;
        }

        .programs-hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .programs-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .filter-section {
            padding: 20px;
        }

        .category-buttons {
            justify-content: center;
        }

        .program-content {
            padding: 20px;
        }

        .program-content-wrapper {
            padding: 25px;
            margin-top: -30px;
        }

        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }

        .cta-btn {
            width: 100%;
            max-width: 250px;
        }
    }
</style>

<?php if ($program_id && $program): ?>
    <!-- Single Program View -->
    <section class="single-program-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb justify-content-center bg-transparent">
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>/programs.php" class="text-white-50">Programs</a>
                            </li>
                            <li class="breadcrumb-item active text-white" aria-current="page">
                                <?php echo htmlspecialchars(truncate_text($program['title'], 30)); ?>
                            </li>
                        </ol>
                    </nav>

                    <div class="hero-badge mb-3">
                        <?php if ($program['category']): ?>
                            <span class="badge bg-light text-dark me-2"><?php echo htmlspecialchars($program['category']); ?></span>
                        <?php endif; ?>
                        <span class="badge bg-primary">Conservation Program</span>
                    </div>

                    <h1><?php echo htmlspecialchars($program['title']); ?></h1>

                    <div class="program-meta d-flex justify-content-center flex-wrap gap-4 mt-4">
                        <?php if ($program['duration']): ?>
                            <span class="text-white-75">
                                <i class="fas fa-clock me-2"></i>
                                <?php echo htmlspecialchars($program['duration']); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($program['location']): ?>
                            <span class="text-white-75">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($program['location']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="program-content-wrapper">
                        <?php if ($program['featured_image']): ?>
                            <div class="program-image mb-4">
                                <img src="<?php echo SITE_URL; ?>/<?php echo $program['featured_image']; ?>"
                                    alt="<?php echo htmlspecialchars($program['title']); ?>"
                                    class="img-fluid rounded">
                            </div>
                        <?php endif; ?>

                        <div class="program-description">
                            <?php if ($program['description']): ?>
                                <?php echo $program['description']; ?>
                            <?php else: ?>
                                <p class="text-muted">Program details will be updated soon.</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($program['objectives'] || $program['activities'] || $program['impact']): ?>
                            <div class="program-features">
                                <h4 class="mb-4">Program Highlights</h4>

                                <?php if ($program['objectives']): ?>
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-bullseye"></i>
                                        </div>
                                        <div class="feature-content">
                                            <h6>Objectives</h6>
                                            <p><?php echo htmlspecialchars($program['objectives']); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($program['activities']): ?>
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-tasks"></i>
                                        </div>
                                        <div class="feature-content">
                                            <h6>Key Activities</h6>
                                            <p><?php echo htmlspecialchars($program['activities']); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($program['impact']): ?>
                                    <div class="feature-item">
                                        <div class="feature-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <div class="feature-content">
                                            <h6>Expected Impact</h6>
                                            <p><?php echo htmlspecialchars($program['impact']); ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="program-cta">
                            <h4 class="cta-title">Get Involved in This Program</h4>
                            <p class="cta-description">Join us in making a difference through environmental conservation and community development.</p>
                            <div class="cta-buttons">
                                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="cta-btn">
                                    <i class="fas fa-hands-helping me-2"></i>Volunteer
                                </a>
                                <a href="<?php echo SITE_URL; ?>/contact.php" class="cta-btn">
                                    <i class="fas fa-envelope me-2"></i>Learn More
                                </a>
                                <a href="<?php echo SITE_URL; ?>/campaigns.php" class="cta-btn">
                                    <i class="fas fa-donate me-2"></i>Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="program-sidebar">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Program Details</h5>
                                <?php if ($program['status']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Status</small>
                                        <span class="program-status status-<?php echo strtolower($program['status']); ?>">
                                            <?php echo htmlspecialchars($program['status']); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>

                                <?php if ($program['duration']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Duration</small>
                                        <strong><?php echo htmlspecialchars($program['duration']); ?></strong>
                                    </div>
                                <?php endif; ?>

                                <?php if ($program['location']): ?>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Location</small>
                                        <strong><?php echo htmlspecialchars($program['location']); ?></strong>
                                    </div>
                                <?php endif; ?>

                                <a href="<?php echo SITE_URL; ?>/programs.php" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-arrow-left me-2"></i>All Programs
                                </a>
                                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-primary w-100">
                                    <i class="fas fa-heart me-2"></i>Join Program
                                </a>
                            </div>
                        </div>

                        <?php if ($related_programs): ?>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Related Programs</h5>
                                    <?php foreach ($related_programs as $related): ?>
                                        <?php if ($related['id'] != $program['id']): ?>
                                            <div class="mb-3 pb-3 border-bottom">
                                                <h6 class="mb-1">
                                                    <a href="<?php echo SITE_URL; ?>/programs.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                                        <?php echo htmlspecialchars(truncate_text($related['title'], 50)); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">
                                                    <?php echo htmlspecialchars($related['category']); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>
    <!-- Programs Listing -->
    <section class="programs-hero">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="hero-badge">
                        <i class="fas fa-seedling me-2"></i>Our Programs
                    </div>
                    <h1 style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);">Conservation Programs</h1>
                    <p style="color: #ffffff; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);">Discover our comprehensive environmental conservation programs designed to protect Tanzania's natural heritage and empower local communities for sustainable development.</p>
                </div>
            </div>

            <div class="programs-stats">
                <div class="row">
                    <div class="col-md-4 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $program_stats['total_programs']; ?></span>
                            <span class="stat-label">Active Programs</span>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $program_stats['categories']; ?></span>
                            <span class="stat-label">Program Categories</span>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $program_stats['featured']; ?></span>
                            <span class="stat-label">Featured Programs</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($featured_programs): ?>
        <section class="featured-programs">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <div class="section-badge">
                            <i class="fas fa-star me-2"></i>Featured Programs
                        </div>
                        <h2 class="section-title">Signature Initiatives</h2>
                        <p class="section-subtitle">Our flagship environmental conservation programs making the biggest impact across Tanzania</p>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($featured_programs as $featured): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="featured-card" onclick="window.location.href='<?php echo SITE_URL; ?>/programs.php?id=<?php echo $featured['id']; ?>'">
                                <div class="program-image">
                                    <?php if ($featured['featured_image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/<?php echo $featured['featured_image']; ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_PATH; ?>/images/conservation-work/LUC06459.JPG" alt="Program">
                                    <?php endif; ?>

                                    <?php if ($featured['category']): ?>
                                        <div class="program-category-badge">
                                            <?php echo htmlspecialchars($featured['category']); ?>
                                        </div>
                                    <?php endif; ?>

                                    <div class="program-overlay">
                                        <div class="overlay-content">
                                            <div class="overlay-title"><?php echo htmlspecialchars($featured['title']); ?></div>
                                            <div class="overlay-description">
                                                <?php echo htmlspecialchars(truncate_text(strip_tags($featured['description'] ?? ''), 80)); ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="program-content">
                                    <h4 class="program-title">
                                        <a href="<?php echo SITE_URL; ?>/programs.php?id=<?php echo $featured['id']; ?>">
                                            <?php echo htmlspecialchars($featured['title']); ?>
                                        </a>
                                    </h4>

                                    <p class="program-description">
                                        <?php echo htmlspecialchars(truncate_text(strip_tags($featured['description'] ?? ''), 120)); ?>
                                    </p>

                                    <div class="program-meta">
                                        <?php if ($featured['duration']): ?>
                                            <span class="program-duration">
                                                <i class="fas fa-clock me-1"></i>
                                                <?php echo htmlspecialchars($featured['duration']); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ($featured['location']): ?>
                                            <span class="program-location">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($featured['location']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <?php if ($featured['status']): ?>
                                            <span class="program-status status-<?php echo strtolower($featured['status']); ?>">
                                                <?php echo htmlspecialchars($featured['status']); ?>
                                            </span>
                                        <?php endif; ?>

                                        <a href="<?php echo SITE_URL; ?>/programs.php?id=<?php echo $featured['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            Learn More <i class="fas fa-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="programs-content">
        <div class="container">
            <!-- Filters -->
            <div class="filter-section">
                <h3 class="filter-title">
                    <?php if ($category): ?>
                        <i class="fas fa-folder me-2"></i>Category: <?php echo htmlspecialchars(ucwords(str_replace('-', ' ', $category))); ?>
                    <?php else: ?>
                        <i class="fas fa-seedling me-2"></i>Browse All Programs
                    <?php endif; ?>
                </h3>

                <div class="category-buttons">
                    <a href="<?php echo SITE_URL; ?>/programs.php"
                        class="category-btn <?php echo !$category ? 'active' : ''; ?>">
                        <i class="fas fa-th me-1"></i>All Programs
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="<?php echo SITE_URL; ?>/programs.php?category=<?php echo urlencode($cat['category']); ?>"
                            class="category-btn <?php echo $category === $cat['category'] ? 'active' : ''; ?>">
                            <i class="fas fa-leaf me-1"></i>
                            <?php echo htmlspecialchars(ucwords($cat['category'])); ?>
                            <span class="badge bg-secondary ms-1"><?php echo $cat['program_count']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Programs Grid -->
            <?php if ($programs): ?>
                <div class="programs-grid">
                    <?php foreach ($programs as $program): ?>
                        <div class="program-card" onclick="window.location.href='<?php echo SITE_URL; ?>/programs.php?id=<?php echo $program['id']; ?>'">
                            <div class="program-image">
                                <?php if ($program['featured_image']): ?>
                                    <img src="<?php echo SITE_URL; ?>/<?php echo $program['featured_image']; ?>" alt="<?php echo htmlspecialchars($program['title']); ?>">
                                <?php else: ?>
                                    <img src="<?php echo ASSETS_PATH; ?>/images/conservation-work/LUC06459.JPG" alt="Program">
                                <?php endif; ?>

                                <?php if ($program['category']): ?>
                                    <div class="program-category-badge">
                                        <?php echo htmlspecialchars($program['category']); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="program-overlay">
                                    <div class="overlay-content">
                                        <div class="overlay-title"><?php echo htmlspecialchars($program['title']); ?></div>
                                        <div class="overlay-description">
                                            <?php echo htmlspecialchars(truncate_text(strip_tags($program['description'] ?? ''), 80)); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="program-content">
                                <h5 class="program-title">
                                    <a href="<?php echo SITE_URL; ?>/programs.php?id=<?php echo $program['id']; ?>">
                                        <?php echo htmlspecialchars($program['title']); ?>
                                    </a>
                                </h5>

                                <p class="program-description">
                                    <?php echo htmlspecialchars(truncate_text(strip_tags($program['description'] ?? ''), 120)); ?>
                                </p>

                                <div class="program-meta">
                                    <?php if ($program['duration']): ?>
                                        <span class="program-duration">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo htmlspecialchars($program['duration']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($program['location']): ?>
                                        <span class="program-location">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($program['location']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <?php if ($program['status']): ?>
                                        <span class="program-status status-<?php echo strtolower($program['status']); ?>">
                                            <?php echo htmlspecialchars($program['status']); ?>
                                        </span>
                                    <?php endif; ?>

                                    <a href="<?php echo SITE_URL; ?>/programs.php?id=<?php echo $program['id']; ?>" class="btn btn-primary btn-sm">
                                        Join Program
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($pagination['total_pages'] > 1): ?>
                    <nav aria-label="Programs pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] - 1); ?><?php echo ($category ? '&category=' . urlencode($category) : ''); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($i === $pagination['current_page']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo ($category ? '&category=' . urlencode($category) : ''); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] + 1); ?><?php echo ($category ? '&category=' . urlencode($category) : ''); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-programs">
                    <i class="fas fa-seedling fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">
                        <?php echo $category ? 'No programs in this category' : 'No programs available'; ?>
                    </h4>
                    <p class="text-muted mb-4">
                        <?php if ($category): ?>
                            Try browsing other categories or view all programs.
                        <?php else: ?>
                            Check back later for updates on our conservation programs.
                        <?php endif; ?>
                    </p>
                    <?php if ($category): ?>
                        <a href="<?php echo SITE_URL; ?>/programs.php" class="btn btn-primary">
                            <i class="fas fa-seedling me-2"></i>View All Programs
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>