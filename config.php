<?php
// Configuration & Constants

define('DB_HOST', 'localhost');
define('DB_NAME', 'sub4sub_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// API Configuration (Ensure these keys are valid or use Quota-Free fallbacks in functions.php)
define('YOUTUBE_API_KEY', 'AIzaSyCNgREc0XJdaFSgWAZMmr51j6BBiBioxT8');

// Business Logic
define('MIN_CAMPAIGN_BALANCE', 1000); // Minimum balance to be shown in campaign
define('PRICE_PER_SUB', 105); // Deducted from channel owner
define('REWARD_PER_SUB', 52.5); // 50% of price given to subscriber

// Bank Details (Updated as per request)
define('BANK_ACCOUNT_NUMBER', '227801010326500');
define('BANK_NAME', 'BRI');
define('BANK_HOLDER', 'Admin Sub4Sub');

// Security
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>