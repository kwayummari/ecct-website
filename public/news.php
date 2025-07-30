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

<style>
    /* News Page Styles */
    .news-hero {
        position: relative;
        min-height: 70vh;
        display: flex;
        align-items: center;
        background: linear-gradient(90deg, #28a745, #20c997), url('<?php echo ASSETS_PATH; ?>/images/she-lead/LUC06465.JPG');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
    }

    .news-hero h1 {
        font-size: 3.5rem;
        font-weight: 800;
        text-shadow: 2px 2px 4px rgba(f, f, f, 0.5);
        color: #ffffff;
        margin-bottom: 1.5rem;
    }

    .news-hero p {
        font-size: 1.2rem;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        margin-bottom: 2rem;
    }

    .hero-badge {
        background: rgba(0, 123, 255, 0.9);
        border-radius: 25px;
        padding: 8px 20px;
        display: inline-block;
        margin-bottom: 2rem;
        font-weight: 500;
    }

    .news-stats {
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

    .featured-news {
        padding: 80px 0;
        background: #f8f9fa;
    }

    .featured-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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
        background: #007bff;
        color: white;
        padding: 5px 12px;
        border-radius: 15px;
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 2;
    }

    .news-content {
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
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .news-card {
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
        margin-bottom: 30px;
    }

    .news-card:hover {
        transform: translateY(-5px);
    }

    .news-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .news-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .news-card:hover .news-image img {
        transform: scale(1.05);
    }

    .news-date-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(0, 123, 255, 0.9);
        color: white;
        padding: 8px 15px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .reading-time {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 0.75rem;
    }

    .news-content-card {
        padding: 25px;
    }

    .news-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .news-title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .news-title a:hover {
        color: #007bff;
    }

    .news-excerpt {
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 15px;
        font-size: 0.95rem;
    }

    .news-meta {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.85rem;
        color: #6c757d;
    }

    .news-meta i {
        color: #007bff;
    }

    .news-actions {
        display: flex;
        justify-content: between;
        align-items: center;
        gap: 10px;
        margin-top: 20px;
    }

    .news-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 15px;
    }

    .news-tag {
        background: #e9ecef;
        color: #495057;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
        text-decoration: none;
        transition: background 0.3s ease;
    }

    .news-tag:hover {
        background: #007bff;
        color: white;
    }

    .single-news-hero {
        background: linear-gradient(135deg, rgba(0, 123, 255, 0.9), rgba(0, 0, 0, 0.7));
        color: white;
        padding: 80px 0;
    }

    .article-content-wrapper {
        background: white;
        border-radius: 15px;
        padding: 40px;
        margin-top: -50px;
        position: relative;
        z-index: 2;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .article-image {
        border-radius: 15px;
        overflow: hidden;
        margin: 30px 0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .article-excerpt {
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
        border-left: 4px solid #007bff;
        padding: 25px;
        margin: 30px 0;
        border-radius: 0 15px 15px 0;
        font-style: italic;
    }

    .share-section {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 20px;
        margin: 30px 0;
        text-align: center;
    }

    .share-buttons {
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .share-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: white;
        transition: transform 0.3s ease;
    }

    .share-btn:hover {
        transform: translateY(-2px);
        color: white;
    }

    .share-btn.facebook {
        background: #3b5998;
    }

    .share-btn.twitter {
        background: #1da1f2;
    }

    .share-btn.linkedin {
        background: #0077b5;
    }

    .share-btn.whatsapp {
        background: #25d366;
    }

    .related-news {
        background: #f8f9fa;
        border-radius: 15px;
        padding: 30px;
        margin-top: 40px;
    }

    .related-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .related-item:last-child {
        border-bottom: none;
    }

    .related-image {
        width: 80px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .related-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .related-content h6 {
        font-size: 0.9rem;
        margin-bottom: 5px;
    }

    .related-content small {
        color: #6c757d;
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
        background: linear-gradient(135deg, #007bff, #0056b3);
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
        text-align: center;
    }

    .pagination .page-link {
        border-radius: 25px;
        margin: 0 3px;
        border: none;
        padding: 8px 16px;
        color: #007bff;
    }

    .pagination .page-item.active .page-link {
        background: #007bff;
        border-color: #007bff;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .news-hero h1 {
            font-size: 2.5rem;
        }

        .news-hero p {
            font-size: 1.1rem;
        }

        .section-title {
            font-size: 2rem;
        }

        .news-image {
            height: 180px;
        }

        .news-content-card {
            padding: 20px;
        }

        .article-content-wrapper {
            padding: 25px;
            margin-top: -30px;
        }

        .share-buttons {
            justify-content: center;
        }

        .related-item {
            flex-direction: column;
            gap: 10px;
        }

        .related-image {
            width: 100%;
            height: 120px;
        }
    }
</style>

<?php if ($news_id && $news_article): ?>
    <!-- Single News Article View -->
    <section class="single-news-hero">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb justify-content-center bg-transparent">
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="<?php echo SITE_URL; ?>/news.php" class="text-white-50">News</a>
                            </li>
                            <li class="breadcrumb-item active text-white" aria-current="page">
                                <?php echo htmlspecialchars(truncate_text($news_article['title'], 30)); ?>
                            </li>
                        </ol>
                    </nav>

                    <div class="hero-badge mb-3">
                        <?php if ($news_article['is_featured']): ?>
                            <span class="badge bg-warning text-dark me-2">Featured</span>
                        <?php endif; ?>
                        <span class="badge bg-primary">News Article</span>
                    </div>

                    <h1><?php echo htmlspecialchars($news_article['title']); ?></h1>

                    <div class="article-meta d-flex justify-content-center flex-wrap gap-4 mt-4">
                        <span class="text-white-75">
                            <i class="fas fa-calendar me-2"></i>
                            <?php echo format_date($news_article['publish_date'], 'F j, Y'); ?>
                        </span>
                        <span class="text-white-75">
                            <i class="fas fa-clock me-2"></i>
                            <?php echo ceil(str_word_count(strip_tags($news_article['content'])) / 200); ?> min read
                        </span>
                    </div>

                    <?php if ($news_tags): ?>
                        <div class="article-tags mt-4">
                            <?php foreach ($news_tags as $tag): ?>
                                <span class="badge me-2" style="background-color: <?php echo $tag['color']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="article-content-wrapper">
                        <?php if ($news_article['featured_image']): ?>
                            <div class="article-image">
                                <img src="<?php echo SITE_URL; ?>/<?php echo $news_article['featured_image']; ?>"
                                    alt="<?php echo htmlspecialchars($news_article['title']); ?>"
                                    class="img-fluid">
                            </div>
                        <?php endif; ?>

                        <?php if ($news_article['excerpt']): ?>
                            <div class="article-excerpt">
                                <h5 class="mb-3">Article Summary</h5>
                                <p class="mb-0"><?php echo htmlspecialchars($news_article['excerpt']); ?></p>
                            </div>
                        <?php endif; ?>

                        <div class="article-content">
                            <?php if ($news_article['content']): ?>
                                <?php echo $news_article['content']; ?>
                            <?php else: ?>
                                <p class="text-muted">Full article content will be available soon.</p>
                            <?php endif; ?>
                        </div>

                        <div class="share-section">
                            <h5 class="mb-3">Share This Article</h5>
                            <div class="share-buttons">
                                <?php
                                $share_links = get_share_links(
                                    current_url(),
                                    $news_article['title'],
                                    $news_article['excerpt'] ?: generate_meta_description($news_article['content'])
                                );
                                ?>
                                <a href="<?php echo $share_links['facebook']; ?>" target="_blank" class="share-btn facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="<?php echo $share_links['twitter']; ?>" target="_blank" class="share-btn twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="<?php echo $share_links['linkedin']; ?>" target="_blank" class="share-btn linkedin">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="<?php echo $share_links['whatsapp']; ?>" target="_blank" class="share-btn whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="news-sidebar">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Stay Informed</h5>
                                <p class="card-text">Get the latest updates on environmental conservation efforts and community initiatives.</p>
                                <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-primary w-100 mb-2">
                                    <i class="fas fa-newspaper me-2"></i>More News
                                </a>
                                <a href="<?php echo SITE_URL; ?>/volunteer.php" class="btn btn-outline-success w-100">
                                    <i class="fas fa-hands-helping me-2"></i>Get Involved
                                </a>
                            </div>
                        </div>

                        <?php if ($related_news): ?>
                            <div class="related-news">
                                <h5 class="mb-3">Related Articles</h5>
                                <?php foreach ($related_news as $related): ?>
                                    <div class="related-item">
                                        <div class="related-image">
                                            <?php if ($related['featured_image']): ?>
                                                <img src="<?php echo SITE_URL; ?>/<?php echo $related['featured_image']; ?>" alt="Related">
                                            <?php else: ?>
                                                <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="Related">
                                            <?php endif; ?>
                                        </div>
                                        <div class="related-content">
                                            <h6>
                                                <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $related['id']; ?>" class="text-decoration-none">
                                                    <?php echo htmlspecialchars(truncate_text($related['title'], 60)); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?php echo format_date($related['publish_date'], 'M j, Y'); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php else: ?>
    <!-- News Listing -->
    <section class="news-hero">
        <div class="container">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <div class="hero-badge">
                        <i class="fas fa-newspaper me-2"></i>Latest News
                    </div>
                    <h1>News & Events</h1>
                    <p>Stay updated with the latest news, events, and stories from our environmental conservation efforts across Tanzania.</p>
                </div>
            </div>

            <div class="news-stats">
                <div class="row">
                    <div class="col-md-4 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($news_articles ?: []); ?></span>
                            <span class="stat-label">Recent Articles</span>
                        </div>
                    </div>
                    <div class="col-md-4 col-6">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($featured_news ?: []); ?></span>
                            <span class="stat-label">Featured Stories</span>
                        </div>
                    </div>
                    <div class="col-md-4 col-12">
                        <div class="stat-item">
                            <span class="stat-number"><?php echo count($available_tags ?: []); ?></span>
                            <span class="stat-label">Topics Covered</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php if ($featured_news): ?>
        <section class="featured-news">
            <div class="container">
                <div class="row text-center mb-5">
                    <div class="col-12">
                        <div class="section-badge">
                            <i class="fas fa-star me-2"></i>Featured Stories
                        </div>
                        <h2 class="section-title">Spotlight News</h2>
                        <p class="section-subtitle">Discover our most important environmental conservation news and community impact stories</p>
                    </div>
                </div>

                <div class="row">
                    <?php foreach ($featured_news as $featured): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="featured-card">
                                <div class="news-image">
                                    <?php if ($featured['featured_image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/<?php echo $featured['featured_image']; ?>" alt="<?php echo htmlspecialchars($featured['title']); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="News">
                                    <?php endif; ?>

                                    <div class="news-date-badge">
                                        <?php echo format_date($featured['publish_date'], 'M j'); ?>
                                    </div>

                                    <div class="reading-time">
                                        <?php echo ceil(str_word_count(strip_tags($featured['content'])) / 200); ?> min read
                                    </div>
                                </div>

                                <div class="news-content-card">
                                    <h4 class="news-title">
                                        <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $featured['id']; ?>">
                                            <?php echo htmlspecialchars($featured['title']); ?>
                                        </a>
                                    </h4>

                                    <p class="news-excerpt">
                                        <?php echo htmlspecialchars(truncate_text($featured['excerpt'] ?: generate_meta_description($featured['content']), 120)); ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            <?php echo format_date($featured['publish_date'], 'F j, Y'); ?>
                                        </small>
                                        <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $featured['id']; ?>" class="btn btn-primary btn-sm">
                                            Read More <i class="fas fa-arrow-right ms-1"></i>
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

    <section class="news-content">
        <div class="container">
            <!-- Search Filter -->
            <div class="filter-section">
                <h3 class="filter-title">Find Articles</h3>
                <form method="GET" class="row g-3">
                    <div class="col-md-8">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                            value="<?php echo htmlspecialchars($search); ?>" placeholder="Search news articles...">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <a href="<?php echo SITE_URL; ?>/news.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>

            <?php if ($news_articles): ?>
                <div class="row">
                    <?php foreach ($news_articles as $article): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="news-card">
                                <div class="news-image">
                                    <?php if ($article['featured_image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/<?php echo $article['featured_image']; ?>" alt="<?php echo htmlspecialchars($article['title']); ?>">
                                    <?php else: ?>
                                        <img src="<?php echo ASSETS_PATH; ?>/images/green-generation/IMG_3265.JPG" alt="News">
                                    <?php endif; ?>

                                    <div class="news-date-badge">
                                        <?php echo format_date($article['publish_date'], 'M j'); ?>
                                    </div>

                                    <div class="reading-time">
                                        <?php echo ceil(str_word_count(strip_tags($article['content'])) / 200); ?> min read
                                    </div>
                                </div>

                                <div class="news-content-card">
                                    <h5 class="news-title">
                                        <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article['id']; ?>">
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        </a>
                                    </h5>

                                    <p class="news-excerpt">
                                        <?php echo htmlspecialchars(truncate_text($article['excerpt'] ?: generate_meta_description($article['content']), 120)); ?>
                                    </p>

                                    <div class="news-meta">
                                        <span>
                                            <i class="fas fa-calendar"></i>
                                            <?php echo format_date($article['publish_date'], 'M j, Y'); ?>
                                        </span>
                                        <?php if ($article['is_featured']): ?>
                                            <span class="badge bg-primary">Featured</span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="news-actions">
                                        <a href="<?php echo SITE_URL; ?>/news.php?id=<?php echo $article['id']; ?>" class="btn btn-primary">
                                            Read Full Article
                                        </a>
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
                            <?php if ($pagination['current_page'] > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] - 1); ?><?php echo ($search ? '&search=' . urlencode($search) : ''); ?>">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                                <li class="page-item <?php echo ($i === $pagination['current_page']) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo ($search ? '&search=' . urlencode($search) : ''); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($pagination['current_page'] + 1); ?><?php echo ($search ? '&search=' . urlencode($search) : ''); ?>">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else: ?>
                <div class="no-results">
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
    </section>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>