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
        
        // Simulate AI Image Generation using Keyword-based Placeholder
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
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-slate-800">Admin Dashboard</h1>
        <div class="bg-red-100 text-red-600 px-4 py-2 rounded-lg font-mono font-bold">Pass: Amnet123</div>
    </div>

    <?php if($success_msg): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-xl mb-6 font-bold"><?= $success_msg ?></div>
    <?php endif; ?>
    <?php if($error_msg): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-xl mb-6 font-bold"><?= $error_msg ?></div>
    <?php endif; ?>

    <!-- TABS -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        <!-- AI GENERATOR -->
        <div class="glass-panel p-6 rounded-2xl border border-purple-200 bg-gradient-to-br from-white to-purple-50">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2 text-purple-700">
                <i data-lucide="bot" class="w-6 h-6"></i> AI Blog Generator (Gemini)
            </h2>
            <p class="text-sm text-slate-500 mb-4">Otomatis membuat artikel 3000-5000 karakter, judul marketing, dan gambar.</p>
            <form method="POST" class="space-y-4">
                <input type="text" name="topic" required placeholder="Contoh: Cara Menambah 1000 Subscriber Youtube Cepat" class="w-full p-4 rounded-xl border border-purple-200 focus:ring-2 focus:ring-purple-500 outline-none">
                <button type="submit" name="generate_ai" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 rounded-xl transition flex justify-center items-center gap-2">
                    <i data-lucide="sparkles" class="w-4 h-4"></i> Generate Article Now
                </button>
            </form>
        </div>

        <!-- MANUAL POST -->
        <div class="glass-panel p-6 rounded-2xl">
            <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                <i data-lucide="pen-tool" class="w-5 h-5"></i> Manual Post
            </h2>
            <form method="POST" enctype="multipart/form-data" class="space-y-3">
                <input type="text" name="title" required placeholder="Judul" class="w-full p-3 rounded-lg border border-slate-200">
                <textarea name="content" rows="3" required placeholder="Isi Content HTML..." class="w-full p-3 rounded-lg border border-slate-200"></textarea>
                <input type="file" name="thumbnail" required class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <button type="submit" name="post_article" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-bold">Publish Manual</button>
            </form>
        </div>

    </div>

    <!-- DEPOSIT APPROVAL -->
    <div class="mt-8 glass-panel p-6 rounded-2xl border-l-4 border-yellow-400">
        <h2 class="text-xl font-bold mb-4">Deposit Pending</h2>
        <table class="w-full text-sm">
            <thead><tr><th class="text-left">User</th><th class="text-left">Jumlah</th><th class="text-left">Bukti</th><th class="text-left">Aksi</th></tr></thead>
            <tbody>
                <?php foreach($pending_deposits as $d): ?>
                <tr class="border-b">
                    <td class="py-3"><?= htmlspecialchars($d['channel_name']) ?></td>
                    <td class="font-mono text-green-600"><?= formatRupiah($d['amount']) ?></td>
                    <td><a href="<?= $d['proof_img'] ?>" target="_blank" class="text-blue-500 underline">Lihat</a></td>
                    <td><a href="admin.php?approve_deposit=<?= $d['id'] ?>" class="bg-green-500 text-white px-3 py-1 rounded">Approve</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- MANAGE POSTS -->
    <div class="mt-8 glass-panel p-6 rounded-2xl">
        <h2 class="text-xl font-bold mb-4">Manage Blog Posts</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr><th class="text-left">Title</th><th class="text-right">Action</th></tr></thead>
                <tbody>
                    <?php foreach($posts as $post): ?>
                    <tr class="border-b hover:bg-slate-50">
                        <td class="py-3 font-medium"><?= htmlspecialchars($post['title']) ?></td>
                        <td class="text-right flex justify-end gap-2 py-3">
                            <!-- Edit Button (Trigger Modal - simplified for PHP) -->
                            <button onclick="document.getElementById('edit-<?= $post['id'] ?>').style.display='flex'" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs">Edit</button>
                            
                            <!-- Delete Form -->
                            <form method="POST" onsubmit="return confirm('Hapus postingan ini?');">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <button type="submit" name="delete_post" class="bg-red-500 text-white px-3 py-1 rounded text-xs">Hapus</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Simple Edit Modal -->
                    <div id="edit-<?= $post['id'] ?>" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
                        <div class="bg-white p-6 rounded-xl w-full max-w-2xl">
                            <h3 class="font-bold text-lg mb-4">Edit Post</h3>
                            <form method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                <input type="text" name="title" value="<?= htmlspecialchars($post['title']) ?>" class="w-full border p-2 mb-2 rounded">
                                <textarea name="content" rows="10" class="w-full border p-2 mb-4 rounded"><?= htmlspecialchars($post['content']) ?></textarea>
                                <div class="flex justify-end gap-2">
                                    <button type="button" onclick="document.getElementById('edit-<?= $post['id'] ?>').style.display='none'" class="bg-slate-200 px-4 py-2 rounded">Batal</button>
                                    <button type="submit" name="update_post" class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
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