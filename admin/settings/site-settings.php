<?php

/**
 * Site Settings - Admin Panel
 * ECCT Website Settings Management
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require admin role
require_role('admin');

// Get database instance
$db = new Database();

// Page variables
$page_title = 'Site Settings - ECCT Admin';
$current_user = get_current_user();
$breadcrumbs = [
    ['title' => 'Settings', 'url' => SITE_URL . '/admin/settings/'],
    ['title' => 'Site Settings']
];

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf()) {
        $error_message = 'Invalid security token.';
    } else {
        $settings_to_update = [
            'site_name',
            'site_tagline',
            'site_description',
            'contact_email',
            'contact_phone',
            'contact_address',
            'facebook_url',
            'twitter_url',
            'instagram_url',
            'google_analytics',
            'hero_title',
            'hero_subtitle',
            'successful_campaigns',
            'volunteers_count',
            'communities_served'
        ];

        $updated_count = 0;
        $errors = [];

        foreach ($settings_to_update as $setting_key) {
            if (isset($_POST[$setting_key])) {
                $setting_value = sanitize_input($_POST[$setting_key]);

                // Validate email
                if ($setting_key === 'contact_email' && !empty($setting_value) && !is_valid_email($setting_value)) {
                    $errors[] = 'Invalid email address format';
                    continue;
                }

                // Validate URLs
                if (in_array($setting_key, ['facebook_url', 'twitter_url', 'instagram_url']) && !empty($setting_value)) {
                    if (!filter_var($setting_value, FILTER_VALIDATE_URL)) {
                        $errors[] = "Invalid URL format for " . ucwords(str_replace('_', ' ', $setting_key));
                        continue;
                    }
                }

                // Validate numbers
                if (in_array($setting_key, ['successful_campaigns', 'volunteers_count', 'communities_served']) && !empty($setting_value)) {
                    if (!is_numeric($setting_value) || $setting_value < 0) {
                        $errors[] = "Invalid number for " . ucwords(str_replace('_', ' ', $setting_key));
                        continue;
                    }
                }

                if ($db->updateSetting($setting_key, $setting_value)) {
                    $updated_count++;
                }
            }
        }

        // Handle logo upload
        if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOADS_PATH . '/site';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $upload_result = upload_image($_FILES['site_logo'], $upload_dir, ['jpg', 'jpeg', 'png', 'svg'], 2 * 1024 * 1024); // 2MB limit

            if ($upload_result['success']) {
                // Delete old logo
                $old_logo = $db->getSetting('site_logo');
                if ($old_logo && file_exists(ECCT_ROOT . '/' . $old_logo)) {
                    unlink(ECCT_ROOT . '/' . $old_logo);
                }

                $logo_path = 'assets/uploads/site/' . $upload_result['filename'];
                if ($db->updateSetting('site_logo', $logo_path)) {
                    $updated_count++;
                }
            } else {
                $errors[] = 'Logo upload failed: ' . $upload_result['message'];
            }
        }

        // Handle hero background upload
        if (isset($_FILES['hero_background']) && $_FILES['hero_background']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = UPLOADS_PATH . '/site';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $upload_result = upload_image($_FILES['hero_background'], $upload_dir, ['jpg', 'jpeg', 'png'], 5 * 1024 * 1024); // 5MB limit

            if ($upload_result['success']) {
                // Delete old background
                $old_bg = $db->getSetting('hero_background');
                if ($old_bg && file_exists(ECCT_ROOT . '/' . $old_bg)) {
                    unlink(ECCT_ROOT . '/' . $old_bg);
                }

                $bg_path = 'assets/uploads/site/' . $upload_result['filename'];
                if ($db->updateSetting('hero_background', $bg_path)) {
                    $updated_count++;
                }
            } else {
                $errors[] = 'Hero background upload failed: ' . $upload_result['message'];
            }
        }

        if (!empty($errors)) {
            $error_message = 'Some settings could not be updated: ' . implode(', ', $errors);
        }

        if ($updated_count > 0) {
            $success_message = $updated_count . ' setting(s) updated successfully.';
            log_activity('settings_update', "$updated_count site settings updated");

            // Clear cache
            cache_clear('site_settings');
        }

        if (empty($errors) && $updated_count === 0) {
            $error_message = 'No settings were updated.';
        }
    }
}

// Get all current settings
$settings = $db->select('site_settings', [], ['order_by' => 'setting_key ASC']);
$settings_array = [];
foreach ($settings as $setting) {
    $settings_array[$setting['setting_key']] = $setting['setting_value'];
}

include ECCT_ROOT . '/includes/admin-header.php';
?>

<div class="container-fluid">
    <div class="row">
        <!-- Page Header -->
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Site Settings</h1>
                    <p class="text-muted">Configure your website settings and appearance</p>
                </div>
                <div class="page-actions">
                    <a href="<?php echo SITE_URL; ?>" target="_blank" class="btn btn-outline-primary">
                        <i class="fas fa-external-link-alt me-2"></i>View Website
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php echo csrf_field(); ?>

                <div class="row">
                    <!-- General Settings -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-cog text-primary me-2"></i>
                                    General Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="site_name" class="form-label">Site Name</label>
                                        <input type="text"
                                            class="form-control"
                                            id="site_name"
                                            name="site_name"
                                            value="<?php echo htmlspecialchars($settings_array['site_name'] ?? ''); ?>"
                                            required>
                                        <div class="form-text">The name of your website</div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="site_tagline" class="form-label">Site Tagline</label>
                                        <input type="text"
                                            class="form-control"
                                            id="site_tagline"
                                            name="site_tagline"
                                            value="<?php echo htmlspecialchars($settings_array['site_tagline'] ?? ''); ?>">
                                        <div class="form-text">A short description of your site</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="site_description" class="form-label">Site Description</label>
                                    <textarea class="form-control"
                                        id="site_description"
                                        name="site_description"
                                        rows="3"
                                        data-max-length="500"><?php echo htmlspecialchars($settings_array['site_description'] ?? ''); ?></textarea>
                                    <div class="form-text">Used for SEO meta description</div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-address-book text-info me-2"></i>
                                    Contact Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="contact_email" class="form-label">Contact Email</label>
                                        <input type="email"
                                            class="form-control"
                                            id="contact_email"
                                            name="contact_email"
                                            value="<?php echo htmlspecialchars($settings_array['contact_email'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="contact_phone" class="form-label">Contact Phone</label>
                                        <input type="tel"
                                            class="form-control"
                                            id="contact_phone"
                                            name="contact_phone"
                                            value="<?php echo htmlspecialchars($settings_array['contact_phone'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="contact_address" class="form-label">Contact Address</label>
                                    <textarea class="form-control"
                                        id="contact_address"
                                        name="contact_address"
                                        rows="2"><?php echo htmlspecialchars($settings_array['contact_address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-share-alt text-success me-2"></i>
                                    Social Media Links
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="facebook_url" class="form-label">
                                            <i class="fab fa-facebook text-primary me-1"></i>Facebook URL
                                        </label>
                                        <input type="url"
                                            class="form-control"
                                            id="facebook_url"
                                            name="facebook_url"
                                            value="<?php echo htmlspecialchars($settings_array['facebook_url'] ?? ''); ?>"
                                            placeholder="https://facebook.com/yourpage">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="twitter_url" class="form-label">
                                            <i class="fab fa-twitter text-info me-1"></i>Twitter URL
                                        </label>
                                        <input type="url"
                                            class="form-control"
                                            id="twitter_url"
                                            name="twitter_url"
                                            value="<?php echo htmlspecialchars($settings_array['twitter_url'] ?? ''); ?>"
                                            placeholder="https://twitter.com/yourhandle">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="instagram_url" class="form-label">
                                            <i class="fab fa-instagram text-danger me-1"></i>Instagram URL
                                        </label>
                                        <input type="url"
                                            class="form-control"
                                            id="instagram_url"
                                            name="instagram_url"
                                            value="<?php echo htmlspecialchars($settings_array['instagram_url'] ?? ''); ?>"
                                            placeholder="https://instagram.com/yourhandle">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Homepage Settings -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-home text-warning me-2"></i>
                                    Homepage Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="hero_title" class="form-label">Hero Section Title</label>
                                    <input type="text"
                                        class="form-control"
                                        id="hero_title"
                                        name="hero_title"
                                        value="<?php echo htmlspecialchars($settings_array['hero_title'] ?? ''); ?>">
                                </div>

                                <div class="mb-3">
                                    <label for="hero_subtitle" class="form-label">Hero Section Subtitle</label>
                                    <textarea class="form-control"
                                        id="hero_subtitle"
                                        name="hero_subtitle"
                                        rows="2"><?php echo htmlspecialchars($settings_array['hero_subtitle'] ?? ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="successful_campaigns" class="form-label">Successful Campaigns</label>
                                        <input type="number"
                                            class="form-control"
                                            id="successful_campaigns"
                                            name="successful_campaigns"
                                            value="<?php echo htmlspecialchars($settings_array['successful_campaigns'] ?? ''); ?>"
                                            min="0">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="volunteers_count" class="form-label">Volunteers Count</label>
                                        <input type="number"
                                            class="form-control"
                                            id="volunteers_count"
                                            name="volunteers_count"
                                            value="<?php echo htmlspecialchars($settings_array['volunteers_count'] ?? ''); ?>"
                                            min="0">
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="communities_served" class="form-label">Communities Served</label>
                                        <input type="number"
                                            class="form-control"
                                            id="communities_served"
                                            name="communities_served"
                                            value="<?php echo htmlspecialchars($settings_array['communities_served'] ?? ''); ?>"
                                            min="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Advanced Settings -->
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-code text-secondary me-2"></i>
                                    Advanced Settings
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="google_analytics" class="form-label">Google Analytics Code</label>
                                    <textarea class="form-control"
                                        id="google_analytics"
                                        name="google_analytics"
                                        rows="4"
                                        placeholder="<!-- Google Analytics tracking code -->"><?php echo htmlspecialchars($settings_array['google_analytics'] ?? ''); ?></textarea>
                                    <div class="form-text">Paste your Google Analytics tracking code here</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Media Settings -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-image text-primary me-2"></i>
                                    Site Media
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Site Logo -->
                                <div class="mb-4">
                                    <label for="site_logo" class="form-label">Site Logo</label>
                                    <div class="current-logo mb-3">
                                        <?php if (!empty($settings_array['site_logo'])): ?>
                                            <img src="<?php echo SITE_URL . '/' . $settings_array['site_logo']; ?>"
                                                alt="Current Logo"
                                                class="img-thumbnail"
                                                style="max-height: 100px;">
                                            <div class="form-text">Current logo</div>
                                        <?php else: ?>
                                            <div class="text-muted">No logo uploaded</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file"
                                        class="form-control"
                                        id="site_logo"
                                        name="site_logo"
                                        accept="image/*">
                                    <div class="form-text">Upload a new logo (JPG, PNG, SVG - max 2MB)</div>
                                </div>

                                <!-- Hero Background -->
                                <div class="mb-4">
                                    <label for="hero_background" class="form-label">Hero Background Image</label>
                                    <div class="current-background mb-3">
                                        <?php if (!empty($settings_array['hero_background'])): ?>
                                            <img src="<?php echo SITE_URL . '/' . $settings_array['hero_background']; ?>"
                                                alt="Current Background"
                                                class="img-thumbnail w-100"
                                                style="max-height: 150px; object-fit: cover;">
                                            <div class="form-text">Current hero background</div>
                                        <?php else: ?>
                                            <div class="text-muted">No background image uploaded</div>
                                        <?php endif; ?>
                                    </div>
                                    <input type="file"
                                        class="form-control"
                                        id="hero_background"
                                        name="hero_background"
                                        accept="image/*">
                                    <div class="form-text">Upload hero background (JPG, PNG - max 5MB)</div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0">
                                <h5 class="mb-0">
                                    <i class="fas fa-bolt text-warning me-2"></i>
                                    Quick Actions
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="<?php echo SITE_URL; ?>/admin/settings/backup.php" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-download me-2"></i>Backup Database
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/settings/cache-clear.php" class="btn btn-outline-warning btn-sm">
                                        <i class="fas fa-trash me-2"></i>Clear Cache
                                    </a>
                                    <a href="<?php echo SITE_URL; ?>/admin/settings/system-info.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-info-circle me-2"></i>System Info
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="row">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Changes will be applied immediately across the website
                                        </small>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-primary btn-lg">
                                            <i class="fas fa-save me-2"></i>Save Settings
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Form validation
        const form = document.querySelector('.needs-validation');
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });

        // Image preview functionality
        const logoInput = document.getElementById('site_logo');
        const backgroundInput = document.getElementById('hero_background');

        function setupImagePreview(input, previewContainer) {
            input.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = previewContainer.querySelector('img');
                        if (img) {
                            img.src = e.target.result;
                        } else {
                            const newImg = document.createElement('img');
                            newImg.src = e.target.result;
                            newImg.className = 'img-thumbnail';
                            newImg.style.maxHeight = input.id === 'site_logo' ? '100px' : '150px';
                            if (input.id === 'hero_background') {
                                newImg.classList.add('w-100');
                                newImg.style.objectFit = 'cover';
                            }
                            previewContainer.innerHTML = '';
                            previewContainer.appendChild(newImg);

                            const text = document.createElement('div');
                            text.className = 'form-text';
                            text.textContent = 'New ' + (input.id === 'site_logo' ? 'logo' : 'background') + ' (preview)';
                            previewContainer.appendChild(text);
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        if (logoInput) {
            setupImagePreview(logoInput, logoInput.parentNode.querySelector('.current-logo'));
        }

        if (backgroundInput) {
            setupImagePreview(backgroundInput, backgroundInput.parentNode.querySelector('.current-background'));
        }
    });
</script>

<?php include ECCT_ROOT . '/includes/admin-footer.php'; ?>