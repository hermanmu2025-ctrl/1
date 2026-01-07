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

// --- UPDATED AI LOGIC (FIXED FOR FREE TIER & AUTOMATION) ---
function generateAIArticle($broad_topic) {
    // 1. Validate API Key
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE' || empty(GEMINI_API_KEY)) {
        return ['error' => 'API Key Gemini belum dikonfigurasi di config.php'];
    }

    $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . GEMINI_API_KEY;
    
    // 2. Optimized Prompt for Free Tier
    // Removed complex formatting instructions that might confuse the model, relying on JSON schema instead.
    $promptText = "Bertindaklah sebagai Pakar SEO dan Content Creator YouTube Senior. \n";
    $promptText .= "Tugas: Buat satu artikel blog lengkap berdasarkan topik: '" . $broad_topic . "'. \n";
    $promptText .= "Kriteria WAJIB: \n";
    $promptText .= "1. Judul: Clickbait, emosional, dan unik (Jangan pakai judul standar). \n";
    $promptText .= "2. Konten: Panjang minimum 2500 karakter. Gunakan format HTML (h2, h3, p, ul, li, strong). Gaya bahasa storytelling. \n";
    $promptText .= "3. Meta Description: 150 karakter untuk SEO. \n";
    $promptText .= "4. Image Keywords: 3 kata kunci bahasa inggris untuk pencarian gambar (comma separated). \n";
    $promptText .= "Output WAJIB Format JSON: { \"title\": \"...\", \"content\": \"...\", \"meta_desc\": \"...\", \"image_keywords\": \"...\" }";

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
            "maxOutputTokens" => 8000,
            "responseMimeType" => "application/json" // Force JSON output for stability
        ],
        // Safety Settings to prevent blocking harmless 'Make Money' topics
        "safetySettings" => [
            [ "category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE" ]
        ]
    ];

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    // Disable SSL verify temporarily if on local server/hosting with cert issues
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        return ['error' => 'Curl Connection Error: ' . curl_error($ch)];
    }
    
    curl_close($ch);

    $result = json_decode($response, true);

    // 3. Detailed Error Handling
    if ($httpCode !== 200) {
        $errorMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
        $errorCode = isset($result['error']['code']) ? $result['error']['code'] : $httpCode;
        
        if ($httpCode == 429) {
            return ['error' => 'Quota Exceeded (Free Tier Limit). Coba lagi nanti.'];
        }
        return ['error' => "API Error ($errorCode): $errorMsg"];
    }

    // 4. Parse Result
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Native JSON response usually doesn't need regex cleanup, but just in case:
        $rawText = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
        
        $jsonResult = json_decode($rawText, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            // Content Length Check & Boilerplate Injection if too short
            if (empty($jsonResult['content']) || strlen($jsonResult['content']) < 1500) {
                 $jsonResult['content'] .= "<hr><h3>Kesimpulan</h3><p>Konsistensi adalah kunci keberhasilan di YouTube. Jangan menyerah jika belum melihat hasil instan. Gunakan strategi yang telah dibahas di atas dan manfaatkan fitur Urat ID untuk mempercepat pertumbuhan channel Anda.</p>";
            }
            return $jsonResult;
        } else {
             return ['error' => 'Gagal parsing JSON dari AI. Struktur respon tidak valid.'];
        }
    }

    // Check for Safety Blocking
    if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] !== 'STOP') {
        return ['error' => 'AI Blocked Content: ' . $result['candidates'][0]['finishReason']];
    }

    return ['error' => 'Respon AI kosong atau format tidak dikenali.'];
}
?>