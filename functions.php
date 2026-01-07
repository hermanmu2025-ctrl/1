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

// --- SOVEREIGN AI INTELLIGENCE SUITE (v6.0 Professional SEO Architect) ---

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
    
    // PROFESSIONAL SEO & STORYTELLING PROMPT (ENHANCED V6.0)
    // Updated constraints: Double spacing, 2 images (prompts), Backlink included.
    
    $promptText = "Role: Senior SEO Content Writer & Storyteller Professional (Bahasa Indonesia).\n";
    $promptText .= "Task: Tulis artikel blog 'In-Depth' (Mendalam) dan sangat menarik tentang: '" . $broad_topic . "'.\n";
    $promptText .= "Audience: Youtuber Pemula, Content Creator, dan Digital Marketer.\n\n";
    
    $promptText .= "STRUKTUR & ATURAN PENULISAN (WAJIB DIPATUHI):\n";
    $promptText .= "1. GAYA BAHASA:\n";
    $promptText .= "   - Gunakan bahasa yang elegan, profesional, namun mengalir seperti bercerita (Storytelling).\n";
    $promptText .= "   - Hindari bahasa robot. Gunakan empati. Contoh: 'Pernahkah Anda merasa lelah...'.\n";
    $promptText .= "2. FORMATTING (SANGAT PENTING):\n";
    $promptText .= "   - Gunakan tag <h2> untuk Judul Bab. Judul Bab harus menarik (Clickable).\n";
    $promptText .= "   - Gunakan tag <p> untuk paragraf. \n";
    $promptText .= "   - JARAK ANTAR PARAGRAF: Pastikan setiap paragraf tidak terlalu panjang (maksimal 3 kalimat). Pisahkan ide dengan paragraf baru agar ada 'ruang napas' (whitespace) yang lega.\n";
    $promptText .= "3. PENEMPATAN GAMBAR & BACKLINK:\n";
    $promptText .= "   - Di tengah-tengah artikel (misalnya setelah Bab 2), WAJIB tuliskan placeholder ini persis: [[IMAGE_MID]]. Sistem saya akan menggantinya dengan gambar.\n";
    $promptText .= "   - SEO BACKLINK: Di dalam salah satu paragraf yang relevan, sisipkan 1 link HTML ke 'index.php' dengan anchor text variatif (contoh: 'Jasa Subscriber Terpercaya' atau 'Komunitas Youtuber'). Format: <a href='index.php' class='text-brand-600 font-bold underline'>Anchor Text Disini</a>.\n";
    $promptText .= "4. KONTEN VISUAL (IMAGE PROMPTS):\n";
    $promptText .= "   - Berikan 2 deskripsi prompt gambar (Bahasa Inggris) yang sangat detail, cinematic, dan photorealistic.\n";
    $promptText .= "   - Prompt 1: Untuk Thumbnail Utama (Harus sangat Eye Catching).\n";
    $promptText .= "   - Prompt 2: Untuk Gambar Tengah (Ilustrasi pendukung cerita).\n\n";

    $promptText .= "OUTPUT FORMAT: Valid JSON Only. Keys: 'title', 'content', 'meta_desc', 'img_prompt_main', 'img_prompt_mid'.\n";
    $promptText .= "- 'title': Judul Headline yang Premium & SEO Friendly.\n";
    $promptText .= "- 'content': Isi artikel lengkap format HTML (Termasuk placeholder [[IMAGE_MID]] dan Backlink).\n";
    $promptText .= "- 'meta_desc': Ringkasan SEO 150 karakter.\n";
    $promptText .= "- 'img_prompt_main': English prompt for main thumbnail (e.g., 'Cinematic shot of youtube studio, 8k, dramatic lighting').\n";
    $promptText .= "- 'img_prompt_mid': English prompt for middle content image (e.g., 'Close up of analytics graph on laptop screen, bokeh, professional').";

    $data = [
        "contents" => [["parts" => [["text" => $promptText]]]],
        "generationConfig" => [
            "temperature" => 0.75, // Balanced creativity and coherence
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        
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