<?php
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // If DB doesn't exist, allow install script to run, otherwise stop.
    if (strpos($_SERVER['SCRIPT_NAME'], 'install_db.php') === false) {
         die("<div style='font-family:sans-serif; text-align:center; padding:50px;'>Database connection failed. <br><br> <a href='install_db.php' style='background:#2563eb; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Run Installer</a></div>");
    }
}
?>