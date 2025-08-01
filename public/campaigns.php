<?php

/**
 * Campaigns Page for ECCT Website
 */

if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', __DIR__);
}
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Get parameters
$campaign_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// If viewing single campaign
if ($campaign_id) {
    $campaign = $db->selectOne('campaigns', ['id' => $campaign_id]);

    if (!$campaign) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit;
    }

    // Page variables for single campaign
    $page_title = $campaign['title'] . ' - ECCT Campaigns';
    $meta_description = $campaign['description'] ?: generate_meta_description($campaign['content']);
    $page_class = 'campaign-single';

    // Get related campaigns
    $related_campaigns = $db->select('campaigns', [
        'status' => ['active', 'completed'],
        'id' => array_filter([$campaign_id], function ($id) {
            return false;
        }) // Exclude current campaign
    ], [
        'order_by' => 'created_at DESC',
        'limit' => 3
    ]);

    // Get campaign tags if they exist
    $campaign_tags = $db->raw(
        "SELECT t.* FROM tags t 
         JOIN campaign_tags ct ON t.id = ct.tag_id 
         WHERE ct.campaign_id = ?",
        [$campaign_id]
    );
    $campaign_tags = $campaign_tags ? $campaign_tags->fetchAll() : [];
} else {
    // Campaigns listing page
    $page_title = 'Environmental Campaigns - ECCT';
    $meta_description = 'Discover ECCT environmental conservation campaigns and initiatives across Tanzania. Join our community-driven efforts for a sustainable future.';
    $page_class = 'campaigns-listing';

    // Build conditions for campaigns query
    $conditions = [];

    if ($status_filter && in_array($status_filter, ['planning', 'active', 'completed', 'cancelled'])) {
        $conditions['status'] = $status_filter;
    }

    if ($search) {
        // Use search functionality
        $campaign_results = $db->search('campaigns', $search, ['title', 'description', 'content'], $conditions, [
            'order_by' => 'created_at DESC'
        ]);
        $total_campaigns = count($campaign_results ?: []);
        $campaigns = $campaign_results ?: [];
    } else {
        // Regular pagination
        $pagination_result = $db->paginate('campaigns', $page, ITEMS_PER_PAGE, $conditions, [
            'order_by' => 'created_at DESC'
        ]);
        $campaigns = $pagination_result['data'];
        $pagination = $pagination_result['pagination'];
    }

    // Get featured campaigns
    $featured_campaigns = $db->select('campaigns', [
        'is_featured' => 1,
        'status' => ['active', 'completed']
    ], [
        'order_by' => 'created_at DESC',
        'limit' => 2
    ]);

    // Get campaign statistics
    $campaign_stats = [
        'total' => $db->count('campaigns'),
        'active' => $db->count('campaigns', ['status' => 'active']),
        'completed' => $db->count('campaigns', ['status' => 'completed']),
        'planning' => $db->count('campaigns', ['status' => 'planning'])
    ];
}

include 'includes/header.php';
?>

<style>
    /* Campaigns Page Styles */
    .campaigns-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(32, 136, 54, 0.7)),
            url('<?php echo SITE_URL; ?>/assets/images/she-lead/LUC06465.JPG');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: #ffffff;
    }

    .campaigns-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        margin-bottom: 1.5rem;
    }

    .campaigns-hero p {
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

    .campaign-stats {
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

    .campaigns-content {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .filter-section {
        background: white;
        border-radius: 15px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        margin-bottom: 40px;
    }

    .filter-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .campaign-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
        margin-bottom: 30px;
    }

    .campaign-card:hover {
        transform: translateY(-10px);
    }

    .campaign-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .campaign-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .campaign-card:hover .campaign-image img {
        transform: scale(1.05);
    }

    .campaign-status {
        position: absolute;
        top: 15px;
        left: 15px;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .campaign-status.active {
        background: rgba(40, 167, 69, 0.9);
        color: white;
    }

    .campaign-status.completed {
        background: rgba(0, 123, 255, 0.9);
        color: white;
    }

    .campaign-status.planning {
        background: rgba(255, 193, 7, 0.9);
        color: #212529;
    }

    .campaign-progress-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 10px 15px;
        font-size: 0.9rem;
    }

    .progress-bar-mini {
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
        overflow: hidden;
        margin-top: 5px;
    }

    .progress-fill {
        height: 100%;
        background: #28a745;
        transition: width 0.3s ease;
    }

    .campaign-content {
        padding: 25px;
    }

    .campaign-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .campaign-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .campaign-title a:hover {
        color: #28a745;
    }

    .campaign-description {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .campaign-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .campaign-meta i {
        color: #28a745;
    }

    .campaign-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .featured-campaigns {
        padding: 80px 0;
        background: white;
    }

    .featured-card {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.05), rgba(0, 123, 255, 0.05));
        border: 2px solid #28a745;
        border-radius: 15px;
        padding: 30px;
        height: 100%;
        transition: transform 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .featured-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(40, 167, 69, 0.1), transparent);
        transform: rotate(45deg);
        transition: transform 0.6s ease;
    }

    .featured-card:hover::before {
        transform: translateX(100%) rotate(45deg);
    }

    .featured-card:hover {
        transform: translateY(-5px);
    }

    .featured-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        background: #28a745;
        color: white;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .single-campaign-hero {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.9), rgba(0, 0, 0, 0.7));
        color: white;
        padding: 80px 0;
    }

    .campaign-single-content {
        background: white;
        border-radius: 15px;
        padding: 40px;
        margin-top: -50px;
        position: relative;
        z-index: 2;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .progress-section {
        background: linear-gradient(135deg, #28a745, #20c997);
        color: white;
        border-radius: 15px;
        padding: 30px;
        margin: 30px 0;
    }

    .progress-bar-large {
        height: 20px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 10px;
        overflow: hidden;
        margin: 15px 0;
    }

    .progress-fill-large {
        height: 100%;
        background: white;
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    .cta-section {
        background: linear-gradient(135deg, rgba(44, 62, 80, 0.9), rgba(0, 0, 0, 0.7)), url('<?php echo ASSETS_PATH; ?>/images/eco-wear/_DSC2829.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
        padding: 80px 0;
        text-align: center;
    }

    .cta-section h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 20px;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    }

    .cta-section .btn {
        margin: 0 10px 10px 0;
        padding: 12px 30px;
        font-weight: 600;
        border-radius: 25px;
        transition: all 0.3s ease;
    }

    .cta-section .btn:hover {
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

    .no-results {
        background: white;
        border-radius: 15px;
        padding: 60px 30px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
        .campaigns-hero h1 {
            font-size: 2.5rem;
        }

        .campaigns-hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .campaign-image {
            height: 200px;
        }

        .campaign-content {
            padding: 20px;
        }

        .campaign-actions {
            flex-direction: column;
        }

        .cta-section h3 {
            font-size: 2rem;
        }

        .cta-section .btn {
            display: block;
            width: 100%;
            margin: 10px 0;
        }

        .stat-number {
            font-size: 1.5rem;
        }
    }
</style>

<?php if ($campaign_id && $campaign): ?>
    <!-- Single Campaign View -->
    <section class="single-campaign-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb justify-content-center bg-transparent">
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>/campaigns.php" class="text-white-50">Campaigns</a>
                            </li>
                            <li class="breadcrumb-item active text-white" aria-current="page">
                                <?php echo htmlspecialchars(truncate_text($campaign['title'], 30)); ?>
                            </li>
                        </ol>
                    </nav>

                    <div class="hero-badge mb-3">
                        <span class="badge bg-<?php echo match ($campaign['status']) {
                                                    'active' => 'success',
                                                    'completed' => 'primary',
                                                    'planning' => 'warning',
                                                    'cancelled' => 'danger',
                                                    default => 'secondary'
                                                }; ?>">
                            <?php echo ucfirst($campaign['status']); ?>
                        </span>
                        <?php if ($campaign['is_featured']): ?>
                            <span class="badge bg-warning text-dark ms-2">Featured</span>
                        <?php endif; ?>
                    </div>

                    <h1><?php echo htmlspecialchars($campaign['title']); ?></h1>
                    <?php if ($campaign['description']): ?>
                        <p><?php echo htmlspecialchars($campaign['description']); ?></p>
                    <?php endif; ?>

                    <div class="campaign-meta justify-content-center d-flex flex-wrap gap-4 mt-4">
                        <?php if ($campaign['location']): ?>
                            <span class="text-white-75">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($campaign['location']); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ($campaign['start_date']): ?>
                            <span class="text-white-75">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo format_date($campaign['start_date']); ?>
                                <?php if ($campaign['end_date']): ?>
                                    - <?php echo format_date($campaign['end_date']); ?>
                                <?php endif; ?>
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
                    <div class="campaign-single-content">
                        <?php if ($campaign['goal_amount'] > 0): ?>
                            <div class="progress-section">
                                <h4 class="mb-3">Campaign Progress</h4>
                                <?php
                                $progress_percentage = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                $progress_percentage = min(100, $progress_percentage);
                                ?>
                                <div class="progress-bar-large">
                                    <div class="progress-fill-large" style="width: <?php echo $progress_percentage; ?>%"></div>
                                </div>
                                <div class="d-flex justify-content-between mt-3">
                                    <span>Raised: $<?php echo number_format($campaign['raised_amount'], 2); ?></span>
                                    <span>Goal: $<?php echo number_format($campaign['goal_amount'], 2); ?></span>
                                </div>
                                <div class="text-center mt-3">
                                    <h3><?php echo round($progress_percentage); ?>% Complete</h3>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="campaign-content-full">
                            <?php if ($campaign['content']): ?>
                                <?php echo $campaign['content']; ?>
                            <?php else: ?>
                                <p class="text-muted">Full campaign details will be available soon.</p>
                            <?php endif; ?>
                        </div>

                        <?php if ($campaign_tags): ?>
                            <div class="campaign-tags mt-4">
                                <h6>Tags:</h6>
                                <?php foreach ($campaign_tags as $tag): ?>
                                    <span class="badge me-2" style="background-color: <?php echo $tag['color']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="campaign-sidebar">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Take Action</h5>
                                <p class="card-text">Join this campaign and make a difference in environmental conservation.</p>
                                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-success btn-lg w-100 mb-2">
                                    <i class="fas fa-hands-helping me-2"></i>Volunteer
                                </a>
                                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-envelope me-2"></i>Learn More
                                </a>
                            </div>
                        </div>

                        <?php if ($related_campaigns): ?>
                            <div class="card border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title">Related Campaigns</h5>
                                    <?php foreach ($related_campaigns as $related): ?>
                                        <div class="mb-3 pb-3 border-bottom">
                                            <h6 class="mb-1">
                                                <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars($related['title']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars(truncate_text($related['description'], 80)); ?>
                                            </small>
                                        </div>
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
    <!-- Campaigns Listing -->
    <section class="campaigns-hero">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="hero-badge">
                        <i class="fas fa-bullhorn me-2"></i>Our Campaigns
                    </div>
                    <h1 style="color: #ffffff; text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);">Environmental Conservation Campaigns</h1>
                    <p style="color: #ffffff; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.6);">Join our community-driven initiatives to protect Tanzania's environment and build sustainable communities for future generations.</p>
                </div>
            </div>

            <div class="campaign-stats">
                <div class="row">
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $campaign_stats['total']; ?></span>
                            <span class="stat-label">Total Campaigns</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $campaign_stats['active']; ?></span>
                            <span class="stat-label">Active</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $campaign_stats['completed']; ?></span>
                            <span class="stat-label">Completed</span>
                        </div>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo $campaign_stats['planning']; ?></span>
                            <span class="stat-label">In Planning</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($featured_campaigns): ?>
        <section class="featured-campaigns">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <div class="section-badge">
                            <i class="fas fa-star me-2"></i>Featured Campaigns
                        </div>
                        <h2 class="section-title">Spotlight Initiatives</h2>
                        <p class="section-subtitle">Discover our most impactful environmental conservation campaigns making a difference across Tanzania</p>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($featured_campaigns as $featured): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="featured-card">
                                <div class="featured-badge">Featured</div>
                                <h4 class="mb-3">
                                    <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $featured['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($featured['title']); ?>
                                    </a>
                                </h4>
                                <p class="text-muted mb-3">
                                    <?php echo htmlspecialchars(truncate_text($featured['description'], 120)); ?>
                                </p>

                                <?php if ($featured['goal_amount'] > 0): ?>
                                    <?php
                                    $progress = ($featured['raised_amount'] / $featured['goal_amount']) * 100;
                                    $progress = min(100, $progress);
                                    ?>
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between small text-muted mb-1">
                                            <span>Progress: <?php echo round($progress); ?>%</span>
                                            <span>$<?php echo number_format($featured['raised_amount']); ?> / $<?php echo number_format($featured['goal_amount']); ?></span>
                                        </div>
                                        <div class="progress-bar-mini">
                                            <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-<?php echo match ($featured['status']) {
                                                                'active' => 'success',
                                                                'completed' => 'primary',
                                                                'planning' => 'warning',
                                                                default => 'secondary'
                                                            }; ?>">
                                        <?php echo ucfirst($featured['status']); ?>
                                    </span>
                                    <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $featured['id']; ?>" class="btn btn-primary">
                                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <section class="campaigns-content">
        <div class="container">
            <!-- Filters -->
            <div class="filter-section">
                <h3 class="filter-title">Find Campaigns</h3>
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                            value="<?php echo htmlspecialchars($search); ?>" placeholder="Search campaigns...">
                    </div>
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="planning" <?php echo ($status_filter === 'planning') ? 'selected' : ''; ?>>Planning</option>
                            <option value="active" <?php echo ($status_filter === 'active') ? 'selected' : ''; ?>>Active</option>
                            <option value="completed" <?php echo ($status_filter === 'completed') ? 'selected' : ''; ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <a href="<?php echo SITE_URL; ?>/campaigns.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <?php if ($campaigns): ?>
                <div class="row">
                    <?php foreach ($campaigns as $campaign): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="campaign-card">
                                <div class="campaign-image">
                                    <?php if ($campaign['image']): ?>
                                        <img src="<?php echo SITE_URL . '/' . $campaign['image']; ?>" alt="<?php echo htmlspecialchars($campaign['title']); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="Campaign">
                                    <?php endif; ?>

                                    <div class="campaign-status <?php echo $campaign['status']; ?>">
                                        <?php echo ucfirst($campaign['status']); ?>
                                    </div>

                                    <?php if ($campaign['goal_amount'] > 0): ?>
                                        <div class="campaign-progress-overlay">
                                            <?php
                                            $progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                            $progress = min(100, $progress);
                                            ?>
                                            <span><?php echo round($progress); ?>% funded</span>
                                            <div class="progress-bar-mini">
                                                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="campaign-content">
                                    <h5 class="campaign-title">
                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>">
                                            <?php echo htmlspecialchars($campaign['title']); ?>
                                        </a>
                                    </h5>

                                    <p class="campaign-description">
                                        <?php echo htmlspecialchars(truncate_text($campaign['description'], 100)); ?>
                                    </p>

                                    <div class="campaign-meta">
                                        <?php if ($campaign['location']): ?>
                                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($campaign['location']); ?></span>
                                        <?php endif; ?>

                                        <?php if ($campaign['start_date']): ?>
                                            <span><i class="fas fa-calendar"></i> <?php echo format_date($campaign['start_date']); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="campaign-actions">
                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>" class="btn btn-primary">
                                            View Details
                                        </a>
                                        <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-outline-success">
                                            Join Campaign
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Campaigns pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] - 1); ?><?php echo ($status_filter ? '&status=' . $status_filter : ''); ?><?php echo ($search ? '&search=' . urlencode($search) : ''); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($i === $pagination['current_page']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo ($status_filter ? '&status=' . $status_filter : ''); ?><?php echo ($search ? '&search=' . urlencode($search) : ''); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] + 1); ?><?php echo ($status_filter ? '&status=' . $status_filter : ''); ?><?php echo ($search ? '&search=' . urlencode($search) : ''); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-bullhorn fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">
                        <?php echo ($search || $status_filter) ? 'No campaigns found' : 'No campaigns available'; ?>
                    </h4>
                    <p class="text-muted mb-4">
                        <?php if ($search || $status_filter): ?>
                            Try adjusting your search terms or filters, or browse all campaigns.
                        <?php else: ?>
                            Check back later for new environmental conservation campaigns.
                        <?php endif; ?>
                    </p>
                    <?php if ($search || $status_filter): ?>
                        <a href="<?php echo SITE_URL; ?>/campaigns.php" class="btn btn-primary">
                            <i class="fas fa-list me-2"></i>Browse All Campaigns
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h3>Ready to Make an Impact?</h3>
                    <p>Join our environmental conservation efforts and help create positive change in Tanzania's communities.</p>
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-light btn-lg">
                        <i class="fas fa-hands-helping me-2"></i>Volunteer Now
                    </a>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-outline-light btn-lg">
                        <i class="fas fa-envelope me-2"></i>Contact Us
                    </a>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>