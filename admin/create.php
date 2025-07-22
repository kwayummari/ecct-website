<?php

/**
 * Create Admin User Script
 * Run this script ONCE to create your first admin user
 * Delete this file after creating the admin user for security
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';
require_once 'includes/database.php';

// Check if admin users table exists and has any users
$db = new Database();

try {
    $admin_count = $db->count('admin_users');

    if ($admin_count > 0) {
        die('Admin users already exist. Delete this file for security.');
    }

    // Create the first admin user
    $admin_data = [
        'username' => 'admin',
        'email' => 'admin@ecct.or.tz',
        'password' => password_hash('EcctAdmin@2025', PASSWORD_DEFAULT), // Change this password!
        'full_name' => 'ECCT Administrator',
        'role' => 'super_admin',
        'is_active' => 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];

    $user_id = $db->insert('admin_users', $admin_data);

    if ($user_id) {
        echo "<h2>Admin User Created Successfully!</h2>";
        echo "<p><strong>Username:</strong> admin</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
        echo "<p><strong>Please change this password immediately after login!</strong></p>";
        echo "<p><a href='admin/login.php'>Login to Admin Panel</a></p>";
        echo "<br><p style='color: red;'><strong>IMPORTANT: Delete this file (create_admin_user.php) for security!</strong></p>";
    } else {
        echo "Error creating admin user. Check your database connection.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "<br><br>Make sure your database is properly set up with the admin_users table.";
}
