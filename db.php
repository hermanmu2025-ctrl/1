<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Graceful Error Handling during Installation Check
    if (strpos($_SERVER['SCRIPT_NAME'], 'install_db.php') === false) {
         die("<div style='font-family:sans-serif; text-align:center; padding:100px; background:#f8fafc; color:#334155;'>
                <h1 style='margin-bottom:10px;'>System Maintenance</h1>
                <p>Database connection is currently unavailable.</p>
                <br> 
                <a href='install_db.php' style='background:#2563eb; color:white; padding:12px 25px; text-decoration:none; border-radius:8px; font-weight:bold;'>Run System Installer</a>
             </div>");
    }
}
?>