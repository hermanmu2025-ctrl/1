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

// --- SOVEREIGN AI CONTENT STUDIO (v2.1 Stable) ---
// Features: Multi-Model Fallback, Auto-Retry, JSON Enforcing
function generateAIArticle($broad_topic) {
    // 1. Validate API Key
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE' || empty(GEMINI_API_KEY)) {
        return ['error' => 'API Key Gemini belum dikonfigurasi di config.php'];
    }

    // List of models to try in order of preference
    // First: gemini-1.5-flash (Fast & Free Tier friendly)
    // Fallback: gemini-pro (Stable v1.0 model)
    $models = ['gemini-1.5-flash', 'gemini-pro'];

    $promptText = "Bertindaklah sebagai Pakar SEO dan Content Creator YouTube Senior. \n";
    $promptText .= "Tugas: Buat satu artikel blog lengkap berdasarkan topik: '" . $broad_topic . "'. \n";
    $promptText .= "Kriteria WAJIB: \n";
    $promptText .= "1. Judul: Clickbait, emosional, dan unik (Jangan pakai judul standar). \n";
    $promptText .= "2. Konten: Panjang minimum 2000 karakter. Gunakan format HTML (h2, h3, p, ul, li, strong). Gaya bahasa storytelling yang mengalir. \n";
    $promptText .= "3. Meta Description: 150 karakter untuk SEO. \n";
    $promptText .= "4. Image Keywords: 3 kata kunci bahasa inggris untuk pencarian gambar (comma separated). \n";
    $promptText .= "Output WAJIB Format JSON Murni tanpa markdown: { \"title\": \"...\", \"content\": \"...\", \"meta_desc\": \"...\", \"image_keywords\": \"...\" }";

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
             // Note: responseMimeType is supported in newer models, removed here for broader compatibility with gemini-pro fallback
        ],
        "safetySettings" => [
            [ "category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE" ]
        ]
    ];

    $lastError = '';

    foreach ($models as $model) {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . GEMINI_API_KEY;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $lastError = "Curl Error ($model): $curlErr";
            continue; // Try next model
        }

        $result = json_decode($response, true);

        if ($httpCode !== 200) {
            $apiMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            $lastError = "API Error $httpCode ($model): $apiMsg";
            
            // If 404 (Model not found) or 400 (Bad Request), try next model.
            // If 429 (Quota), stop immediately to avoid ban.
            if ($httpCode == 429) {
                return ['error' => 'Quota Exceeded (Free Tier Limit). Try again later.'];
            }
            continue; // Try next model
        }

        // Success Parsing
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean Markdown JSON wrapping if present
            $rawText = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
            $rawText = preg_replace('/^```\s*|\s*```$/', '', trim($rawText));
            
            $jsonResult = json_decode($rawText, true);
            
            if (json_last_error() === JSON_ERROR_NONE && !empty($jsonResult['content'])) {
                // Content Injection for length assurance
                if (strlen($jsonResult['content']) < 1000) {
                     $jsonResult['content'] .= "<hr><h3>Kesimpulan</h3><p>Konsistensi adalah kunci keberhasilan di YouTube. Jangan menyerah jika belum melihat hasil instan. Gunakan strategi yang telah dibahas di atas dan manfaatkan fitur Urat ID untuk mempercepat pertumbuhan channel Anda.</p>";
                }
                return $jsonResult; // Success return
            }
        }
    }

    // If loop finishes without return
    return ['error' => "Sovereign AI Failed. Details: $lastError"];
}
?>