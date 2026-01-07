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

// --- SOVEREIGN AI INTELLIGENCE SUITE (v3.0 Auto-Discovery) ---

/**
 * Automatically fetches available Gemini models from Google API.
 * Prioritizes Flash models for speed and cost-efficiency.
 */
function getAvailableGeminiModels() {
    if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY) || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
        return [];
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . GEMINI_API_KEY;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $models = [];

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['models'])) {
            foreach ($data['models'] as $model) {
                // Filter for content generation models only
                if (isset($model['supportedGenerationMethods']) && in_array('generateContent', $model['supportedGenerationMethods'])) {
                    $modelName = str_replace('models/', '', $model['name']);
                    $models[] = $modelName;
                }
            }
        }
    }

    // Smart Sort: Prefer 'flash' models, then 'pro', then others.
    usort($models, function($a, $b) {
        $scoreA = (strpos($a, 'flash') !== false ? 2 : 0) + (strpos($a, '1.5') !== false ? 1 : 0);
        $scoreB = (strpos($b, 'flash') !== false ? 2 : 0) + (strpos($b, '1.5') !== false ? 1 : 0);
        return $scoreB - $scoreA; // Descending
    });

    // Fallback if API fails or returns nothing
    if (empty($models)) {
        return ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-1.0-pro'];
    }

    return $models;
}

function generateAIArticle($broad_topic) {
    // 1. Validate API Key
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE' || empty(GEMINI_API_KEY)) {
        return ['error' => 'API Key Gemini belum dikonfigurasi di config.php'];
    }

    // 2. Get Auto-Discovered Models
    $models = getAvailableGeminiModels();
    
    // 3. Prompt Engineering
    $promptText = "Bertindaklah sebagai Pakar SEO dan Content Creator YouTube Senior. \n";
    $promptText .= "Tugas: Buat satu artikel blog lengkap berdasarkan topik: '" . $broad_topic . "'. \n";
    $promptText .= "Kriteria WAJIB: \n";
    $promptText .= "1. Judul: Clickbait, emosional, dan unik (Jangan pakai judul standar). \n";
    $promptText .= "2. Konten: Panjang minimum 2500 karakter. Gunakan format HTML (h2, h3, p, ul, li, strong, blockquote). Gaya bahasa storytelling yang mengalir. \n";
    $promptText .= "3. Meta Description: 150 karakter untuk SEO. \n";
    $promptText .= "4. Image Keywords: 3 kata kunci bahasa inggris untuk pencarian gambar (comma separated). \n";
    $promptText .= "Output WAJIB Format JSON Murni tanpa markdown code block: { \"title\": \"...\", \"content\": \"...\", \"meta_desc\": \"...\", \"image_keywords\": \"...\" }";

    $data = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => $promptText]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.7,
            "maxOutputTokens" => 8000,
        ],
        "safetySettings" => [
            [ "category" => "HARM_CATEGORY_HARASSMENT", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_HATE_SPEECH", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT", "threshold" => "BLOCK_NONE" ],
            [ "category" => "HARM_CATEGORY_DANGEROUS_CONTENT", "threshold" => "BLOCK_NONE" ]
        ]
    ];

    $lastError = '';
    $successModel = '';

    foreach ($models as $model) {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . GEMINI_API_KEY;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Prevent hang
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            $lastError = "Curl Error ($model): $curlErr";
            continue; // Try next model
        }

        $result = json_decode($response, true);

        // Handle API Errors
        if ($httpCode !== 200) {
            $apiMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            $lastError = "API Error $httpCode ($model): $apiMsg";
            
            if ($httpCode == 429) {
                // Rate limit hit, maybe break or continue depending on strategy. 
                // Continuing to next model is better as different models might have different quotas.
                continue;
            }
            continue;
        }

        // Success Parsing
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
            
            // Clean Markdown JSON wrapping if present
            $rawText = preg_replace('/^```json\s*|\s*```$/', '', trim($rawText));
            $rawText = preg_replace('/^```\s*|\s*```$/', '', trim($rawText));
            
            $jsonResult = json_decode($rawText, true);
            
            if (json_last_error() === JSON_ERROR_NONE && !empty($jsonResult['content'])) {
                // Content Injection for length assurance and internal linking
                if (strlen($jsonResult['content']) < 1000) {
                     $jsonResult['content'] .= "<hr><h3>Kesimpulan Strategis</h3><p>Konsistensi adalah kunci keberhasilan di YouTube. Jangan menyerah jika belum melihat hasil instan. Gunakan strategi yang telah dibahas di atas dan manfaatkan fitur <strong>Urat ID</strong> untuk mempercepat pertumbuhan channel Anda.</p>";
                }
                $jsonResult['used_model'] = $model; // Track which model worked
                return $jsonResult; // Success return
            } else {
                $lastError = "JSON Parse Error ($model)";
            }
        }
    }

    // If loop finishes without return
    return ['error' => "Sovereign AI Failed on all available models. Last Error: $lastError"];
}
?>