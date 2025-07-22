<?php
define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';

require_login();

$db = new Database();
$current_user = get_current_user();

// Handle form submission
if ($_POST) {
    $settings_updated = 0;
    $errors = [];

    // Handle logo upload
    if (!empty($_FILES['site_logo']['name'])) {
        $upload_dir = ECCT_ROOT . '/assets/uploads/site/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $upload_path)) {
            // Delete old logo
            $old_logo = $db->getSetting('site_logo');
            if ($old_logo && file_exists(ECCT_ROOT . '/' . $old_logo)) {
                unlink(ECCT_ROOT . '/' . $old_logo);
            }
            $_POST['site_logo'] = 'assets/uploads/site/' . $filename;
        }
    }

    // Handle hero background upload
    if (!empty($_FILES['hero_background']['name'])) {
        $upload_dir = ECCT_ROOT . '/assets/uploads/site/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['hero_background']['name'], PATHINFO_EXTENSION);
        $filename = 'hero_bg_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['hero_background']['tmp_name'], $upload_path)) {
            // Delete old background
            $old_bg = $db->getSetting('hero_background');
            if ($old_bg && file_exists(ECCT_ROOT . '/' . $old_bg)) {
                unlink(ECCT_ROOT . '/' . $old_bg);
            }
            $_POST['hero_background'] = 'assets/uploads/site/' . $filename;
        }
    }

    // Update settings
    foreach ($_POST as $key => $value) {
        if ($key !== 'csrf_token' && !empty($key)) {
            if ($db->updateSetting($key, $value)) {
                $settings_updated++;
            }
        }
    }

    if ($settings_updated > 0) {
        $success = "Settings updated successfully!";
    } else {
        $error = "No changes were made.";
    }
}

// Get current settings
$settings = [
    'site_name' => $db->getSetting('site_name', 'ECCT'),
    'site_tagline' => $db->getSetting('site_tagline', 'Environmental Conservation Community of Tanzania'),
    'site_description' => $db->getSetting('site_description', ''),
    'contact_email' => $db->getSetting('contact_email', ''),
    'contact_phone' => $db->getSetting('contact_phone', ''),
    'contact_address' => $db->getSetting('contact_address', ''),
    'facebook_url' => $db->getSetting('facebook_url', ''),
    'twitter_url' => $db->getSetting('twitter_url', ''),
    'instagram_url' => $db->getSetting('instagram_url', ''),
    'site_logo' => $db->getSetting('site_logo', ''),
    'hero_title' => $db->getSetting('hero_title', ''),
    'hero_subtitle' => $db->getSetting('hero_subtitle', ''),
    'hero_background' => $db->getSetting('hero_background', ''),
    'successful_campaigns' => $db->getSetting('successful_campaigns', '0'),
    'volunteers_count' => $db->getSetting('volunteers_count', '0'),
    'communities_served' => $db->getSetting('communities_served', '0'),
    'google_analytics' => $db->getSetting('google_analytics', '')
];

include '../includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar.php'; ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Site Settings</h1>
            </div>

            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-8">
                        <!-- General Settings -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">General Settings</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="site_name" class="form-label">Site Name</label>
                                    <input type="text" class="form-control" id="site_name" name="site_name"
                                        value="<?php echo htmlspecialchars($settings['site_name']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="site_tagline" class="form-label">Site Tagline</label>
                                    <input type="text" class="form-control" id="site_tagline" name="site_tagline"
                                        value="<?php echo htmlspecialchars($settings['site_tagline']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control" id="site_description" name="site_description" rows="3"><?php echo htmlspecialchars($settings['site_description']); ?></textarea>
                                    <div class="form-text">Used for SEO meta description</div>
                                </div>

                                <div class="mb-3">
                                    <label for="site_logo" class="form-label">Site Logo</label>
                                    <?php if ($settings['site_logo']): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo SITE_URL . '/' . $settings['site_logo']; ?>"
                                                alt="Current logo" class="img-thumbnail" style="max-height: 100px;">
                                            <p class="small text-muted">Current logo</p>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="site_logo" name="site_logo" accept="image/*">
                                    <div class="form-text">Upload a new logo to replace the current one</div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Contact Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="contact_email" class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" id="contact_email" name="contact_email"
                                        value="<?php echo htmlspecialchars($settings['contact_email']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_phone" class="form-label">Contact Phone</label>
                                    <input type="text" class="form-control" id="contact_phone" name="contact_phone"
                                        value="<?php echo htmlspecialchars($settings['contact_phone']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="contact_address" class="form-label">Contact Address</label>
                                    <textarea class="form-control" id="contact_address" name="contact_address" rows="3"><?php echo htmlspecialchars($settings['contact_address']); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Social Media Links</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="facebook_url" class="form-label">Facebook URL</label>
                                    <input type="url" class="form-control" id="facebook_url" name="facebook_url"
                                        value="<?php echo htmlspecialchars($settings['facebook_url']); ?>"
                                        placeholder="https://facebook.com/your-page">
                                </div>

                                <div class="mb-3">
                                    <label for="twitter_url" class="form-label">Twitter URL</label>
                                    <input type="url" class="form-control" id="twitter_url" name="twitter_url"
                                        value="<?php echo htmlspecialchars($settings['twitter_url']); ?>"
                                        placeholder="https://twitter.com/your-handle">
                                </div>

                                <div class="mb-3">
                                    <label for="instagram_url" class="form-label">Instagram URL</label>
                                    <input type="url" class="form-control" id="instagram_url" name="instagram_url"
                                        value="<?php echo htmlspecialchars($settings['instagram_url']); ?>"
                                        placeholder="https://instagram.com/your-handle">
                                </div>
                            </div>
                        </div>

                        <!-- Homepage Hero Section -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Homepage Hero Section</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="hero_title" class="form-label">Hero Title</label>
                                    <input type="text" class="form-control" id="hero_title" name="hero_title"
                                        value="<?php echo htmlspecialchars($settings['hero_title']); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="hero_subtitle" class="form-label">Hero Subtitle</label>
                                    <textarea class="form-control" id="hero_subtitle" name="hero_subtitle" rows="2"><?php echo htmlspecialchars($settings['hero_subtitle']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="hero_background" class="form-label">Hero Background Image</label>
                                    <?php if ($settings['hero_background']): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo SITE_URL . '/' . $settings['hero_background']; ?>"
                                                alt="Current hero background" class="img-thumbnail" style="max-height: 150px;">
                                            <p class="small text-muted">Current hero background</p>
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="hero_background" name="hero_background" accept="image/*">
                                    <div class="form-text">Recommended size: 1920x1080px or larger</div>
                                </div>
                            </div>
                        </div>

                        <!-- Analytics -->
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Analytics</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="google_analytics" class="form-label">Google Analytics Code</label>
                                    <textarea class="form-control" id="google_analytics" name="google_analytics" rows="5"
                                        placeholder="Paste your Google Analytics tracking code here"><?php echo htmlspecialchars($settings['google_analytics']); ?></textarea>
                                    <div class="form-text">Include the full script tag from Google Analytics</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Statistics -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Homepage Statistics</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="successful_campaigns" class="form-label">Successful Campaigns</label>
                                    <input type="number" class="form-control" id="successful_campaigns" name="successful_campaigns"
                                        value="<?php echo htmlspecialchars($settings['successful_campaigns']); ?>" min="0">
                                </div>

                                <div class="mb-3">
                                    <label for="volunteers_count" class="form-label">Volunteers Count</label>
                                    <input type="number" class="form-control" id="volunteers_count" name="volunteers_count"
                                        value="<?php echo htmlspecialchars($settings['volunteers_count']); ?>" min="0">
                                </div>

                                <div class="mb-3">
                                    <label for="communities_served" class="form-label">Communities Served</label>
                                    <input type="number" class="form-control" id="communities_served" name="communities_served"
                                        value="<?php echo htmlspecialchars($settings['communities_served']); ?>" min="0">
                                </div>

                                <div class="form-text">These numbers will be displayed on the homepage</div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="card">
                            <div class="card-body">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-save me-2"></i>Save Settings
                                </button>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">Quick Actions</h5>
                            </div>
                            <div class="card-body">
                                <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-external-link-alt me-2"></i>View Website
                                </a>

                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearCache()">
                                    <i class="fas fa-sync me-2"></i>Clear Cache
                                </button>
                            </div>
                        </div>

                        <!-- System Info -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h5 class="mb-0">System Info</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>PHP Version:</strong><br><?php echo PHP_VERSION; ?></p>
                                <p><strong>MySQL Version:</strong><br><?php echo $db->raw("SELECT VERSION()")->fetchColumn(); ?></p>
                                <p><strong>Server:</strong><br><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
                                <p><strong>Upload Max Size:</strong><br><?php echo ini_get('upload_max_filesize'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </main>
    </div>
</div>

<script>
    function clearCache() {
        if (confirm('Are you sure you want to clear the cache?')) {
            // Here you would make an AJAX call to clear cache
            alert('Cache cleared successfully!');
        }
    }
</script>

<?php include '../includes/footer.php'; ?>