<?php

/**
 * Campaigns Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
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

<?php if ($campaign_id && $campaign): ?>
    <!-- Single Campaign -->
    <article class="campaign-single">
        <!-- Campaign Header -->
        <section class="campaign-header bg-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/campaigns.php">Campaigns</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?php echo htmlspecialchars(truncate_text($campaign['title'], 50)); ?>
                                </li>
                            </ol>
                        </nav>

                        <h1 class="display-5 fw-bold mb-4"><?php echo htmlspecialchars($campaign['title']); ?></h1>

                        <div class="campaign-meta d-flex flex-wrap align-items-center mb-4">
                            <span class="badge bg-<?php echo match ($campaign['status']) {
                                                        'active' => 'success',
                                                        'completed' => 'primary',
                                                        'planning' => 'warning',
                                                        'cancelled' => 'danger',
                                                        default => 'secondary'
                                                    }; ?> me-3">
                                <?php echo ucfirst($campaign['status']); ?>
                            </span>

                            <?php if ($campaign['location']): ?>
                                <span class="text-muted me-4">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($campaign['location']); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($campaign['start_date']): ?>
                                <span class="text-muted me-4">
                                    <i class="fas fa-calendar me-2"></i>
                                    <?php echo format_date($campaign['start_date']); ?>
                                    <?php if ($campaign['end_date']): ?>
                                        - <?php echo format_date($campaign['end_date']); ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($campaign['is_featured']): ?>
                                <span class="badge bg-warning text-dark">Featured</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($campaign['description']): ?>
                            <p class="lead text-muted mb-4"><?php echo htmlspecialchars($campaign['description']); ?></p>
                        <?php endif; ?>

                        <?php if ($campaign_tags): ?>
                            <div class="campaign-tags mb-4">
                                <?php foreach ($campaign_tags as $tag): ?>
                                    <span class="badge rounded-pill me-2" style="background-color: <?php echo $tag['color']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Campaign Progress -->
                        <?php if ($campaign['goal_amount'] > 0): ?>
                            <div class="campaign-progress card border-0 shadow-sm p-4 mb-4">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-2">Campaign Progress</h6>
                                        <div class="progress mb-2" style="height: 10px;">
                                            <?php
                                            $progress_percentage = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                            $progress_percentage = min(100, $progress_percentage);
                                            ?>
                                            <div class="progress-bar bg-success" role="progressbar"
                                                style="width: <?php echo $progress_percentage; ?>%"
                                                aria-valuenow="<?php echo $progress_percentage; ?>"
                                                aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <small class="text-muted">
                                                Raised: $<?php echo number_format($campaign['raised_amount'], 2); ?>
                                            </small>
                                            <small class="text-muted">
                                                Goal: $<?php echo number_format($campaign['goal_amount'], 2); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-center mt-3 mt-md-0">
                                        <h4 class="text-success mb-1"><?php echo round($progress_percentage); ?>%</h4>
                                        <small class="text-muted">Complete</small>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="campaign-actions">
                            <?php if ($campaign['status'] === 'active'): ?>
                                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-primary btn-lg me-3">
                                    <i class="fas fa-hands-helping me-2"></i>
                                    Join This Campaign
                                </a>
                            <?php endif; ?>

                            <a href="<?php echo SITE_URL; ?>/contact.php?subject=Campaign: <?php echo urlencode($campaign['title']); ?>"
                                class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-envelope me-2"></i>
                                Get Involved
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Campaign Content -->
        <section class="campaign-content py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <?php if ($campaign['featured_image']): ?>
                            <div class="campaign-image mb-5">
                                <img src="<?php echo UPLOADS_URL . '/campaigns/' . $campaign['featured_image']; ?>"
                                    alt="<?php echo htmlspecialchars($campaign['title']); ?>"
                                    class="img-fluid rounded shadow">
                            </div>
                        <?php endif; ?>

                        <div class="campaign-body">
                            <?php echo $campaign['content']; ?>
                        </div>

                        <!-- Share Campaign -->
                        <div class="share-campaign mt-5 pt-5 border-top">
                            <h5 class="mb-3">Share This Campaign</h5>
                            <?php
                            $share_links = get_share_links(
                                current_url(),
                                $campaign['title'],
                                $campaign['description'] ?: generate_meta_description($campaign['content'])
                            );
                            ?>
                            <div class="d-flex flex-wrap">
                                <a href="<?php echo $share_links['facebook']; ?>" target="_blank"
                                    class="btn btn-outline-primary me-2 mb-2">
                                    <i class="fab fa-facebook-f me-2"></i>Facebook
                                </a>
                                <a href="<?php echo $share_links['twitter']; ?>" target="_blank"
                                    class="btn btn-outline-info me-2 mb-2">
                                    <i class="fab fa-twitter me-2"></i>Twitter
                                </a>
                                <a href="<?php echo $share_links['whatsapp']; ?>" target="_blank"
                                    class="btn btn-outline-success me-2 mb-2">
                                    <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                </a>
                                <a href="<?php echo $share_links['email']; ?>"
                                    class="btn btn-outline-secondary mb-2">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Campaigns -->
        <?php if ($related_campaigns): ?>
            <section class="related-campaigns py-5 bg-light">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="h4 fw-bold mb-4">Related Campaigns</h3>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($related_campaigns as $related): ?>
                            <div class="col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <?php if ($related['featured_image']): ?>
                                        <img src="<?php echo UPLOADS_URL . '/campaigns/' . $related['featured_image']; ?>"
                                            class="card-img-top" alt="<?php echo htmlspecialchars($related['title']); ?>"
                                            style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-<?php echo match ($related['status']) {
                                                                        'active' => 'success',
                                                                        'completed' => 'primary',
                                                                        'planning' => 'warning',
                                                                        default => 'secondary'
                                                                    }; ?> me-2">
                                                <?php echo ucfirst($related['status']); ?>
                                            </span>
                                            <?php if ($related['location']): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($related['location']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <h6 class="card-title">
                                            <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $related['id']; ?>"
                                                class="text-decoration-none">
                                                <?php echo htmlspecialchars($related['title']); ?>
                                            </a>
                                        </h6>
                                        <p class="card-text text-muted flex-grow-1">
                                            <?php echo htmlspecialchars(truncate_text($related['description'], 100)); ?>
                                        </p>
                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $related['id']; ?>"
                                            class="btn btn-outline-primary btn-sm mt-auto">
                                            Learn More
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>
    </article>

<?php else: ?>
    <!-- Campaigns Listing -->

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Environmental Campaigns</h1>
                    <p class="lead mb-0">
                        Join our community-driven environmental conservation initiatives across Tanzania
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Campaigns</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Campaign Stats -->
    <section class="campaign-stats py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon text-primary mb-3">
                            <i class="fas fa-bullhorn fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-primary"><?php echo $campaign_stats['total']; ?></h3>
                        <p class="text-muted mb-0">Total Campaigns</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon text-success mb-3">
                            <i class="fas fa-play fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-success"><?php echo $campaign_stats['active']; ?></h3>
                        <p class="text-muted mb-0">Active Now</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon text-primary mb-3">
                            <i class="fas fa-check-circle fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-primary"><?php echo $campaign_stats['completed']; ?></h3>
                        <p class="text-muted mb-0">Completed</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon text-warning mb-3">
                            <i class="fas fa-clock fa-3x"></i>
                        </div>
                        <h3 class="fw-bold text-warning"><?php echo $campaign_stats['planning']; ?></h3>
                        <p class="text-muted mb-0">In Planning</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Campaigns -->
    <?php if ($featured_campaigns && !$search && !$status_filter): ?>
        <section class="featured-campaigns py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h2 class="h4 fw-bold">Featured Campaigns</h2>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($featured_campaigns as $featured): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="featured-campaign card border-0 shadow h-100">
                                <?php if ($featured['featured_image']): ?>
                                    <img src="<?php echo UPLOADS_URL . '/campaigns/' . $featured['featured_image']; ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($featured['title']); ?>"
                                        style="height: 300px; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex align-items-center mb-3">
                                        <span class="badge bg-warning text-dark me-2">Featured</span>
                                        <span class="badge bg-<?php echo match ($featured['status']) {
                                                                    'active' => 'success',
                                                                    'completed' => 'primary',
                                                                    'planning' => 'warning',
                                                                    default => 'secondary'
                                                                }; ?>">
                                            <?php echo ucfirst($featured['status']); ?>
                                        </span>
                                    </div>

                                    <h4 class="card-title">
                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $featured['id']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($featured['title']); ?>
                                        </a>
                                    </h4>

                                    <p class="card-text text-muted flex-grow-1">
                                        <?php echo htmlspecialchars(truncate_text($featured['description'], 150)); ?>
                                    </p>

                                    <div class="campaign-meta mb-3">
                                        <?php if ($featured['location']): ?>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($featured['location']); ?>
                                            </small>
                                        <?php endif; ?>
                                        <?php if ($featured['start_date']): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                <?php echo format_date($featured['start_date']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>

                                    <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $featured['id']; ?>"
                                        class="btn btn-primary">
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

    <!-- Campaigns Listing -->
    <section class="campaigns-listing py-5">
        <div class="container">
            <!-- Filters -->
            <div class="campaigns-filters mb-5">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <form action="" method="GET" class="d-flex">
                            <input type="text"
                                class="form-control me-2"
                                name="search"
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Search campaigns...">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end mt-3 mt-md-0">
                            <div class="btn-group" role="group" aria-label="Campaign status filter">
                                <a href="<?php echo SITE_URL; ?>/campaigns.php"
                                    class="btn btn-outline-primary <?php echo empty($status_filter) ? 'active' : ''; ?>">
                                    All
                                </a>
                                <a href="<?php echo SITE_URL; ?>/campaigns.php?status=active"
                                    class="btn btn-outline-success <?php echo $status_filter === 'active' ? 'active' : ''; ?>">
                                    Active
                                </a>
                                <a href="<?php echo SITE_URL; ?>/campaigns.php?status=completed"
                                    class="btn btn-outline-primary <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                                    Completed
                                </a>
                                <a href="<?php echo SITE_URL; ?>/campaigns.php?status=planning"
                                    class="btn btn-outline-warning <?php echo $status_filter === 'planning' ? 'active' : ''; ?>">
                                    Planning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($search || $status_filter): ?>
                    <div class="active-filters mt-3">
                        <div class="d-flex align-items-center flex-wrap">
                            <span class="text-muted me-3">Active filters:</span>
                            <?php if ($search): ?>
                                <span class="badge bg-light text-dark me-2">
                                    Search: "<?php echo htmlspecialchars($search); ?>"
                                    <a href="<?php echo SITE_URL; ?>/campaigns.php<?php echo $status_filter ? '?status=' . $status_filter : ''; ?>"
                                        class="text-decoration-none ms-1">×</a>
                                </span>
                            <?php endif; ?>
                            <?php if ($status_filter): ?>
                                <span class="badge bg-light text-dark me-2">
                                    Status: <?php echo ucfirst($status_filter); ?>
                                    <a href="<?php echo SITE_URL; ?>/campaigns.php<?php echo $search ? '?search=' . urlencode($search) : ''; ?>"
                                        class="text-decoration-none ms-1">×</a>
                                </span>
                            <?php endif; ?>
                            <a href="<?php echo SITE_URL; ?>/campaigns.php" class="btn btn-sm btn-outline-secondary">
                                Clear All
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($campaigns): ?>
                <div class="campaigns-grid">
                    <div class="row">
                        <?php foreach ($campaigns as $campaign): ?>
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="campaign-card card border-0 shadow-sm h-100">
                                    <?php if ($campaign['featured_image']): ?>
                                        <img src="<?php echo UPLOADS_URL . '/campaigns/' . $campaign['featured_image']; ?>"
                                            class="card-img-top" alt="<?php echo htmlspecialchars($campaign['title']); ?>"
                                            style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>

                                    <div class="card-body d-flex flex-column">
                                        <div class="campaign-badges mb-2">
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
                                                <span class="badge bg-warning text-dark ms-1">Featured</span>
                                            <?php endif; ?>
                                        </div>

                                        <h5 class="card-title">
                                            <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                                class="text-decoration-none">
                                                <?php echo htmlspecialchars($campaign['title']); ?>
                                            </a>
                                        </h5>

                                        <p class="card-text text-muted flex-grow-1">
                                            <?php echo htmlspecialchars(truncate_text($campaign['description'], 120)); ?>
                                        </p>

                                        <div class="campaign-meta mb-3">
                                            <?php if ($campaign['location']): ?>
                                                <small class="text-muted d-block mb-1">
                                                    <i class="fas fa-map-marker-alt me-1"></i>
                                                    <?php echo htmlspecialchars($campaign['location']); ?>
                                                </small>
                                            <?php endif; ?>

                                            <?php if ($campaign['start_date']): ?>
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo format_date($campaign['start_date']); ?>
                                                    <?php if ($campaign['end_date']): ?>
                                                        - <?php echo format_date($campaign['end_date']); ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Progress Bar for campaigns with goals -->
                                        <?php if ($campaign['goal_amount'] > 0): ?>
                                            <div class="campaign-progress mb-3">
                                                <?php
                                                $progress = ($campaign['raised_amount'] / $campaign['goal_amount']) * 100;
                                                $progress = min(100, $progress);
                                                ?>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small class="text-muted">Progress</small>
                                                    <small class="fw-semibold"><?php echo round($progress); ?>%</small>
                                                </div>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: <?php echo $progress; ?>%"></div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-1">
                                                    <small class="text-muted">
                                                        $<?php echo number_format($campaign['raised_amount']); ?>
                                                    </small>
                                                    <small class="text-muted">
                                                        $<?php echo number_format($campaign['goal_amount']); ?>
                                                    </small>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                                class="btn btn-outline-primary btn-sm">
                                                Learn More
                                            </a>

                                            <?php if ($campaign['status'] === 'active'): ?>
                                                <a href="<?php echo SITE_URL; ?>/volunteer.php"
                                                    class="btn btn-primary btn-sm">
                                                    Join Campaign
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Pagination -->
                <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                    <nav aria-label="Campaigns pagination" class="mt-5">
                        <ul class="pagination justify-content-center">
                            <?php if ($pagination['has_prev']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php
                            $start_page = max(1, $pagination['current_page'] - 2);
                            $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

                            if ($start_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=1<?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">1</a>
                                </li>
                                <?php if ($start_page > 2): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>

                            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($end_page < $pagination['total_pages']): ?>
                                <?php if ($end_page < $pagination['total_pages'] - 1): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['total_pages']; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        <?php echo $pagination['total_pages']; ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php if ($pagination['has_next']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <!-- No Results -->
                <div class="no-results text-center py-5">
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
    <section class="cta-section py-5 bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h3 class="mb-3">Ready to Make an Impact?</h3>
                    <p class="mb-0 lead">
                        Join our environmental conservation efforts and help create positive change in Tanzania's communities.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-light btn-lg me-3">
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