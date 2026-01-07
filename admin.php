<?php
require_once 'functions.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$msg = '';

// Action: Approve Deposit
if (isset($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $trx = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending'");
    $trx->execute([$id]);
    $data = $trx->fetch();

    if ($data) {
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE transactions SET status = 'approved' WHERE id = ?")->execute([$id]);
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$data['amount'], $data['user_id']]);
        $pdo->commit();
        $msg = "Deposit Approved successfully.";
    }
}

// Action: Trigger AI (Enhanced V2 Logic)
if (isset($_POST['trigger_ai'])) {
    $topics = AI_TARGET_KEYWORDS;
    $topic = $topics[array_rand($topics)];
    $res = generateAIArticle($topic);
    
    if (!isset($res['error'])) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $res['title'])));
        
        // 1. Generate Main Thumbnail URL
        $prompt_main = urlencode($res['img_prompt_main'] . " high quality, photorealistic, 4k, cinematic lighting, masterpiece, no text, clean");
        $thumb_url = "https://image.pollinations.ai/prompt/" . $prompt_main . "?width=1280&height=720&nologo=true&seed=" . rand(1000,9999);
        
        // 2. Generate Middle Image URL
        $prompt_mid = urlencode($res['img_prompt_mid'] . " high quality, detail shot, photorealistic, 4k, ambient lighting, no text");
        $mid_url = "https://image.pollinations.ai/prompt/" . $prompt_mid . "?width=1000&height=600&nologo=true&seed=" . rand(1000,9999);
        
        // 3. Process Content: Inject Middle Image
        // We replace the [[IMAGE_MID]] placeholder with a styled img tag
        $html_mid_image = '<figure class="my-10"><img src="' . $mid_url . '" alt="' . htmlspecialchars($res['title']) . ' detail" class="w-full rounded-2xl shadow-xl hover:scale-[1.01] transition duration-500"><figcaption class="text-center text-sm text-slate-500 mt-2 italic">Ilustrasi: ' . htmlspecialchars($res['title']) . '</figcaption></figure>';
        
        $final_content = str_replace('[[IMAGE_MID]]', $html_mid_image, $res['content']);

        // Slug uniqueness check
        $chk = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
        $chk->execute([$slug]);
        if($chk->rowCount() > 0) $slug .= '-' . time();

        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$res['title'], $slug, $final_content, $thumb_url, $res['meta_desc']]);
        $msg = "Professional AI Article Generated: " . $res['title'];
    } else {
        $msg = "AI Error: " . $res['error'];
    }
}

$pending = $pdo->query("SELECT t.*, u.channel_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type='deposit' AND t.status='pending'")->fetchAll();
$users_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$page_title = "Admin Control Panel";
include 'header.php';
?>

<div class="container mx-auto px-6 py-12">
    
    <!-- Admin Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12 bg-slate-900 text-white p-10 rounded-[2rem] shadow-2xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-red-600 rounded-full blur-[100px] opacity-20"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-2">
                <span class="bg-red-600 text-xs font-bold px-2 py-1 rounded">ADMINISTRATOR</span>
                <span class="text-slate-400 text-sm">System v6.0 (Sovereign AI)</span>
            </div>
            <h1 class="text-3xl font-bold">Control Center</h1>
            <p class="text-slate-400">Total Registered Users: <?= number_format($users_count) ?></p>
        </div>
        <div class="flex gap-3 relative z-10">
            <form method="POST">
                <button name="trigger_ai" class="bg-white/10 hover:bg-white/20 border border-white/10 text-white px-6 py-3 rounded-xl font-bold flex items-center gap-2 transition">
                    <i data-lucide="bot" class="w-5 h-5"></i> Generate New Article
                </button>
            </form>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-xl font-bold transition flex items-center gap-2">
                <i data-lucide="log-out" class="w-5 h-5"></i> Logout
            </a>
        </div>
    </div>

    <?php if($msg): ?>
        <div class="bg-green-100 border border-green-200 text-green-800 p-6 rounded-2xl font-bold mb-8 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-6 h-6"></i> <?= $msg ?>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Deposit Table -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2rem] shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="wallet" class="w-5 h-5 text-slate-400"></i> Deposit Requests
                    </h2>
                    <span class="bg-orange-100 text-orange-700 text-xs font-bold px-3 py-1 rounded-full"><?= count($pending) ?> Pending</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs tracking-wider">
                            <tr>
                                <th class="px-8 py-4">User Channel</th>
                                <th class="px-8 py-4">Amount</th>
                                <th class="px-8 py-4">Proof</th>
                                <th class="px-8 py-4">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach($pending as $p): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-8 py-5 font-bold text-slate-700"><?= htmlspecialchars($p['channel_name']) ?></td>
                                <td class="px-8 py-5 text-green-600 font-extrabold"><?= formatRupiah($p['amount']) ?></td>
                                <td class="px-8 py-5">
                                    <a href="<?= $p['proof_img'] ?>" target="_blank" class="text-brand-600 hover:underline font-medium flex items-center gap-1">
                                        <i data-lucide="image" class="w-4 h-4"></i> View
                                    </a>
                                </td>
                                <td class="px-8 py-5">
                                    <a href="?approve=<?= $p['id'] ?>" class="bg-green-500 text-white px-5 py-2 rounded-lg font-bold text-xs hover:bg-green-600 shadow-lg shadow-green-500/30 transition">
                                        Approve
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if(empty($pending)) echo "<tr><td colspan='4' class='px-8 py-10 text-center text-slate-400'>All caught up! No pending deposits.</td></tr>"; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Stats / System Status -->
        <div class="space-y-6">
            <div class="bg-white p-8 rounded-[2rem] border border-slate-200 shadow-sm">
                <h3 class="font-bold text-slate-800 mb-6">System Health</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">Database</span>
                        <span class="text-green-600 text-xs font-bold bg-green-50 px-2 py-1 rounded">Connected</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">AI API (Gemini)</span>
                        <span class="text-brand-600 text-xs font-bold bg-brand-50 px-2 py-1 rounded">Professional</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 text-sm">Image Gen</span>
                        <span class="text-purple-600 text-xs font-bold bg-purple-50 px-2 py-1 rounded">Dual Stream</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>