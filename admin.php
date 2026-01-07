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
            $error_msg = "Error database.";
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

// 4. AI Generate Post
if (isset($_POST['generate_ai'])) {
    $topic = trim($_POST['topic']);
    $aiResult = generateAIArticle($topic);
    
    if (isset($aiResult['error'])) {
        $error_msg = "AI Error: " . $aiResult['error'];
    } else {
        $title = $aiResult['title'];
        $content = $aiResult['content'];
        $meta_desc = $aiResult['meta_desc'] ?? '';
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Simulate AI Image Generation using Keyword-based Placeholder (Unsplash Source)
        $image_keywords = urlencode($aiResult['image_keywords'] ?? 'technology');
        $thumb_url = "https://source.unsplash.com/800x600/?" . $image_keywords;
        
        // Save to DB
        $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail, meta_desc) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $content, $thumb_url, $meta_desc]);
        $success_msg = "Artikel AI berhasil dibuat: $title";
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
    move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $target_dir . $filename);
    $thumb_url = $target_dir . $filename;

    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, content, thumbnail) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $slug, $content, $thumb_url]);
    $success_msg = "Artikel manual dipublish.";
}

// Data Fetching
$pending_deposits = $pdo->query("SELECT t.*, u.channel_name FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.type='deposit' AND t.status='pending'")->fetchAll();
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();

$page_title = "Admin Dashboard";
include 'header.php';
?>

<div class="container mx-auto p-6 max-w-7xl">
    <div class="flex justify-between items-center mb-10">
        <h1 class="text-3xl font-bold text-slate-800">Admin Dashboard</h1>
        <div class="bg-red-50 border border-red-100 text-red-600 px-5 py-2 rounded-lg font-mono font-bold text-sm">
            <i data-lucide="shield" class="w-4 h-4 inline mr-2"></i> Security Active
        </div>
    </div>

    <?php if($success_msg): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-bold flex items-center gap-2"><i data-lucide="check-circle"></i> <?= $success_msg ?></div>
    <?php endif; ?>
    <?php if($error_msg): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-bold flex items-center gap-2"><i data-lucide="alert-triangle"></i> <?= $error_msg ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- AI GENERATOR -->
        <div class="glass-panel p-8 rounded-3xl border border-purple-200 bg-gradient-to-br from-white to-purple-50/50">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-purple-700">
                <i data-lucide="bot" class="w-6 h-6"></i> AI Blog Generator (Gemini)
            </h2>
            <p class="text-sm text-slate-500 mb-6">Otomatis membuat artikel SEO 3000+ karakter, judul marketing, dan gambar.</p>
            <form method="POST" class="space-y-4">
                <input type="text" name="topic" required placeholder="Contoh: Cara Menambah 1000 Subscriber Youtube Cepat" class="w-full p-4 rounded-xl border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none shadow-sm">
                <button type="submit" name="generate_ai" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-4 rounded-xl transition flex justify-center items-center gap-2 shadow-lg shadow-purple-600/20">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Generate Article Now
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
    <div class="mt-10 glass-panel p-8 rounded-3xl border-l-8 border-yellow-400">
        <h2 class="text-xl font-bold mb-6 flex items-center gap-2">Deposit Pending <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full"><?= count($pending_deposits) ?> Request</span></h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-slate-400 uppercase bg-slate-50 rounded-lg">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">User</th>
                        <th class="px-4 py-3">Jumlah</th>
                        <th class="px-4 py-3">Bukti</th>
                        <th class="px-4 py-3 rounded-r-lg">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pending_deposits as $d): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                        <td class="px-4 py-4 font-bold text-slate-700"><?= htmlspecialchars($d['channel_name']) ?></td>
                        <td class="px-4 py-4 font-mono text-green-600 font-bold"><?= formatRupiah($d['amount']) ?></td>
                        <td class="px-4 py-4"><a href="<?= $d['proof_img'] ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1"><i data-lucide="image" class="w-3 h-3"></i> Lihat</a></td>
                        <td class="px-4 py-4"><a href="admin.php?approve_deposit=<?= $d['id'] ?>" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-bold text-xs shadow-md transition">Approve</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($pending_deposits)) echo "<tr><td colspan='4' class='text-center py-4 text-slate-400'>Tidak ada deposit pending.</td></tr>"; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MANAGE POSTS -->
    <div class="mt-10 glass-panel p-8 rounded-3xl border border-slate-200">
        <h2 class="text-xl font-bold mb-6">Manage Blog Posts</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr><th class="text-left pb-4 text-slate-400">Title</th><th class="text-right pb-4 text-slate-400">Action</th></tr></thead>
                <tbody>
                    <?php foreach($posts as $post): ?>
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                        <td class="py-4 font-medium text-slate-700"><?= htmlspecialchars($post['title']) ?></td>
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