<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
    $pdo->exec($sql);
    
    $pdo->exec("USE " . DB_NAME);

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

    // 4. Posts (Enhanced for SEO)
    $pdo->exec("CREATE TABLE IF NOT EXISTS posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        content LONGTEXT NOT NULL,
        thumbnail VARCHAR(255),
        meta_desc TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 5. Messages
    $pdo->exec("CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        message TEXT NOT NULL,
        status ENUM('open', 'closed') DEFAULT 'open',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 6. Promos
    $pdo->exec("CREATE TABLE IF NOT EXISTS promos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        content TEXT NOT NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    echo "<div style='font-family:sans-serif; max-width:600px; margin:50px auto; padding:20px; border:1px solid #ddd; border-radius:10px; text-align:center;'>
            <h2 style='color:green;'>System Installed Successfully!</h2>
            <p>Database created. Tables updated with SEO support.</p>
            <p style='color:red; font-weight:bold;'>IMPORTANT: Delete this file now.</p>
            <a href='index.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Go to Home</a>
          </div>";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>