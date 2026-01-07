<?php
require_once 'config.php';

/**
 * DB INSTALLER - Run this once to set up the database.
 */
try {
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $pdo->exec($sql);
    $pdo->exec("USE " . DB_NAME);

    // Read Schema from file
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    if (!$schema) die("Error: schema.sql not found.");

    $statements = array_filter(array_map('trim', explode(';', $schema)));

    foreach ($statements as $stmt) {
        if (!empty($stmt)) {
            $pdo->exec($stmt);
        }
    }

    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
            <h2 style='color:green;'>Installation Success!</h2>
            <p>Database structure has been created/updated.</p>
            <a href='index.php' style='text-decoration:underline;'>Go to Home</a>
          </div>";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>