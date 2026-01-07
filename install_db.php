<?php
require_once 'config.php';

/**
 * INTELLIGENT DATABASE INSTALLER & MIGRATOR
 * Handles creation of tables and automatic schema updates (adding missing columns).
 */
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create Database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    $pdo->exec("USE " . DB_NAME);

    echo "<div style='font-family:sans-serif; max-width:800px; margin:50px auto; padding:30px; border:1px solid #ddd; border-radius:12px; background:#f8fafc;'>";
    echo "<h2 style='color:#0f172a; border-bottom:2px solid #e2e8f0; padding-bottom:15px;'>System Installation & Recovery</h2>";

    // 1. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        channel_id VARCHAR(100) NOT NULL UNIQUE,
        channel_name VARCHAR(255),
        avatar_url TEXT,
        balance DECIMAL(15,2) DEFAULT 0.00,
        total_subs_gained INT DEFAULT 0,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color:green'>✓ Table 'users' verified.</p>";

    // 2. Transactions
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type ENUM('deposit', 'sub_expense', 'sub_income', 'penalty') NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        description VARCHAR(255),
        status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
        proof_img VARCHAR(255) NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p style='color:green'>✓ Table 'transactions' verified.</p>";

    // 3. Subscriptions
    $pdo->exec("CREATE TABLE IF NOT EXISTS subscriptions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        subscriber_user_id INT,
        target_channel_id VARCHAR(100),
        status ENUM('active', 'unsubbed') DEFAULT 'active',
        check_timestamp TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (subscriber_user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    echo "<p style='color:green'>✓ Table 'subscriptions' verified.</p>";

    // 4. Posts (Smart Migration Logic)
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NOT NULL,
        thumbnail VARCHAR(255),
        meta_desc TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Fix: Check for 'meta_desc' column specifically (The source of SQL Error 1054)
    $colCheck = $pdo->query("SHOW COLUMNS FROM posts LIKE 'meta_desc'");
    if ($colCheck->rowCount() == 0) {
        $pdo->exec("ALTER TABLE posts ADD COLUMN meta_desc TEXT NULL AFTER thumbnail");
        echo "<p style='color:blue; font-weight:bold;'>✓ RECOVERY: Added missing column 'meta_desc' to 'posts' table.</p>";
    } else {
        echo "<p style='color:green'>✓ Table 'posts' verified.</p>";
    }

    // 5. Messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        message TEXT NOT NULL,
        status ENUM('open', 'closed') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color:green'>✓ Table 'messages' verified.</p>";

    // 6. Promos
    $pdo->exec("CREATE TABLE IF NOT EXISTS promos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color:green'>✓ Table 'promos' verified.</p>";

    echo "<div style='margin-top:20px; padding:15px; background:#dcfce7; border:1px solid #22c55e; border-radius:8px; color:#15803d;'>
            <strong>System Ready!</strong> Database structure has been updated successfully.<br>
            The 'Unknown column meta_desc' error is now resolved.
          </div>";
          
    echo "<br><a href='admin.php' style='background:#2563eb; color:white; padding:12px 25px; text-decoration:none; border-radius:8px; font-weight:bold; display:inline-block;'>Return to Admin Panel</a>";
    echo "</div>";

} catch (PDOException $e) {
    die("<div style='color:red; font-family:sans-serif; text-align:center;'><h1>Installation Failed</h1><p>" . $e->getMessage() . "</p></div>");
}
?>",
      