<?php

/**
 * Delete Campaign - ECCT Admin
 */

define('ECCT_ROOT', dirname(__FILE__, 3));
require_once ECCT_ROOT . '/includes/config.php';
require_once ECCT_ROOT . '/includes/database.php';
require_once ECCT_ROOT . '/includes/auth.php';
require_once ECCT_ROOT . '/includes/functions.php';

// Require login and permission
require_login();
require_permission('delete_campaigns');

// Get database instance
$db = new Database();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Invalid request method.');
    redirect('manage-campaigns.php');
}

// Validate CSRF token
if (!validate_csrf()) {
    set_flash('error', 'Invalid security token.');
    redirect('manage-campaigns.php');
}

// Get campaign ID
$campaign_id = (int)($_POST['campaign_id'] ?? 0);
if (!$campaign_id) {
    set_flash('error', 'Invalid campaign ID.');
    redirect('manage-campaigns.php');
}

// Get campaign data
$campaign = $db->selectOne('campaigns', ['id' => $campaign_id]);
if (!$campaign) {
    set_flash('error', 'Campaign not found.');
    redirect('manage-campaigns.php');
}

try {
    // Begin transaction
    $db->beginTransaction();

    // Delete campaign tags associations
    $db->delete('campaign_tags', ['campaign_id' => $campaign_id]);

    // Delete the campaign itself
    $deleted = $db->delete('campaigns', ['id' => $campaign_id]);

    if ($deleted) {
        // Delete associated image files
        if ($campaign['featured_image']) {
            $image_path = UPLOADS_PATH . '/campaigns/' . $campaign['featured_image'];
            delete_image($image_path);
        }

        // Commit transaction
        $db->commit();

        // Log activity
        log_activity('campaign_delete', "Campaign deleted: {$campaign['title']}", 'campaigns', $campaign_id);

        set_flash('success', "Campaign '{$campaign['title']}' has been deleted successfully.");
    } else {
        // Rollback transaction
        $db->rollback();
        set_flash('error', 'Failed to delete campaign. Please try again.');
    }
} catch (Exception $e) {
    // Rollback transaction on error
    $db->rollback();

    if (DEBUG_MODE) {
        set_flash('error', 'Error deleting campaign: ' . $e->getMessage());
    } else {
        set_flash('error', 'An error occurred while deleting the campaign. Please try again.');
    }
}

// Redirect back to campaigns list
redirect('manage-campaigns.php');
