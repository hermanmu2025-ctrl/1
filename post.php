<?php
require_once 'functions.php';

// Get Post Data
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) header("Location: blog.php");

// Handle Comment Submission
$comment_msg = '';
$comment_status = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $comment_msg = "Invalid Security Token. Refresh page.";
        $comment_status = "error";
    } else {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $comment = htmlspecialchars(trim($_POST['comment']));

        if (empty($name) || empty($email) || empty($comment)) {
            $comment_msg = "Semua kolom wajib diisi.";
            $comment_status = "error";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $comment_msg = "Format email tidak valid.";
            $comment_status = "error";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO comments (post_id, name, email, comment) VALUES (?, ?, ?, ?)");
                $stmt->execute([$post['id'], $name, $email, $comment]);
                $comment_msg = "Komentar berhasil dikirim!";
                $comment_status = "success";
                // Clear POST to prevent resubmission
                echo "<script>if(history.replaceState) history.replaceState(null, null, location.href);</script>";
            } catch (Exception $e) {
                $comment_msg = "Terjadi kesalahan sistem.";
                $comment_status = "error";
            }
        }
    }
}

// Get Comments
$stmtComments = $pdo->prepare("SELECT * FROM comments WHERE post_id = ? ORDER BY created_at DESC");
$stmtComments->execute([$post['id']]);
$comments = $stmtComments->fetchAll();

$page_title = htmlspecialchars($post['title']);
$meta_desc = $post['meta_desc'];
include 'header.php';
?>

<div class="bg-white">
    <div class="container mx-auto px-6 py-20 max-w-4xl">
        <article>
            <header class="text-center mb-16">
                <span class="px-4 py-2 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider mb-6 inline-block">Marketing Insight</span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 leading-tight mb-8"><?= htmlspecialchars($post['title']) ?></h1>
                
                <div class="flex items-center justify-center gap-4 text-slate-500 text-sm font-medium">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600">AI</div>
                        <span>Smart Editor</span>
                    </div>
                    <span>&bull;</span>
                    <span><?= date('F d, Y', strtotime($post['created_at'])) ?></span>
                </div>
            </header>

            <div class="rounded-3xl overflow-hidden shadow-2xl mb-16">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full object-cover">
            </div>

            <!-- Content Area with Enhanced Typography -->
            <div class="prose prose-lg prose-slate mx-auto prose-headings:font-extrabold prose-headings:text-slate-900 prose-a:text-blue-600 prose-img:rounded-2xl prose-p:leading-loose prose-p:text-slate-600 prose-li:text-slate-600 prose-strong:text-slate-800">
                <?= $post['content'] ?>
            </div>
        </article>
        
        <div class="mt-20 mb-12 border-t border-slate-100"></div>

        <!-- COMMENT SECTION -->
        <section id="comments" class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-8">
                <div class="p-3 bg-blue-50 rounded-xl text-blue-600">
                    <i data-lucide="message-square" class="w-6 h-6"></i>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">Diskusi (<?= count($comments) ?>)</h3>
            </div>

            <!-- Comment Form -->
            <div class="bg-slate-50 p-8 rounded-3xl border border-slate-200 shadow-sm mb-12 relative overflow-hidden">
                 <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500 rounded-full blur-[80px] opacity-10"></div>
                 
                 <h4 class="text-lg font-bold text-slate-800 mb-6">Tinggalkan Komentar</h4>
                 
                 <?php if($comment_msg): ?>
                    <div class="p-4 rounded-xl mb-6 text-sm font-bold flex items-center gap-2 <?= $comment_status == 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                        <i data-lucide="<?= $comment_status == 'success' ? 'check-circle' : 'alert-circle' ?>" class="w-4 h-4"></i> <?= $comment_msg ?>
                    </div>
                 <?php endif; ?>

                 <form method="POST" class="space-y-5 relative z-10">
                     <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                     <div class="grid md:grid-cols-2 gap-5">
                         <div class="space-y-2">
                             <label class="text-xs font-bold text-slate-500 uppercase ml-1">Nama Lengkap</label>
                             <input type="text" name="name" required placeholder="Jhon Doe" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition font-medium text-slate-800">
                         </div>
                         <div class="space-y-2">
                             <label class="text-xs font-bold text-slate-500 uppercase ml-1">Alamat Email</label>
                             <input type="email" name="email" required placeholder="email@domain.com" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition font-medium text-slate-800">
                         </div>
                     </div>
                     <div class="space-y-2">
                         <label class="text-xs font-bold text-slate-500 uppercase ml-1">Komentar Anda</label>
                         <textarea name="comment" required rows="4" placeholder="Tulis pendapat Anda di sini..." class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition font-medium text-slate-800"></textarea>
                     </div>
                     <button type="submit" name="submit_comment" class="btn-primary text-white font-bold px-8 py-3 rounded-xl shadow-lg hover:shadow-blue-500/30 transition flex items-center gap-2">
                         <i data-lucide="send" class="w-4 h-4"></i> Kirim Komentar
                     </button>
                 </form>
            </div>

            <!-- Comment List -->
            <div class="space-y-8">
                <?php foreach($comments as $c): ?>
                <div class="flex gap-4 group">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 border border-slate-300 flex items-center justify-center font-bold text-slate-500 text-lg">
                            <?= strtoupper(substr($c['name'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="flex-grow">
                        <div class="bg-white p-6 rounded-2xl rounded-tl-none border border-slate-100 shadow-sm group-hover:shadow-md transition">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h5 class="font-bold text-slate-900"><?= htmlspecialchars($c['name']) ?></h5>
                                    <span class="text-xs text-slate-400 font-medium"><?= date('d F Y, H:i', strtotime($c['created_at'])) ?></span>
                                </div>
                            </div>
                            <p class="text-slate-600 leading-relaxed"><?= nl2br(htmlspecialchars($c['comment'])) ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if(empty($comments)): ?>
                    <div class="text-center py-12 opacity-50">
                        <i data-lucide="message-circle" class="w-12 h-12 mx-auto mb-3 text-slate-300"></i>
                        <p class="text-slate-500">Belum ada komentar. Jadilah yang pertama!</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <div class="mt-20 pt-10 border-t border-slate-100 text-center">
            <a href="blog.php" class="inline-flex items-center gap-2 font-bold text-slate-600 hover:text-blue-600 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Blog
            </a>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>