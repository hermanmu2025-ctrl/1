<?php
// Configuration & Constants

define('DB_HOST', 'localhost');

define('DB_NAME', 'sub4sub_db');

define('DB_USER', 'root');

define('DB_PASS', '');

// API Configuration
define('YOUTUBE_API_KEY', 'AIzaSyCNgREc0XJdaFSgWAZMmr51j6BBiBioxT8');

// AI Configuration (Gemini)
// Replace with your actual Gemini API Key
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE'); 

// AI Logic Configuration
// Target Keywords for Marketing-Oriented Content
define('AI_TARGET_KEYWORDS', [
    'Strategi Youtuber Pemula 2024',
    'Cara Monetisasi YouTube Cepat',
    'Tips SEO Video YouTube',
    'Personal Branding untuk Content Creator',
    'Cara Menambah Jam Tayang Organik',
    'Ide Konten Evergreen YouTube',
    'Peralatan Youtuber Murah Berkualitas',
    'Algoritma YouTube Short Terbaru'
]);

// Admin Security
define('ADMIN_PASSWORD', 'Amnet123');

// Business Logic
define('MIN_CAMPAIGN_BALANCE', 1000);
define('PRICE_PER_SUB', 105);
define('REWARD_PER_SUB', 52.5);

// User Welcome Bonus (Strategy: High Acquisition)
define('WELCOME_BONUS', 1000);

// Bank Details
define('BANK_ACCOUNT_NUMBER', '227801010326500');
define('BANK_NAME', 'BRI');
define('BANK_HOLDER', 'Admin Urat ID');

// Security & Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>