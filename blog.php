<?php
require_once 'functions.php';
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
$page_title = "Blog & Artikel";
include 'header.php';
?>

<div class="container mx-auto px-6 py-16">
    <div class="text-center mb-16">
        <h1 class="text-4xl font-extrabold text-slate-900 mb-4">Jurnal <span class="text-blue-600">Digital</span></h1>
        <p class="text-slate-500 max-w-2xl mx-auto">Temukan strategi terbaru algoritma YouTube.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
        <?php foreach($posts as $post): ?>
        <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition duration-300 group">
            <div class="aspect-video bg-slate-200 overflow-hidden">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
            </div>
            <div class="p-8">
                <div class="flex items-center gap-2 mb-4">
                    <span class="px-3 py-1 bg-blue-50 text-blue-600 text-xs font-bold rounded-full uppercase tracking-wider">SEO</span>
                    <span class="text-slate-400 text-xs"><?= date('d M Y', strtotime($post['created_at'])) ?></span>
                </div>
                <h2 class="text-xl font-bold mb-3 text-slate-800 group-hover:text-blue-600 transition">
                    <a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <p class="text-slate-500 text-sm line-clamp-3 leading-relaxed mb-6">
                    <?= strip_tags($post['content']) ?>
                </p>
                <a href="post.php?slug=<?= $post['slug'] ?>" class="inline-flex items-center text-sm font-bold text-slate-800 hover:text-blue-600 transition">
                    Baca Selengkapnya <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>