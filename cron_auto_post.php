<?php
// FILE: Logic Otomatisasi Postingan AI
// Jalankan file ini menggunakan Cron Job (Setiap hari sekali, misal jam 01:00 pagi)
// Command: /usr/local/bin/php /home/username/public_html/cron_auto_post.php

require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';

// Set headers to ensure plain text output for cron logs
header('Content-Type: text/plain');

echo "[AUTO POST] Starting Process at " . date('Y-m-d H:i:s') . "...\n";

// 1. Cek kuota posting harian (Optional: Batasi 2 post per hari)
$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$postsToday = $stmt->fetchColumn();

if ($postsToday >= 2) {
    die("[AUTO POST] Limit harian tercapai ($postsToday posts). Skipping.\n");
}

// 2. Pilih Topik
$allowed_topics = AI_TARGET_KEYWORDS;
if (empty($allowed_topics)) {
    die("[AUTO POST] Error: Daftar Keyword kosong di config.php\n");
}

$random_index = array_rand($allowed_topics);
$selected_topic = $allowed_topics[$random_index];

echo "[AUTO POST] Topic Selected: " . $selected_topic . "\n";

// 3. Request ke AI (Uses Smart Model Selection)
echo "[AUTO POST] Requesting content from Sovereign AI...\n";
$aiResult = generateAIArticle($selected_topic);

// 4. Error Handling Log
if (isset($aiResult['error'])) {
    echo "[AUTO POST] FAILED TO GENERATE:\n";
    echo "Reason: " . $aiResult['error'] . "\n";
    
    // Simple Retry Logic for Rate Limits (Wait 10s and try once more)
    if (strpos($aiResult['error'], 'Quota') !== false || strpos($aiResult['error'], '429') !== false) {
        echo "[AUTO POST] Retrying in 10 seconds...\n";
        sleep(10);
        $aiResult = generateAIArticle($selected_topic);
        if (isset($aiResult['error'])) {
            die("[AUTO POST] RETRY FAILED: " . $aiResult['error'] . "\n");
        }
    } else {
        die("[AUTO POST] Critical Error. Stopping.\n");
    }
}

// 5. Processing Success Data
$title = $aiResult['title'];
$content = $aiResult['content'];
$meta_desc = $aiResult['meta_desc'] ?? substr(strip_tags($content), 0, 150);

// Slug Generation
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
$checkSlug = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
$checkSlug->execute([$slug]);
if ($checkSlug->rowCount() > 0) {
    $slug .= '-' . time();
}

// Image Generation
$keywords = isset($aiResult['image_keywords']) ? $aiResult['image_keywords'] : 'digital marketing';
$image_keywords = urlencode($keywords);
$thumb_url = "https://source.unsplash.com/1200x800/?" . $image_keywords . "&sig=" . time();

// Character Count Log
$char_count = strlen(strip_tags($content));
echo "[AUTO POST] Generated $char_count chars using model: " . ($aiResult['used_model'] ?? 'Unknown') . "\n";

// 6. Save to DB
try {
    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $thumb_url, $meta_desc]);
    
    echo "[AUTO POST] SUCCESS! Article published: $title\n";
    echo "[AUTO POST] Slug: $slug\n";
} catch (PDOException $e) {
    echo "[AUTO POST] DB Error: " . $e->getMessage() . "\n";
}
?>