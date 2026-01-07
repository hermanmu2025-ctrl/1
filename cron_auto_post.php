<?php
// FILE BARU: Logic Otomatisasi Postingan AI
// Jalankan file ini menggunakan Cron Job (Setiap hari sekali, misal jam 01:00 pagi)
// Command: /usr/local/bin/php /path/to/cron_auto_post.php

require_once 'db.php';
require_once 'functions.php';
require_once 'config.php';

echo "[AUTO POST] Starting Process...\n";

// 1. Cek apakah hari ini sudah posting otomatis?
// Logika: Kita cek apakah ada postingan yang dibuat hari ini.
// Catatan: Jika ingin benar-benar 1 post per hari dari AI, kita bisa tambahkan flag di DB. 
// Namun untuk simplifikasi, kita cek count post hari ini.

$stmt = $pdo->prepare("SELECT COUNT(*) FROM posts WHERE DATE(created_at) = CURDATE()");
$stmt->execute();
$postsToday = $stmt->fetchColumn();

// Jika ingin memaksa 1 post per hari (uncomment baris bawah jika ingin strict)
// if ($postsToday > 0) die("[AUTO POST] Sudah ada postingan hari ini. Skipping.\n");

// 2. Pilih Topik Secara Acak dari Config
$allowed_topics = AI_TARGET_KEYWORDS;
if (empty($allowed_topics)) {
    die("[AUTO POST] Error: Daftar Keyword kosong di config.php\n");
}

// Acak index
$random_index = array_rand($allowed_topics);
$selected_topic = $allowed_topics[$random_index];

echo "[AUTO POST] Topic Selected: " . $selected_topic . "\n";

// 3. Request ke AI
echo "[AUTO POST] Requesting content from Gemini AI... (Please wait)\n";
$aiResult = generateAIArticle($selected_topic);

if (isset($aiResult['error'])) {
    die("[AUTO POST] FAILED: " . $aiResult['error'] . "\n");
}

// 4. Proses Hasil AI
$title = $aiResult['title'];
$content = $aiResult['content'];
$meta_desc = $aiResult['meta_desc'];

// Buat Slug
$slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
// Hindari duplikat slug
$checkSlug = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
$checkSlug->execute([$slug]);
if ($checkSlug->rowCount() > 0) {
    $slug .= '-' . time();
}

// Generate Gambar (Unsplash Source based on AI Keywords)
$image_keywords = urlencode($aiResult['image_keywords']);
// Fallback keyword jika kosong
if (empty($image_keywords)) $image_keywords = 'technology,youtube';
$thumb_url = "https://source.unsplash.com/1200x800/?" . $image_keywords . "&sig=" . time(); // Add sig to prevent caching

// Hitung Karakter (Validasi Logika)
$char_count = strlen(strip_tags($content));
echo "[AUTO POST] Character Count: " . $char_count . "\n";

// 5. Simpan ke Database
try {
    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $thumb_url, $meta_desc]);
    
    echo "[AUTO POST] SUCCESS! Article published: $title\n";
} catch (PDOException $e) {
    echo "[AUTO POST] DB Error: " . $e->getMessage() . "\n";
}
?>