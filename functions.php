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
    // In production, implement real YouTube API check here
    // Requires OAuth2 usually. For now, assume true to allow logic flow.
    return true;
}

// AI Content Generator Function (Gemini)
function generateAIArticle($topic) {
    if (GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
        return ['error' => 'API Key Gemini belum disetting di config.php'];
    }

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
    
    $promptText = "Buat artikel blog lengkap untuk website jasa subscriber Youtube. \n";
    $promptText .= "Topik: " . $topic . " (khususnya tentang Youtube Subscriber Growth). \n";
    $promptText .= "Instruksi: \n";
    $promptText .= "1. Judul harus Clickbait & Marketing Friendly. \n";
    $promptText .= "2. Panjang artikel MINIMAL 3000 karakter dan MAKSIMAL 5000 karakter. \n";
    $promptText .= "3. Gaya bahasa: Profesional, Marketing, Persuasif, dan SEO Optimized. \n";
    $promptText .= "4. Sertakan Heading (H2, H3) dalam format HTML (jangan markdown). \n";
    $promptText .= "5. Output harus berupa JSON dengan format: { \"title\": \"Judul...\", \"content\": \"<p>Isi artikel...</p>\", \"meta_desc\": \"Deskripsi singkat 150 kata untuk SEO\", \"image_keywords\": \"keyword inggris untuk gambar\" } ";

    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $promptText]
                ]
            ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    
    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Bersihkan markdown json block jika ada
        $rawText = str_replace(['```json', '```'], '', $rawText);
        return json_decode($rawText, true);
    }

    return ['error' => 'Gagal menghubungi AI.'];
}
?>