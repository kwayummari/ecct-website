<?php

/**
 * 404 Error Page - ECCT Website
 * Page Not Found
 */

// Set proper HTTP status
http_response_code(404);

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Page variables
$page_title = '404 - Page Not Found - ECCT';
$meta_description = 'The page you are looking for could not be found on the ECCT website.';
$page_class = 'error-page error-404';

// Get some recent content for suggestions
$db = new Database();
$recent_news = get_recent_content('news', 3);
$recent_campaigns = get_recent_content('campaigns', 3);

include 'includes/header.php';
?>

<!-- 404 Error Section -->
<section class="error-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <!-- Error Code -->
                <div class="error-code mb-4">
                    <h1 class="display-1 fw-bold text-primary mb-0">404</h1>
                    <h2 class="h4 text-muted">Page Not Found</h2>
                </div>

                <!-- Error Message -->
                <div class="error-message mb-5">
                    <h3 class="h5 mb-3">Oops! The page you're looking for doesn't exist.</h3>
                    <p class="text-muted lead">
                        The page you requested might have been moved, deleted, or you entered the wrong URL.
                        Don't worry, let's get you back on track!
                    </p>
                </div>

                <!-- Action Buttons -->
                <div class="error-actions mb-5">
                    <a href="<?php echo SITE_URL; ?>" class="btn btn-primary btn-lg me-3">
                        <i class="fas fa-home me-2"></i>Go to Homepage
                    </a>
                    <button type="button" class="btn btn-outline-primary btn-lg" onclick="history.back()">
                        <i class="fas fa-arrow-left me-2"></i>Go Back
                    </button>
                </div>

                <!-- Search Box -->
                <div class="error-search mb-5">
                    <h5 class="mb-3">Try searching for what you need:</h5>
                    <form action="<?php echo SITE_URL; ?>/news.php" method="GET" class="d-flex justify-content-center">
                        <div class="input-group" style="max-width: 400px;">
                            <input type="text" class="form-control" name="search"
                                placeholder="Search our website..." required>
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Suggestions Section -->
<section class="suggestions-section py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h3 class="h4">You might be interested in:</h3>
                <p class="text-muted">Check out these popular pages and recent content</p>
            </div>
        </div>

        <div class="row">
            <!-- Popular Pages -->
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas fa-star text-warning me-2"></i>Popular Pages
                        </h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/about.php" class="text-decoration-none">
                                    <i class="fas fa-info-circle text-primary me-2"></i>About ECCT
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="text-decoration-none">
                                    <i class="fas fa-hands-helping text-success me-2"></i>Become a Volunteer
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/campaigns.php" class="text-decoration-none">
                                    <i class="fas fa-bullhorn text-info me-2"></i>Our Campaigns
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/gallery.php" class="text-decoration-none">
                                    <i class="fas fa-images text-warning me-2"></i>Photo Gallery
                                </a>
                            </li>
                            <li class="mb-2">
                                <a href="<?php echo SITE_URL; ?>/contact.php" class="text-decoration-none">
                                    <i class="fas fa-envelope text-danger me-2"></i>Contact Us
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Recent News -->
            <?php if ($recent_news): ?>
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-newspaper text-primary me-2"></i>Recent News
                            </h5>
                            <?php foreach ($recent_news as $news): ?>
                                <div class="news-item mb-3">
                                    <h6 class="mb-1">
                                        <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $news['id']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars(truncate_text($news['title'], 60)); ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo format_date($news['publish_date']); ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                            <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-sm btn-outline-primary">
                                View All News
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Campaigns -->
            <?php if ($recent_campaigns): ?>
                <div class="col-lg-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-bullhorn text-success me-2"></i>Active Campaigns
                            </h5>
                            <?php foreach ($recent_campaigns as $campaign): ?>
                                <div class="campaign-item mb-3">
                                    <h6 class="mb-1">
                                        <a href="<?php echo SITE_URL; ?>/campaigns.php?id=<?php echo $campaign['id']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars(truncate_text($campaign['title'], 60)); ?>
                                        </a>
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-<?php echo match ($campaign['status']) {
                                                                    'active' => 'success',
                                                                    'completed' => 'primary',
                                                                    'planning' => 'warning',
                                                                    default => 'secondary'
                                                                }; ?> me-2">
                                            <?php echo ucfirst($campaign['status']); ?>
                                        </span>
                                        <?php if ($campaign['location']): ?>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <?php echo htmlspecialchars($campaign['location']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="<?php echo SITE_URL; ?>/campaigns.php" class="btn btn-sm btn-outline-success">
                                View All Campaigns
                            </a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Help Section -->
<section class="help-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h4 class="mb-3">Still can't find what you're looking for?</h4>
                <p class="text-muted mb-4">
                    Our team is here to help! Contact us and we'll assist you in finding the information you need.
                </p>
                <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-primary">
                    <i class="fas fa-envelope me-2"></i>Contact Support
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    .error-section {
        min-height: 60vh;
        display: flex;
        align-items: center;
    }

    .error-code h1 {
        font-size: 8rem;
        line-height: 1;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
    }

    .news-item,
    .campaign-item {
        padding-bottom: 1rem;
        border-bottom: 1px solid #e9ecef;
    }

    .news-item:last-child,
    .campaign-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    @media (max-width: 768px) {
        .error-code h1 {
            font-size: 6rem;
        }

        .error-actions .btn {
            display: block;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        .error-actions .btn:last-child {
            margin-bottom: 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add some animation to the error code
        const errorCode = document.querySelector('.error-code h1');
        if (errorCode) {
            errorCode.style.opacity = '0';
            errorCode.style.transform = 'scale(0.5)';
            errorCode.style.transition = 'all 0.5s ease';

            setTimeout(() => {
                errorCode.style.opacity = '1';
                errorCode.style.transform = 'scale(1)';
            }, 200);
        }

        // Track 404 errors (for analytics)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'page_not_found', {
                'page_location': window.location.href,
                'page_referrer': document.referrer
            });
        }

        // Log error for debugging (in development)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            console.log('404 Error - Page not found:', window.location.href);
            console.log('Referrer:', document.referrer);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>