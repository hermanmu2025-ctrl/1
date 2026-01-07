<?php
require_once 'functions.php';
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
$page_title = "Blog & Tips SEO";
$meta_desc = "Kumpulan artikel terbaik tentang cara menambah subscriber Youtube, SEO Video, dan Algoritma terbaru dari Urat ID.";
include 'header.php';
?>

<div class="container mx-auto px-6 py-20">
    <div class="text-center mb-20">
        <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 mb-6 tracking-tight">Jurnal <span class="text-blue-600">Creator</span></h1>
        <p class="text-slate-500 max-w-2xl mx-auto text-xl">Strategi rahasia menaklukkan algoritma YouTube dan optimasi digital.</p>
    </div>

    <div class="grid md:grid-cols-3 gap-10">
        <?php foreach($posts as $post): ?>
        <div class="bg-white rounded-3xl overflow-hidden border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition duration-500 group flex flex-col h-full">
            <div class="aspect-video bg-slate-200 overflow-hidden relative">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-4 py-1.5 rounded-full text-xs font-bold text-blue-600 uppercase tracking-wider">
                    Article
                </div>
            </div>
            <div class="p-8 flex-1 flex flex-col">
                <h2 class="text-2xl font-bold mb-4 text-slate-800 group-hover:text-blue-600 transition leading-snug">
                    <a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <div class="text-slate-500 text-sm line-clamp-3 leading-relaxed mb-8 flex-1">
                    <?= isset($post['meta_desc']) && $post['meta_desc'] ? $post['meta_desc'] : strip_tags(substr($post['content'], 0, 150)).'...' ?>
                </div>
                <div class="pt-6 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-xs text-slate-400 font-bold uppercase"><?= date('d M Y', strtotime($post['created_at'])) ?></span>
                    <a href="post.php?slug=<?= $post['slug'] ?>" class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
                        Baca Lengkap <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>