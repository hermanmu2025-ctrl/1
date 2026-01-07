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
        // Validate Constants
        if (!defined('AI_TARGET_KEYWORDS') || !is_array(AI_TARGET_KEYWORDS) || empty(AI_TARGET_KEYWORDS)) {
            throw new Exception("Konfigurasi AI_TARGET_KEYWORDS di config.php tidak valid atau kosong.");
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
            $used_model = $aiResult['used_model'] ?? 'Unknown Model';
            
            // Sanitize Title Length (DB Limit)
            if (strlen($title) > 250) {
                $title = substr($title, 0, 247) . '...';
            }
            
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            
            // Validasi Duplicate Slug
            $checkSlug = $pdo->prepare("SELECT id FROM posts WHERE slug = ?");
            $checkSlug->execute([$slug]);
            if ($checkSlug->rowCount() > 0) $slug .= '-' . time();

            $image_keywords = urlencode($aiResult['image_keywords'] ?? 'technology');
            // Used picsum instead of unsplash source (deprecated)
            $thumb_url = "https://picsum.photos/1200/800?random=" . time();
            
            $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $thumb_url, $meta_desc]);
            $success_msg = "Artikel Otomatis berhasil dibuat menggunakan model <strong>$used_model</strong> dengan topik: $selected_topic";
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
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $thumb_url]);
        $success_msg = "Artikel manual dipublish.";
    } else {
        $error_msg = "Gagal upload gambar.";
    }
}

// Data Fetching
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
             <a href="index.php" class="bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded-lg font-bold text-sm transition">Visit Site</a>
             <div class="bg-red-600 text-white px-5 py-2 rounded-lg font-mono font-bold text-sm flex items-center">
                <i data-lucide="shield" class="w-4 h-4 inline mr-2"></i> Secure Mode
            </div>
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
        
        <!-- AI GENERATOR (UPDATED LOGIC) -->
        <div class="glass-panel p-8 rounded-3xl border border-purple-200 bg-gradient-to-br from-white to-purple-50/50">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-purple-600/30">
                     <i data-lucide="bot" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-purple-900">Sovereign AI Auto-Pilot</h2>
                    <p class="text-xs text-purple-600 font-bold">System v3.0 (Smart Model Selection)</p>
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

            <p class="text-sm text-slate-600 mb-6 leading-relaxed">
                Sistem akan secara otomatis memilih model terbaik (priority: 1.5-flash) dan topik dari daftar wajib:
                <span class="font-mono text-xs bg-slate-100 p-1 rounded">Youtuber Pemula, Jasa SEO, dll.</span><br>
            </p>

            <form method="POST" class="space-y-4">
                 <div class="bg-yellow-50 border border-yellow-100 p-4 rounded-xl text-xs text-yellow-700 mb-4">
                     <strong>Info:</strong> Fitur ini berjalan otomatis setiap hari via Cron Job. Tombol di bawah ini untuk memaksa AI membuat artikel SEKARANG.
                 </div>
                <button type="submit" name="trigger_auto_ai" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-4 rounded-xl transition flex justify-center items-center gap-2 shadow-lg shadow-purple-600/20">
                    <i data-lucide="zap" class="w-4 h-4"></i> Jalankan AI Sekarang (Random Topic)
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
                <textarea name="content" rows="3" required placeholder="Isi Content (Support HTML)..." class="w-full p-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none">
<p>Paragraf pembuka...</p>
<h2>Sub Judul</h2>
<p>Isi konten...</p>
</textarea>
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
                    <?php if(empty($pending_deposits)) echo "<tr><td colspan='4' class='text-center py-8 text-slate-400 font-medium'>Tidak ada deposit pending saat ini.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MANAGE POSTS -->
    <div class="mt-10 bg-white p-8 rounded-3xl shadow-lg shadow-slate-200/50 border border-slate-100">
        <h2 class="text-xl font-bold mb-6">Manage Blog Posts</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr><th class="text-left pb-4 text-slate-400">Title</th><th class="text-right pb-4 text-slate-400">Action</th></tr></thead>
                <tbody>
                    <?php foreach($posts as $post): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                        <td class="py-4 font-medium text-slate-700">
                            <?= htmlspecialchars($post['title']) ?>
                            <div class="text-[10px] text-slate-400 font-mono mt-1">Slug: <?= $post['slug'] ?></div>
                        </td>
                        <td class="text-right flex justify-end gap-2 py-4">
                            <!-- Edit Button -->
                            <button onclick="document.getElementById('edit-<?= $post['id'] ?>').style.display='flex'" class="bg-yellow-50 text-yellow-600 px-3 py-1.5 rounded-lg text-xs font-bold border border-yellow-200 hover:bg-yellow-100">Edit</button>
                            
                            <!-- Delete Form -->
                            <form method="POST" onsubmit="return confirm('Hapus postingan ini?');">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" name="delete_post" class="bg-red-50 text-red-600 px-3 py-1.5 rounded-lg text-xs font-bold border border-red-200 hover:bg-red-100">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Simple Edit Modal -->
                    <div id="edit-<?= $post['id'] ?>" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden items-center justify-center z-50">
                        <div class="bg-white p-8 rounded-2xl w-full max-w-2xl shadow-2xl">
                            <h3 class="font-bold text-lg mb-6">Edit Post</h3>
                            <form method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" class="w-full border border-slate-200 p-3 mb-4 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none">
                                <textarea name="content" rows="10" class="w-full border border-slate-200 p-3 mb-6 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none font-mono text-sm"><?= htmlspecialchars($post['content']) ?></textarea>
                                <div class="flex justify-end gap-3">
                                    <button type="button" onclick="document.getElementById('edit-<?= $post['id'] ?>').style.display='none'" class="bg-slate-100 text-slate-600 px-6 py-2.5 rounded-xl font-bold hover:bg-slate-200 transition">Batal</button>
                                    <button type="submit" name="update_post" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-blue-700 transition">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>