<?php

/**
 * Database Setup Check Script
 * Run this to check if all required tables exist
 */

define('ECCT_ROOT', __DIR__);
require_once 'includes/config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>Database Connection: ✅ SUCCESS</h2>";

    // Required tables
    $required_tables = [
        'admin_users',
        'news',
        'campaigns',
        'volunteers',
        'contact_messages',
        'gallery',
        'pages',
        'site_settings',
        'activity_log'
    ];

    echo "<h3>Checking Required Tables:</h3>";

    $missing_tables = [];

    foreach ($required_tables as $table) {
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);

        if ($stmt->rowCount() > 0) {
            echo "<p>✅ {$table} - EXISTS</p>";
        } else {
            echo "<p>❌ {$table} - MISSING</p>";
            $missing_tables[] = $table;
        }
    }

    if (empty($missing_tables)) {
        echo "<br><h3 style='color: green;'>✅ All tables exist! You can proceed with creating an admin user.</h3>";
        echo "<p><a href='create_admin_user.php'>Create Admin User</a></p>";
    } else {
        echo "<br><h3 style='color: red;'>❌ Missing tables detected!</h3>";
        echo "<p>Please import your database SQL file that contains these tables:</p>";
        echo "<ul>";
        foreach ($missing_tables as $table) {
            echo "<li>{$table}</li>";
        }
        echo "</ul>";
        echo "<p>You should have a SQL file (like u750269652_ecct2025.sql) that creates all these tables.</p>";
    }
} catch (PDOException $e) {
    echo "<h2 style='color: red;'>❌ Database Connection Failed</h2>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database configuration in includes/config.php:</p>";
    echo "<ul>";
    echo "<li>DB_HOST: " . DB_HOST . "</li>";
    echo "<li>DB_NAME: " . DB_NAME . "</li>";
    echo "<li>DB_USER: " . DB_USER . "</li>";
    echo "<li>DB_PASS: [hidden]</li>";
    echo "</ul>";
}
