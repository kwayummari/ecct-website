<?php

/**
 * News & Events Page for ECCT Website
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Get database instance
$db = new Database();

// Get parameters
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';

// If viewing single news article
if ($news_id) {
    $news_article = $db->selectOne('news', [
        'id' => $news_id,
        'is_published' => 1
    ]);

    if (!$news_article) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit;
    }

    // Page variables for single article
    $page_title = $news_article['title'] . ' - ECCT News';
    $meta_description = $news_article['meta_description'] ?: generate_meta_description($news_article['content']);
    $page_class = 'news-single';

    // Get related news
    $related_news = $db->select('news', [
        'is_published' => 1,
        'id' => array_filter([$news_id], function ($id) {
            return false;
        }) // Exclude current article
    ], [
        'order_by' => 'publish_date DESC',
        'limit' => 3
    ]);

    // Get news tags if they exist
    $news_tags = $db->raw(
        "SELECT t.* FROM tags t 
         JOIN news_tags nt ON t.id = nt.tag_id 
         WHERE nt.news_id = ?",
        [$news_id]
    );
    $news_tags = $news_tags ? $news_tags->fetchAll() : [];
} else {
    // News listing page
    $page_title = 'News & Events - ECCT';
    $meta_description = 'Stay updated with latest news, events and environmental conservation activities from ECCT Tanzania';
    $page_class = 'news-listing';

    // Build conditions for news query
    $conditions = ['is_published' => 1];
    $search_columns = [];

    if ($search) {
        // Use search functionality
        $news_results = $db->search('news', $search, ['title', 'excerpt', 'content'], $conditions, [
            'order_by' => 'publish_date DESC'
        ]);
        $total_news = count($news_results ?: []);
        $total_pages = ceil($total_news / NEWS_PER_PAGE);
        $offset = ($page - 1) * NEWS_PER_PAGE;
        $news_articles = array_slice($news_results ?: [], $offset, NEWS_PER_PAGE);
    } else {
        // Regular pagination
        $pagination_result = $db->paginate('news', $page, NEWS_PER_PAGE, $conditions, [
            'order_by' => 'publish_date DESC'
        ]);
        $news_articles = $pagination_result['data'];
        $pagination = $pagination_result['pagination'];
    }

    // Get featured news
    $featured_news = $db->getPublishedContent('news', [
        'conditions' => ['is_featured' => 1],
        'order_by' => 'publish_date DESC',
        'limit' => 3
    ]);

    // Get available tags for filtering
    $available_tags = $db->raw(
        "SELECT t.*, COUNT(nt.news_id) as news_count 
         FROM tags t 
         JOIN news_tags nt ON t.id = nt.tag_id 
         JOIN news n ON nt.news_id = n.id 
         WHERE n.is_published = 1 
         GROUP BY t.id 
         ORDER BY news_count DESC, t.name ASC"
    );
    $available_tags = $available_tags ? $available_tags->fetchAll() : [];
}

include 'includes/header.php';
?>

<?php if ($news_id && $news_article): ?>
    <!-- Single News Article -->
    <article class="news-article">
        <!-- Article Header -->
        <section class="article-header bg-light py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <!-- Breadcrumb -->
                        <nav aria-label="breadcrumb" class="mb-4">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
                                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/news.php">News</a></li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?php echo htmlspecialchars(truncate_text($news_article['title'], 50)); ?>
                                </li>
                            </ol>
                        </nav>

                        <h1 class="display-5 fw-bold mb-4"><?php echo htmlspecialchars($news_article['title']); ?></h1>

                        <div class="article-meta d-flex flex-wrap align-items-center mb-4">
                            <span class="text-muted me-4">
                                <i class="fas fa-calendar me-2"></i>
                                <?php echo format_date($news_article['publish_date'], 'F j, Y'); ?>
                            </span>
                            <span class="text-muted me-4">
                                <i class="fas fa-clock me-2"></i>
                                <?php echo ceil(str_word_count(strip_tags($news_article['content'])) / 200); ?> min read
                            </span>
                            <?php if ($news_article['is_featured']): ?>
                                <span class="badge bg-primary me-2">Featured</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($news_tags): ?>
                            <div class="article-tags mb-4">
                                <?php foreach ($news_tags as $tag): ?>
                                    <span class="badge rounded-pill" style="background-color: <?php echo $tag['color']; ?>">
                                        <?php echo htmlspecialchars($tag['name']); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Share Buttons -->
                        <div class="share-buttons">
                            <span class="text-muted me-3">Share:</span>
                            <?php
                            $share_links = get_share_links(
                                current_url(),
                                $news_article['title'],
                                $news_article['excerpt'] ?: generate_meta_description($news_article['content'])
                            );
                            ?>
                            <a href="<?php echo $share_links['facebook']; ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="<?php echo $share_links['twitter']; ?>" target="_blank" class="btn btn-outline-info btn-sm me-2">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="<?php echo $share_links['linkedin']; ?>" target="_blank" class="btn btn-outline-primary btn-sm me-2">
                                <i class="fab fa-linkedin-in"></i>
                            </a>
                            <a href="<?php echo $share_links['whatsapp']; ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Article Content -->
        <section class="article-content py-5">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <?php if ($news_article['featured_image']): ?>
                            <div class="article-image mb-5">
                                <img src="<?php echo SITE_URL; ?>/<?php echo $news['featured_image']; ?> ?>"
                                    alt="<?php echo htmlspecialchars($news_article['title']); ?>"
                                    class="img-fluid rounded shadow">
                            </div>
                        <?php endif; ?>

                        <?php if ($news_article['excerpt']): ?>
                            <div class="article-excerpt bg-light p-4 rounded mb-5">
                                <p class="lead mb-0"><?php echo htmlspecialchars($news_article['excerpt']); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="article-body">
                            <?php echo $news_article['content']; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related News -->
        <?php if ($related_news): ?>
            <section class="related-news py-5 bg-light">
                <div class="container">
                    <div class="row">
                        <div class="col-12">
                            <h3 class="h4 fw-bold mb-4">Related News</h3>
                        </div>
                    </div>
                    <div class="row">
                        <?php foreach ($related_news as $related): ?>
                            <div class="col-lg-4 mb-4">
                                <div class="card border-0 shadow-sm h-100">
                                    <?php if ($related['featured_image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/<?php echo $related['featured_image']; ?>"
                                            class="card-img-top" alt="<?php echo htmlspecialchars($related['title']); ?>"
                                            style="height: 200px; object-fit: cover;">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $related['id']; ?>"
                                                class="text-decoration-none">
                                                <?php echo htmlspecialchars($related['title']); ?>
                                            </a>
                                        </h6>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars(truncate_text($related['excerpt'] ?: strip_tags($related['content']), 100)); ?>
                                        </p>
                                        <small class="text-muted">
                                            <?php echo format_date($related['publish_date']); ?>
                                        </small>
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
    <!-- News Listing -->

    <!-- Page Header -->
    <section class="page-header bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">News & Events</h1>
                    <p class="lead mb-0">
                        Stay updated with our latest environmental conservation activities and community initiatives
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-lg-end mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item active text-white" aria-current="page">News</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured News -->
    <?php if ($featured_news && !$search): ?>
        <section class="featured-news py-5 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-12 mb-4">
                        <h2 class="h4 fw-bold">Featured Stories</h2>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($featured_news as $index => $featured): ?>
                        <div class="col-lg-<?php echo $index === 0 ? '6' : '3'; ?> col-md-6 mb-4">
                            <div class="featured-card card border-0 shadow h-100">
                                <?php if ($featured['featured_image']): ?>
                                    <img src="<?php echo UPLOADS_URL . '/news/' . $featured['featured_image']; ?>"
                                        class="card-img-top" alt="<?php echo htmlspecialchars($featured['title']); ?>"
                                        style="height: <?php echo $index === 0 ? '300px' : '200px'; ?>; object-fit: cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge bg-primary me-2">Featured</span>
                                        <small class="text-muted">
                                            <?php echo format_date($featured['publish_date']); ?>
                                        </small>
                                    </div>
                                    <h5 class="card-title <?php echo $index === 0 ? 'h4' : ''; ?>">
                                        <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $featured['id']; ?>"
                                            class="text-decoration-none">
                                            <?php echo htmlspecialchars($featured['title']); ?>
                                        </a>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php echo htmlspecialchars(truncate_text($featured['excerpt'] ?: strip_tags($featured['content']), $index === 0 ? 150 : 100)); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <!-- News Listing -->
    <section class="news-listing py-5">
        <div class="container">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 mb-5">
                    <div class="news-sidebar">
                        <!-- Search -->
                        <div class="search-widget card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-3">Search News</h6>
                                <form action="" method="GET">
                                    <div class="input-group">
                                        <input type="text"
                                            class="form-control"
                                            name="search"
                                            value="<?php echo htmlspecialchars($search); ?>"
                                            placeholder="Search articles...">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </form>
                                <?php if ($search): ?>
                                    <div class="mt-2">
                                        <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i>Clear Search
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Tags -->
                        <?php if ($available_tags): ?>
                            <div class="tags-widget card border-0 shadow-sm mb-4">
                                <div class="card-body">
                                    <h6 class="card-title fw-bold mb-3">Topics</h6>
                                    <div class="tag-cloud">
                                        <?php foreach ($available_tags as $tag): ?>
                                            <a href="<?php echo SITE_URL; ?>/news.php?tag=<?php echo $tag['slug']; ?>"
                                                class="badge rounded-pill me-2 mb-2 text-decoration-none"
                                                style="background-color: <?php echo $tag['color']; ?>">
                                                <?php echo htmlspecialchars($tag['name']); ?>
                                                <span class="badge bg-light text-dark ms-1"><?php echo $tag['news_count']; ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Newsletter -->
                        <div class="newsletter-widget card border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="card-title fw-bold mb-3">Stay Updated</h6>
                                <p class="text-muted small mb-3">
                                    Subscribe to our newsletter for the latest environmental news and updates.
                                </p>
                                <form action="<?php echo SITE_URL; ?>/includes/newsletter-subscribe.php" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <div class="mb-3">
                                        <input type="email" class="form-control" name="email" placeholder="Your email" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        Subscribe
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-lg-9">
                    <?php if ($search): ?>
                        <div class="search-results-header mb-4">
                            <h4>Search Results for "<?php echo htmlspecialchars($search); ?>"</h4>
                            <p class="text-muted">
                                Found <?php echo count($news_articles); ?> article(s)
                            </p>
                        </div>
                    <?php endif; ?>

                    <?php if ($news_articles): ?>
                        <div class="news-grid">
                            <?php foreach ($news_articles as $article): ?>
                                <div class="news-item card border-0 shadow-sm mb-4">
                                    <div class="row g-0">
                                        <?php if ($article['featured_image']): ?>
                                            <div class="col-md-4">
                                                <img src="<?php echo UPLOADS_URL . '/news/' . $article['featured_image']; ?>"
                                                    class="img-fluid h-100 w-100"
                                                    alt="<?php echo htmlspecialchars($article['title']); ?>"
                                                    style="object-fit: cover; min-height: 200px;">
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-<?php echo $article['featured_image'] ? '8' : '12'; ?>">
                                            <div class="card-body h-100 d-flex flex-column">
                                                <div class="article-meta mb-2">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo format_date($article['publish_date']); ?>
                                                    </small>
                                                    <?php if ($article['is_featured']): ?>
                                                        <span class="badge bg-primary ms-2">Featured</span>
                                                    <?php endif; ?>
                                                </div>

                                                <h5 class="card-title">
                                                    <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article['id']; ?>"
                                                        class="text-decoration-none">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </a>
                                                </h5>

                                                <p class="card-text text-muted flex-grow-1">
                                                    <?php echo htmlspecialchars(truncate_text($article['excerpt'] ?: strip_tags($article['content']), 150)); ?>
                                                </p>

                                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                                    <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article['id']; ?>"
                                                        class="btn btn-outline-primary btn-sm">
                                                        Read More <i class="fas fa-arrow-right ms-1"></i>
                                                    </a>
                                                    <small class="text-muted">
                                                        <?php echo ceil(str_word_count(strip_tags($article['content'])) / 200); ?> min read
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if (isset($pagination) && $pagination['total_pages'] > 1): ?>
                            <nav aria-label="News pagination" class="mt-5">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagination['has_prev']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] - 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $pagination['current_page'] - 2);
                                    $end_page = min($pagination['total_pages'], $pagination['current_page'] + 2);

                                    if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">1</a>
                                        </li>
                                        <?php if ($start_page > 2): ?>
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo $i === $pagination['current_page'] ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
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
                                            <a class="page-link" href="?page=<?php echo $pagination['total_pages']; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
                                                <?php echo $pagination['total_pages']; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php if ($pagination['has_next']): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $pagination['current_page'] + 1; ?><?php echo $search ? '&search=' . urlencode($search) : ''; ?>">
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
                            <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                            <h4 class="text-muted mb-3">
                                <?php echo $search ? 'No articles found' : 'No news articles available'; ?>
                            </h4>
                            <p class="text-muted mb-4">
                                <?php if ($search): ?>
                                    Try adjusting your search terms or browse all articles.
                                <?php else: ?>
                                    Check back later for updates on our environmental conservation activities.
                                <?php endif; ?>
                            </p>
                            <?php if ($search): ?>
                                <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i>Browse All Articles
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>