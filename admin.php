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
        $msg = "Deposit Approved.";
    }
}

// Action: Trigger AI
if (isset($_POST['trigger_ai'])) {
    $topics = AI_TARGET_KEYWORDS;
    $topic = $topics[array_rand($topics)];
    $res = generateAIArticle($topic);
    
    if (!isset($res['error'])) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $res['title'])));
        $thumb = "https://source.unsplash.com/1200x800/?" . urlencode($res['image_keywords']);
        
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$res['title'], $slug, $res['content'], $thumb, $res['meta_desc']]);
        $msg = "AI Article Generated: " . $res['title'];
    } else {
        $msg = "AI Error: " . $res['error'];
    }
}

$pending = $pdo->query("SELECT t.*, u.channel_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type='deposit' AND t.status='pending'")->fetchAll();
$page_title = "Admin Panel";
include 'header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="flex justify-between items-center mb-10 bg-slate-900 text-white p-8 rounded-3xl">
        <div>
            <h1 class="text-2xl font-bold">Admin Control Center</h1>
            <p class="text-slate-400">Manage System & Content</p>
        </div>
        <div class="flex gap-3">
            <form method="POST">
                <button name="trigger_ai" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 rounded-lg font-bold flex items-center gap-2">
                    <i data-lucide="bot"></i> Generate AI Post
                </button>
            </form>
            <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg font-bold">Logout</a>
        </div>
    </div>

    <?php if($msg): ?>
        <div class="bg-blue-100 text-blue-800 p-4 rounded-xl font-bold mb-8"><?= $msg ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-800">Deposit Requests</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4">User</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Proof</th>
                        <th class="px-6 py-4">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($pending as $p): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium"><?= htmlspecialchars($p['channel_name']) ?></td>
                        <td class="px-6 py-4 text-green-600 font-bold"><?= formatRupiah($p['amount']) ?></td>
                        <td class="px-6 py-4">
                            <a href="<?= $p['proof_img'] ?>" target="_blank" class="text-blue-600 underline">View Image</a>
                        </td>
                        <td class="px-6 py-4">
                            <a href="?approve=<?= $p['id'] ?>" class="bg-green-500 text-white px-4 py-1.5 rounded-lg font-bold text-xs hover:bg-green-600">Approve</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($pending)) echo "<tr><td colspan='4' class='px-6 py-8 text-center text-slate-400'>No pending requests.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>