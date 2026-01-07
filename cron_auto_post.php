<?php
// CRON JOB: Run Once Daily to generate AI Content (Enhanced V2)
require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';

echo "[AUTO POST] Starting Sovereign AI Process...\n";

$topics = AI_TARGET_KEYWORDS;
$selected_topic = $topics[array_rand($topics)];

echo "[AUTO POST] Topic Selected: $selected_topic\n";
$res = generateAIArticle($selected_topic);

if (isset($res['error'])) {
    die("[ERROR] " . $res['error'] . "\n");
}

$title = $res['title'];
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
$meta = $res['meta_desc'] ?? '';

// 1. GENERATE IMAGE URLS (Pollinations AI)
// Main Thumbnail
$prompt_main = urlencode($res['img_prompt_main'] . " high resolution, cinematic lighting, 8k, detailed, realistic, masterpiece, clean composition");
$thumb_url = "https://image.pollinations.ai/prompt/" . $prompt_main . "?width=1280&height=720&nologo=true&seed=" . rand(1000,9999);

// Middle Image
$prompt_mid = urlencode($res['img_prompt_mid'] . " high quality, photorealistic, 4k, ambient lighting, professional photography");
$mid_url = "https://image.pollinations.ai/prompt/" . $prompt_mid . "?width=1000&height=600&nologo=true&seed=" . rand(1000,9999);

// 2. PROCESS CONTENT HTML
// Replace placeholder [[IMAGE_MID]] with actual HTML Image Tag
$html_mid_image = '<figure class="my-12"><img src="' . $mid_url . '" alt="' . htmlspecialchars($title) . '" class="w-full rounded-2xl shadow-xl border border-slate-100"><figcaption class="text-center text-sm text-slate-500 mt-3">' . htmlspecialchars($title) . '</figcaption></figure>';

$final_content = str_replace('[[IMAGE_MID]]', $html_mid_image, $res['content']);

// Prevent duplicate slug
$check = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
$check->execute([$slug]);
if($check->rowCount() > 0) $slug .= '-' . time();

try {
    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $final_content, $thumb_url, $meta]);
    echo "[SUCCESS] Published: $title\n";
    echo "[INFO] 2 Images Generated & Backlink Inserted.\n";
} catch (Exception $e) {
    echo "[DB ERROR] " . $e->getMessage() . "\n";
}
?>