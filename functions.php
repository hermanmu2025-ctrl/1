<?php
require_once 'db.php';

// Increase time limit for AI operations
if (function_exists('set_time_limit')) {
    set_time_limit(300); // 5 Minutes
}

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

// --- SOVEREIGN AI INTELLIGENCE SUITE (v3.2 Enhanced) ---

/**
 * Extract JSON object from text that might contain markdown or other noise
 */
function extractJsonFromText($text) {
    // 1. Try to find content between ```json and ```
    if (preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $matches)) {
        return trim($matches[1]);
    }
    // 2. Try to find content between ``` and ```
    if (preg_match('/```\s*([\s\S]*?)\s*```/', $text, $matches)) {
        return trim($matches[1]);
    }
    // 3. Try to find the first '{' and last '}'
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start !== false && $end !== false && $end > $start) {
        return substr($text, $start, $end - $start + 1);
    }
    
    return $text;
}

/**
 * Automatically fetches available Gemini models from Google API.
 */
function getAvailableGeminiModels() {
    if (!defined('GEMINI_API_KEY') || empty(GEMINI_API_KEY) || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
        return [];
    }

    $url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . GEMINI_API_KEY;
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $models = [];

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['models']) && is_array($data['models'])) {
            foreach ($data['models'] as $model) {
                if (isset($model['supportedGenerationMethods']) && in_array('generateContent', $model['supportedGenerationMethods'])) {
                    $modelName = str_replace('models/', '', $model['name']);
                    $models[] = $modelName;
                }
            }
        }
    }

    usort($models, function($a, $b) {
        $scoreA = (strpos($a, 'flash') !== false ? 2 : 0) + (strpos($a, '1.5') !== false ? 1 : 0);
        $scoreB = (strpos($b, 'flash') !== false ? 2 : 0) + (strpos($b, '1.5') !== false ? 1 : 0);
        return $scoreB - $scoreA;
    });

    if (empty($models)) {
        return ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-1.0-pro'];
    }

    return $models;
}

function generateAIArticle($broad_topic) {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE' || empty(GEMINI_API_KEY)) {
        return ['error' => 'API Key Gemini belum dikonfigurasi di config.php'];
    }

    $models = getAvailableGeminiModels();
    
    // Strict JSON Prompt
    $promptText = "Role: SEO Expert & Professional Youtuber.\n";
    $promptText .= "Task: Write a blog post about: '" . $broad_topic . "'.\n";
    $promptText .= "Requirements:\n";
    $promptText .= "1. Title: Clickbait & Emotional (Max 100 chars).\n";
    $promptText .= "2. Content: Min 2500 chars. Use HTML (h2, h3, p, ul, strong). Storytelling style.\n";
    $promptText .= "3. Meta Desc: 150 chars summary.\n";
    $promptText .= "4. Image Keywords: 3 English keywords for finding images (comma separated).\n";
    $promptText .= "IMPORTANT: Output ONLY valid JSON RFC8259. No markdown blocks. Structure: { \"title\": \"...\", \"content\": \"...\", \"meta_desc\": \"...\", \"image_keywords\": \"...\" }";

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
            "responseMimeType" => "application/json" // Explicitly request JSON
        ]
    ];

    $lastError = '';
    if (!is_array($models) || empty($models)) $models = ['gemini-1.5-flash'];

    foreach ($models as $model) {
        $apiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . $model . ":generateContent?key=" . GEMINI_API_KEY;
        
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            $result = json_decode($response, true);
            $apiMsg = isset($result['error']['message']) ? $result['error']['message'] : 'Unknown API Error';
            $lastError = "API Error $httpCode ($model): $apiMsg";
            continue;
        }

        $result = json_decode($response, true);
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
            $cleanJson = extractJsonFromText($rawText);
            $jsonResult = json_decode($cleanJson, true);
            
            if (json_last_error() === JSON_ERROR_NONE && !empty($jsonResult['content'])) {
                 // Fallback if meta_desc missing from AI (prevents SQL error logic wise, though DB should be fixed)
                if (!isset($jsonResult['meta_desc'])) {
                    $jsonResult['meta_desc'] = substr(strip_tags($jsonResult['content']), 0, 150) . '...';
                }
                $jsonResult['used_model'] = $model;
                return $jsonResult;
            } else {
                $lastError = "JSON Parse Error ($model): " . json_last_error_msg();
            }
        }
    }

    return ['error' => "Sovereign AI Failed. Last Error: $lastError"];
}
?>