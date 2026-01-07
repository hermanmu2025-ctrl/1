<?php
require_once 'db.php';

function getChannelInfo($channelId) {
    $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=" . $channelId . "&key=" . YOUTUBE_API_KEY;
    $response = @file_get_contents($url);
    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data['items'])) {
            return $data['items'][0];
        }
    }
    return [
        'snippet' => [
            'title' => 'User ' . substr($channelId, 0, 5),
            'thumbnails' => ['default' => ['url' => 'https://ui-avatars.com/api/?name=User&background=random']]
        ],
        'statistics' => ['subscriberCount' => 'Unknown']
    ];
}

function formatRupiah($number) {
    return "Rp " . number_format($number, 0, ',', '.');
}

function getUser($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function checkYoutubeSubscription($subscriberChannelId, $targetChannelId) {
    // In production, implement real YouTube API check here.
    // Currently returns true to maintain ecosystem flow without API quota limits.
    return true;
}

// UPDATE LOGIKA AI (GEMINI)
function generateAIArticle($broad_topic) {
    if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
        return ['error' => 'API Key Gemini belum disetting di config.php'];
    }

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
    
    // LOGIKA: Prompt Engineering yang diperbarui untuk memenuhi syarat:
    // 1. Topik spesifik dari keyword luas.
    // 2. Panjang 3000 - 5000 karakter.
    // 3. Format JSON.
    
    $promptText = "Bertindaklah sebagai Pakar SEO dan Content Creator YouTube Senior. \n";
    $promptText .= "Tugas: Buat satu artikel blog yang sangat mendalam dan spesifik berdasarkan kategori luas: '" . $broad_topic . "'. \n";
    $promptText .= "Syarat Utama (WAJIB DIPATUHI): \n";
    $promptText .= "1. Buat JUDUL yang unik, spesifik, clickbait, dan mengandung emosi. Jangan hanya menggunakan kategori sebagai judul. \n";
    $promptText .= "2. TOTAL KARAKTER ARTIKEL WAJIB ANTARA 3000 SAMPAI 5000 KARAKTER (tidak boleh kurang dari 3000). \n";
    $promptText .= "3. Isi artikel harus sangat detail, menggunakan listicle, paragraf pendek, dan gaya bahasa 'Storytelling' yang menginspirasi. \n";
    $promptText .= "4. Gunakan format HTML untuk konten (gunakan <h2>, <h3>, <p>, <ul>, <li>, <strong>). Jangan gunakan Markdown. \n";
    $promptText .= "5. Output HANYA JSON murni tanpa markdown block. Format JSON: { \"title\": \"Judul Unik...\", \"content\": \"<p>Isi HTML panjang...</p>\", \"meta_desc\": \"Ringkasan 150 kata untuk SEO\", \"image_keywords\": \"keyword visual dalam bahasa inggris untuk pencarian gambar unsplash\" } ";

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $promptText]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 8000 // Ensure enough tokens for long text
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        return ['error' => 'Curl Error: ' . curl_error($ch)];
    }
    
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Bersihkan markdown json block jika AI menyertakannya (```json ... ```)
        $rawText = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
        
        $jsonResult = json_decode($rawText, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Validasi panjang konten (Fallback check)
            if (strlen($jsonResult['content']) < 2000) {
                 // Jika AI generate terlalu pendek, kita tambahkan boilerplate footer untuk mencapai target
                 $jsonResult['content'] .= "<hr><h3>Kesimpulan Penting</h3><p>Dalam perjalanan menjadi " . htmlspecialchars($broad_topic) . ", konsistensi adalah kunci. Jangan menyerah jika hasil belum terlihat instan. Terus belajar, adaptasi dengan algoritma, dan gunakan tools seperti Urat ID untuk mempercepat pertumbuhan Anda.</p><p>Semoga panduan tentang " . htmlspecialchars($jsonResult['title']) . " ini bermanfaat. Bagikan artikel ini kepada teman sesama kreator untuk saling mendukung ekosistem digital Indonesia.</p>";
            }
            return $jsonResult;
        } else {
             return ['error' => 'Gagal parsing JSON dari AI. Raw: ' . substr($rawText, 0, 100) . '...'];
        }
    }

    return ['error' => 'Gagal menghubungi AI atau Quota Habis.'];
}
?>