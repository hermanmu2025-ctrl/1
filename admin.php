<?php
require_once 'functions.php';

// Security Check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit;
}

$success_msg = '';
$error_msg = '';

// --- ACTION HANDLERS ---

// 1. Approve Deposit
if (isset($_GET['approve_deposit'])) {
    $trx_id = (int)$_GET['approve_deposit'];
    $trx = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending'");
    $trx->execute([$trx_id]);
    $data = $trx->fetch();

    if ($data) {
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE transactions SET status = 'approved' WHERE id = ?")->execute([$trx_id]);
            $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$data['amount'], $data['user_id']]);
            $pdo->commit();
            $success_msg = "Deposit disetujui.";
        } catch(Exception $e) {
            $pdo->rollBack();
            $error_msg = "Error database: " . $e->getMessage();
        }
    }
}

// 2. Delete Post
if (isset($_POST['delete_post'])) {
    $id = (int)$_POST['post_id'];
    $pdo->prepare("DELETE FROM posts WHERE id = ?")->execute([$id]);
    $success_msg = "Postingan berhasil dihapus.";
}

// 3. Edit Post
if (isset($_POST['update_post'])) {
    $id = (int)$_POST['post_id'];
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $pdo->prepare("UPDATE posts SET title=?, content=? WHERE id=?")->execute([$title, $content, $id]);
    $success_msg = "Postingan berhasil diupdate.";
}

// 4. TRIGGER AUTO POST (MANUAL OVERRIDE)
if (isset($_POST['trigger_auto_ai'])) {
    try {
        if (!defined('AI_TARGET_KEYWORDS') || !is_array(AI_TARGET_KEYWORDS)) {
            throw new Exception("Konfigurasi AI_TARGET_KEYWORDS tidak valid.");
        }

        $allowed_topics = AI_TARGET_KEYWORDS;
        $random_index = array_rand($allowed_topics);
        $selected_topic = $allowed_topics[$random_index];

        $aiResult = generateAIArticle($selected_topic);
        
        if (isset($aiResult['error'])) {
            $error_msg = "AI Error: " . $aiResult['error'];
        } else {
            $title = $aiResult['title'] ?? 'Tanpa Judul';
            $content = $aiResult['content'] ?? '<p>Konten Kosong</p>';
            $meta_desc = $aiResult['meta_desc'] ?? '';
            $used_model = $aiResult['used_model'] ?? 'Unknown';
            
            // Normalize
            if (strlen($title) > 250) $title = substr($title, 0, 247) . '...';
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            $checkSlug = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $checkSlug->execute([$slug]);
            if ($checkSlug->rowCount() > 0) $slug .= '-' . time();

            $thumb_url = "https://picsum.photos/1200/800?random=" . time();
            
            try {
                $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $thumb_url, $meta_desc]);
                $success_msg = "Artikel Otomatis berhasil dibuat (Model: $used_model).";
            } catch (PDOException $e) {
                // Specific Handling for the User's Error
                if ($e->getCode() == '42S22') {
                    throw new Exception("CRITICAL ERROR: Kolom 'meta_desc' tidak ditemukan di database. <br><a href='install_db.php' class='underline font-bold'>KLIK DI SINI UNTUK PERBAIKI DATABASE</a>");
                }
                throw $e;
            }
        }
    } catch (Exception $e) {
        $error_msg = "System Error: " . $e->getMessage();
    }
}

// 5. Manual Post
if (isset($_POST['post_article'])) {
    $title = trim($_POST['title']);
    $content = $_POST['content'];
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    
    $target_dir = "uploads/blog/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $filename = time() . "_" . basename($_FILES["thumbnail"]["name"]);
    
    if (move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_dir . $filename)) {
        $thumb_url = $target_dir . $filename;
        // Ensure manual post also supports meta_desc if needed, or default null
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $thumb_url, substr(strip_tags($content), 0, 150)]);
        $success_msg = "Artikel manual dipublish.";
    } else {
        $error_msg = "Gagal upload gambar.";
    }
}

$pending_deposits = $pdo->query("SELECT t.*, u.channel_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type='deposit' AND t.status='pending'")->fetchAll();
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
$available_models = getAvailableGeminiModels();

$page_title = "Admin Dashboard";
include 'header.php';
?>

<div class="container mx-auto p-6 max-w-7xl">
    <div class="flex justify-between items-center mb-10 bg-slate-900 text-white p-6 rounded-3xl">
        <div>
            <h1 class="text-3xl font-bold">Admin Dashboard</h1>
            <p class="text-slate-400 text-sm">System Control Panel v2.5</p>
        </div>
        <div class="flex gap-3">
             <a href="install_db.php" class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded-lg font-bold text-sm transition flex items-center gap-2"><i data-lucide="database"></i> Fix DB Structure</a>
             <a href="index.php" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded-lg font-bold text-sm transition">Visit Site</a>
        </div>
    </div>

    <?php if($success_msg): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-bold flex items-center gap-2 shadow-sm border border-green-200"><i data-lucide="check-circle"></i> <?= $success_msg ?></div>
    <?php endif; ?>
    <?php if($error_msg): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-bold flex items-center gap-2 shadow-sm border border-red-200"><i data-lucide="alert-triangle"></i> <?= $error_msg ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- AI GENERATOR -->
        <div class="glass-panel p-8 rounded-3xl border border-purple-200 bg-gradient-to-br from-white to-purple-50/50">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-600/30">
                     <i data-lucide="bot" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-purple-900">Sovereign AI Auto-Pilot</h2>
                    <p class="text-xs text-purple-600 font-bold">System v3.2 (Smart Schema)</p>
                </div>
            </div>
            
            <div class="bg-white/80 p-4 rounded-xl mb-6 border border-purple-100 shadow-sm">
                <p class="text-xs text-slate-500 font-bold uppercase mb-2">Detected AI Models:</p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach($available_models as $model): ?>
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 text-[10px] font-mono rounded font-bold border border-purple-200"><?= $model ?></span>
                    <?php endforeach; ?>
                    <?php if(empty($available_models)) echo '<span class="text-red-500 text-xs">No models found. Check API Key.</span>'; ?>
                </div>
            </div>

            <form method="POST" class="space-y-4">
                 <div class="bg-yellow-50 border border-yellow-100 p-4 rounded-xl text-xs text-yellow-700 mb-4">
                     <strong>Status:</strong> Siap untuk deployment konten otomatis.
                 </div>
                <button type="submit" name="trigger_auto_ai" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-4 rounded-xl transition flex justify-center items-center gap-2 shadow-lg shadow-purple-600/20">
                    <i data-lucide="zap" class="w-4 h-4"></i> Jalankan AI Sekarang
                </button>
            </form>
        </div>

        <!-- MANUAL POST -->
        <div class="glass-panel p-8 rounded-3xl border border-slate-200">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-slate-800">
                <i data-lucide="pen-tool" class="w-5 h-5"></i> Manual Post
            </h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-3">
                <input type="text" name="title" required placeholder="Judul Artikel" class="w-full p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
                <textarea name="content" rows="3" required placeholder="Isi Content (Support HTML)..." class="w-full p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                <input type="file" name="thumbnail" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition">
                <button type="submit" name="post_article" class="bg-slate-800 hover:bg-slate-900 text-white px-6 py-3 rounded-xl font-bold transition w-full">Publish Manual</button>
            </form>
        </div>

    </div>

    <!-- DEPOSIT APPROVAL -->
    <div class="mt-10 bg-white p-8 rounded-3xl shadow-lg shadow-slate-200/50 border border-slate-100">
        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">Deposit Pending <span class="text-xs bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full font-extrabold"><?= count($pending_deposits) ?> Request</span></h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-400 uppercase bg-slate-50 rounded-lg">
                    <tr>
                        <th class="px-6 py-4 rounded-l-lg">User</th>
                        <th class="px-6 py-4">Jumlah</th>
                        <th class="px-6 py-4">Bukti</th>
                        <th class="px-6 py-4 rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_deposits as $d): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-700"><?= htmlspecialchars($d['channel_name']) ?></td>
                        <td class="px-6 py-4 font-mono text-green-600 font-bold"><?= formatRupiah($d['amount']) ?></td>
                        <td class="px-6 py-4"><a href="<?= $d['proof_img'] ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1"><i data-lucide="image" class="w-3 h-3"></i> Lihat</a></td>
                        <td class="px-6 py-4"><a href="admin.php?approve_deposit=<?= $d['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-bold text-xs shadow-md transition">Approve</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>