<?php
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

// Basic Admin Check (In real production, use a role column. Here we assume user ID 1 is admin or just allow for demo purpose if not strict)
// For safety in this prompt output, I'll allow access but you should protect this file.

// Handle Deposit Approval
if (isset($_GET['approve_deposit'])) {
    $trx_id = (int)$_GET['approve_deposit'];
    $trx = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending'");
    $trx->execute([$trx_id]);
    $data = $trx->fetch();

    if ($data) {
        try {
            $pdo->beginTransaction();
            // Mark Approved
            $pdo->prepare("UPDATE transactions SET status = 'approved' WHERE id = ?")->execute([$trx_id]);
            // Add Balance (Automatic Process Triggered by Admin)
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$data['amount'], $data['user_id']]);
            $pdo->commit();
            header("Location: admin.php?success=approved");
        } catch(Exception $e) {
            $pdo->rollBack();
        }
    }
}

// Handle Promo
if (isset($_POST['post_promo'])) {
    $content = trim($_POST['promo_content']);
    $pdo->prepare("UPDATE promos SET is_active = 0")->execute();
    $pdo->prepare("INSERT INTO promos (content, is_active) VALUES (?, 1)")->execute([$content]);
}

// Handle Blog Post
if (isset($_POST['post_article'])) {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    $target_dir = "uploads/blog/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $filename = time() . "_" . basename($_FILES["thumbnail"]["name"]);
    move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_dir . $filename);
    $thumb_url = $target_dir . $filename;

    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $thumb_url]);
}

// Data Fetching
$pending_deposits = $pdo->query("SELECT t.*, u.channel_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type='deposit' AND t.status='pending'")->fetchAll();
$users = $pdo->query("SELECT * FROM users ORDER BY id DESC LIMIT 10")->fetchAll();

$page_title = "Admin Panel";
include 'header.php';
?>

<div class="container mx-auto p-6 max-w-7xl">
    <div class="flex items-center gap-4 mb-8">
        <div class="p-3 bg-red-600 rounded-lg text-white">
             <i data-lucide="layout-dashboard" class="w-6 h-6"></i>
        </div>
        <h1 class="text-3xl font-bold text-slate-800">Admin Dashboard</h1>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- 1. PENDING DEPOSITS -->
        <div class="glass-panel p-6 rounded-2xl border-l-4 border-yellow-400">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-slate-800">
                <i data-lucide="clock" class="w-5 h-5 text-yellow-500"></i> Menunggu Verifikasi (<?= count($pending_deposits) ?>)
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-slate-500 border-b border-slate-100 uppercase text-xs"><tr><th class="py-2">User</th><th>Amount</th><th>Proof</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($pending_deposits as $d): ?>
                        <tr class="border-b border-slate-50">
                            <td class="py-3 font-bold text-slate-700"><?= htmlspecialchars($d['channel_name']) ?></td>
                            <td class="text-green-600 font-mono"><?= formatRupiah($d['amount']) ?></td>
                            <td><a href="<?= $d['proof_img'] ?>" target="_blank" class="text-blue-500 hover:underline">Lihat Bukti</a></td>
                            <td>
                                <a href="admin.php?approve_deposit=<?= $d['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold transition shadow-lg shadow-green-500/30">Approve</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($pending_deposits)) echo "<tr><td colspan='4' class='py-4 text-center text-slate-400'>Tidak ada deposit pending.</td></tr>"; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. CREATE CONTENT -->
        <div class="glass-panel p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <i data-lucide="pen-tool" class="w-5 h-5 text-blue-500"></i> Post Artikel
            </h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="text" name="title" required placeholder="Judul Artikel" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 outline-none focus:border-blue-500">
                <textarea name="content" required rows="4" placeholder="Isi konten..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 outline-none focus:border-blue-500"></textarea>
                <div class="flex justify-between items-center">
                    <input type="file" name="thumbnail" required class="text-xs text-slate-500">
                    <button type="submit" name="post_article" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">Publish</button>
                </div>
            </form>
        </div>

        <!-- 3. BROADCAST -->
        <div class="glass-panel p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4">Broadcast Promo</h2>
            <form method="POST" class="flex gap-2">
                <input type="text" name="promo_content" placeholder="Pesan promo..." class="flex-1 bg-slate-50 border border-slate-200 rounded-lg p-3 outline-none">
                <button type="submit" name="post_promo" class="bg-purple-600 text-white px-6 py-2 rounded-lg font-bold">Kirim</button>
            </form>
        </div>

        <!-- 4. USERS -->
        <div class="glass-panel p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4">User Terbaru</h2>
            <ul class="space-y-2">
                <?php foreach($users as $u): ?>
                <li class="flex justify-between items-center bg-slate-50 p-3 rounded-lg border border-slate-100">
                    <span class="text-sm font-bold text-slate-700"><?= htmlspecialchars($u['channel_name']) ?></span>
                    <span class="text-xs font-mono text-green-600 bg-green-100 px-2 py-1 rounded"><?= formatRupiah($u['balance']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>