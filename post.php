<?php
require_once 'functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) header("Location: blog.php");

$page_title = htmlspecialchars($post['title']);
include 'header.php';
?>

<div class="bg-white min-h-screen pb-20">
    <div class="container mx-auto px-6 max-w-4xl py-20">
        <article>
            <header class="mb-10 text-center">
                <div class="inline-block px-4 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-full mb-6 uppercase tracking-wider">Blog Urat ID</div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-6 leading-tight"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="flex items-center justify-center gap-4 text-slate-500 text-sm font-medium">
                    <div class="flex items-center gap-2">
                         <div class="w-8 h-8 bg-slate-200 rounded-full flex items-center justify-center font-bold text-slate-600">A</div>
                         <span>Admin</span>
                    </div>
                    <span>•</span>
                    <span><?= date('d F Y', strtotime($post['created_at'])) ?></span>
                </div>
            </header>

            <div class="rounded-2xl overflow-hidden mb-12 shadow-2xl shadow-blue-900/10">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full max-h-[500px] object-cover">
            </div>

            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-bold prose-a:text-blue-600 prose-img:rounded-xl">
                <?= nl2br($post['content']) ?>
            </div>

            <div class="mt-16 pt-10 border-t border-slate-100 flex justify-center">
                <a href="blog.php" class="px-8 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl transition flex items-center gap-2">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i> Kembali ke Blog
                </a>
            </div>
        </article>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>