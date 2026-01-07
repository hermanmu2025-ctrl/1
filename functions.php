<?php
require_once 'db.php';

// Increase time limit for AI operations
if (function_exists('set_time_limit')) {
    set_time_limit(300);
}

function getChannelInfo($channelId) {
    // Production: Use real API. Dev: Simulation to save quota.
    $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet,statistics&id=" . $channelId . "&key=" . YOUTUBE_API_KEY;
    $response = @file_get_contents($url);
    
    if ($response) {
        $data = json_decode($response, true);
        if (!empty($data['items'])) {
            return $data['items'][0];
        }
    }
    
    // Fallback Simulation
    return [
        'snippet' => [
            'title' => 'User ' . substr($channelId, 0, 5),
            'thumbnails' => ['default' => ['url' => 'https://ui-avatars.com/api/?name=User&background=random&color=fff']]
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
    return true;
}

// --- SOVEREIGN AI INTELLIGENCE SUITE (v5.0 Enhanced Storyteller) ---

function extractJsonFromText($text) {
    if (preg_match('/```json\s*([\s\S]*?)\s*```/', $text, $matches)) {
        return trim($matches[1]);
    }
    if (preg_match('/```\s*([\s\S]*?)\s*```/', $text, $matches)) {
        return trim($matches[1]);
    }
    $start = strpos($text, '{');
    $end = strrpos($text, '}');
    if ($start !== false && $end !== false && $end > $start) {
        return substr($text, $start, $end - $start + 1);
    }
    return $text;
}

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
    // Prioritize Flash models for speed
    usort($models, function($a, $b) {
        return (strpos($b, 'flash') !== false) <=> (strpos($a, 'flash') !== false);
    });
    
    return !empty($models) ? $models : ['gemini-1.5-flash'];
}

function generateAIArticle($broad_topic) {
    if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE') {
        return ['error' => 'API Key Config Missing'];
    }

    $models = getAvailableGeminiModels();
    
    // PROFESSIONAL STORYTELLER PROMPT (ENHANCED V5.0)
    $promptText = "Role: Penulis Konten Profesional & Storyteller (Ahli Bahasa Indonesia yang Mengalir & Rapi).\n";
    $promptText .= "Task: Tulis artikel blog yang sangat menarik, humanis, dan informatif tentang: '" . $broad_topic . "'.\n";
    $promptText .= "Target Audience: Konten Kreator & Youtuber Pemula Indonesia.\n\n";
    
    $promptText .= "ATURAN FORMAT & GAYA BAHASA (WAJIB DIPATUHI):\n";
    $promptText .= "1. GAYA BAHASA: Gunakan bahasa yang 'manusiawi', bercerita (storytelling), tidak kaku seperti robot. Gunakan kata ganti 'kita' atau 'kamu' agar akrab.\n";
    $promptText .= "2. KERAPIAN & SPASI: Jangan membuat paragraf panjang (Wall of Text). Satu paragraf maksimal 3-4 kalimat. Berikan jeda nafas bagi pembaca.\n";
    $promptText .= "3. STRUKTUR ARTIKEL (GUNAKAN TAG HTML):\n";
    $promptText .= "   - Gunakan <h2>Bab 1. [Judul Subtopik Menarik]</h2> untuk setiap bagian utama.\n";
    $promptText .= "   - Gunakan <h2>Bab 2. [Judul Subtopik Menarik]</h2> untuk bagian selanjutnya.\n";
    $promptText .= "   - Gunakan <p> untuk setiap paragraf. Pastikan tag penutup </p> ada.\n";
    $promptText .= "   - Gunakan <h3> untuk poin-poin detail jika perlu.\n";
    $promptText .= "4. CONTOH ALUR TULISAN:\n";
    $promptText .= "   (Intro): \"Pernahkah Anda merasa lelah membuat konten tapi views tetap sepi? Tenang, Anda tidak sendirian...\"\n";
    $promptText .= "   (Body): \"<h2>Bab 1. Memahami Algoritma Sebenarnya</h2><p>Banyak yang salah paham...\"\n";
    $promptText .= "5. KONTEN VISUAL: Berikan deskripsi prompt gambar (image_keywords) dalam bahasa Inggris yang sangat detail, cinematic, dan photorealistic untuk Unsplash/AI generator.\n\n";

    $promptText .= "OUTPUT FORMAT: Valid JSON Only. Keys: 'title', 'content', 'meta_desc', 'image_keywords'.\n";
    $promptText .= "- 'title': Judul artikel yang Clickbait tapi Jujur (Bahasa Indonesia).\n";
    $promptText .= "- 'content': String HTML lengkap artikel (isi artikel saja, tanpa judul ulang).\n";
    $promptText .= "- 'meta_desc': Deskripsi singkat untuk SEO (max 150 chars).\n";
    $promptText .= "- 'image_keywords': English description for image generation (e.g., 'Cinematic shot of a young youtuber recording video in cozy studio, warm lighting, 8k, highly detailed').";

    $data = [
        "contents" => [["parts" => [["text" => $promptText]]]],
        "generationConfig" => [
            "temperature" => 0.8, // Slightly higher creative freedom
            "maxOutputTokens" => 8000,
            "responseMimeType" => "application/json"
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 90); // Increased timeout for better quality
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                $rawText = $result['candidates'][0]['content']['parts'][0]['text'];
                $cleanJson = extractJsonFromText($rawText);
                $jsonResult = json_decode($cleanJson, true);
                
                if (json_last_error() === JSON_ERROR_NONE && !empty($jsonResult['content'])) {
                    $jsonResult['used_model'] = $model;
                    if (!isset($jsonResult['meta_desc'])) $jsonResult['meta_desc'] = substr(strip_tags($jsonResult['content']), 0, 150) . '...';
                    return $jsonResult;
                }
            }
        } else {
             $lastError = "HTTP $httpCode on $model";
        }
    }

    return ['error' => "All AI models failed. $lastError"];
}
?>