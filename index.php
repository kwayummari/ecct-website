<?php

/**
 * Root Index File - ECCT Website
 * Redirects to public folder or handles routing
 */

// Define root path
if (!defined('ECCT_ROOT')) {
    define('ECCT_ROOT', __DIR__);
}

// Check if we're accessing from web root or if files are in a subdirectory
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = dirname($script_name);

// Remove query string from request URI
$request_path = parse_url($request_uri, PHP_URL_PATH);

// Remove base path from request path
if ($base_path !== '/' && strpos($request_path, $base_path) === 0) {
    $request_path = substr($request_path, strlen($base_path));
}

// Clean up the path
$request_path = '/' . trim($request_path, '/');

// Route handling
switch ($request_path) {
    case '/':
    case '/index':
    case '/index.php':
        require_once 'public/index.php';
        break;

    case '/about':
    case '/about.php':
        require_once 'public/about.php';
        break;

    case '/contact':
    case '/contact.php':
        require_once 'public/contact.php';
        break;

    case '/news':
    case '/news.php':
        require_once 'public/news.php';
        break;

    case '/campaigns':
    case '/campaigns.php':
        require_once 'public/campaigns.php';
        break;

    case '/gallery':
    case '/gallery.php':
        require_once 'public/gallery.php';
        break;

    case '/volunteer':
    case '/volunteer.php':
        require_once 'public/volunteer.php';
        break;

    case '/programs':
    case '/programs.php':
        require_once 'public/programs.php';
        break;

    // Admin routes
    case '/admin':
    case '/admin/':
        require_once 'admin/index.php';
        break;

    case '/admin/login':
    case '/admin/login.php':
        require_once 'admin/login.php';
        break;

    // Assets
    case (preg_match('/^\/assets\//', $request_path) ? $request_path : null):
        // Handle static assets
        $file_path = ECCT_ROOT . $request_path;
        if (file_exists($file_path) && is_file($file_path)) {
            $mime_type = mime_content_type($file_path);
            header('Content-Type: ' . $mime_type);
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        }
        break;

    // Dynamic page routing
    default:
        // Try to find a matching page in the database
        require_once 'includes/config.php';
        require_once 'includes/database.php';
        require_once 'includes/functions.php';

        $db = new Database();
        $slug = trim($request_path, '/');

        // Check if it's a dynamic page
        $page = $db->selectOne('pages', [
            'slug' => $slug,
            'is_published' => 1
        ]);

        if ($page) {
            // Load dynamic page template
            $page_title = $page['title'] . ' - ECCT';
            $meta_description = $page['meta_description'] ?: generate_meta_description($page['content']);
            $page_class = 'dynamic-page page-' . $page['slug'];

            include 'includes/header.php';
?>

            <!-- Dynamic Page Content -->
            <section class="page-header bg-primary text-white py-5">
                <div class="container">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <h1 class="display-4 fw-bold mb-3"><?php echo htmlspecialchars($page['title']); ?></h1>
                        </div>
                        <div class="col-lg-4 text-lg-end">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-lg-end mb-0">
                                    <li class="breadcrumb-item">
                                        <a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active text-white" aria-current="page">
                                        <?php echo htmlspecialchars($page['title']); ?>
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </section>

            <section class="page-content py-5">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <div class="content-wrapper">
                                <?php echo $page['content']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

<?php
            include 'includes/footer.php';
        } else {
            // 404 Not Found
            header('HTTP/1.0 404 Not Found');
            include '404.php';
        }
        break;
}
?>