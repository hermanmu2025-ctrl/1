<?php
require_once 'functions.php';
$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
$page_title = "Blog & Edukasi";
include 'header.php';
?>

<div class="container mx-auto px-6 py-20">
    <div class="text-center max-w-3xl mx-auto mb-20">
        <span class="text-blue-600 font-bold tracking-widest uppercase text-xs">Creator Academy</span>
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mt-4 mb-6 leading-tight">
            Strategi Menaklukkan <br> Algoritma YouTube
        </h1>
        <p class="text-xl text-slate-500 leading-relaxed">
            Kumpulan artikel mendalam, tips SEO, dan panduan teknis yang ditulis oleh AI Expert dan tim editorial kami.
        </p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
        <?php foreach($posts as $post): ?>
        <article class="bg-white rounded-[2rem] overflow-hidden border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-2 transition duration-500 group flex flex-col h-full">
            <div class="relative aspect-video overflow-hidden">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-60"></div>
                <span class="absolute bottom-4 left-4 text-white text-xs font-bold px-3 py-1 bg-blue-600 rounded-full">
                    Tips & Trik
                </span>
            </div>
            <div class="p-8 flex-1 flex flex-col">
                <h2 class="text-xl font-bold text-slate-900 mb-4 leading-snug group-hover:text-blue-600 transition">
                    <a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                </h2>
                <p class="text-slate-500 text-sm line-clamp-3 mb-6 flex-1 leading-relaxed">
                    <?= isset($post['meta_desc']) ? $post['meta_desc'] : strip_tags($post['content']) ?>
                </p>
                <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                    <span class="text-xs text-slate-400 font-bold uppercase"><?= date('d M Y', strtotime($post['created_at'])) ?></span>
                    <a href="post.php?slug=<?= $post['slug'] ?>" class="text-blue-600 font-bold text-sm flex items-center gap-1 hover:gap-2 transition-all">
                        Read Article <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>