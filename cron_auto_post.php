<?php
// CRON JOB: Run Once Daily to generate AI Content
require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';

echo "[AUTO POST] Starting Process...\n";

$topics = AI_TARGET_KEYWORDS;
$selected_topic = $topics[array_rand($topics)];

echo "[AUTO POST] Topic: $selected_topic\n";
$res = generateAIArticle($selected_topic);

if (isset($res['error'])) {
    die("[ERROR] " . $res['error'] . "\n");
}

$title = $res['title'];
$content = $res['content'];
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
$meta = $res['meta_desc'] ?? '';

// IMPROVED: Use Pollinations AI for Generative Image based on context
// This ensures the photo is 'menarik sesuai konten' as requested.
$image_prompt = urlencode($res['image_keywords'] . " high resolution, cinematic lighting, 8k, detailed, realistic");
$thumb = "https://image.pollinations.ai/prompt/" . $image_prompt . "?width=1200&height=800&nologo=true&seed=" . rand(1000,9999);

// Prevent duplicate slug
$check = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
$check->execute([$slug]);
if($check->rowCount() > 0) $slug .= '-' . time();

try {
    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $thumb, $meta]);
    echo "[SUCCESS] Published: $title\n";
} catch (Exception $e) {
    echo "[DB ERROR] " . $e->getMessage() . "\n";
}
?>