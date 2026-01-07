<?php
require_once 'functions.php';

$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC")->fetchAll();
$featured = !empty($posts) ? $posts[0] : null;
$remaining_posts = array_slice($posts, 1);

$page_title = "Blog & Insight";
include 'header.php';
?>

<div class="bg-white min-h-screen">
    
    <!-- Blog Header -->
    <div class="bg-slate-900 text-white py-20 relative overflow-hidden">
         <div class="absolute top-0 right-0 w-1/2 h-full bg-brand-900 opacity-20 transform skew-x-12"></div>
         <div class="container mx-auto px-6 relative z-10 text-center">
             <span class="text-brand-400 font-bold tracking-widest uppercase text-xs mb-4 block">URAT ID ACADEMY</span>
             <h1 class="text-4xl md:text-6xl font-extrabold mb-6">Wawasan Digital & SEO.</h1>
             <p class="text-slate-400 text-lg max-w-2xl mx-auto">Pelajari cara kerja algoritma YouTube, strategi konten viral, dan teknik monetisasi terbaru langsung dari AI Expert kami.</p>
         </div>
    </div>

    <div class="container mx-auto px-6 py-16">
        
        <?php if($featured): ?>
        <!-- Featured Post -->
        <div class="mb-16">
            <div class="group relative rounded-[2rem] overflow-hidden shadow-2xl">
                <div class="md:flex h-full md:h-[500px]">
                    <div class="md:w-7/12 relative overflow-hidden">
                         <img src="<?= htmlspecialchars($featured['thumbnail']) ?>" class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                         <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition"></div>
                    </div>
                    <div class="md:w-5/12 bg-slate-50 p-10 md:p-16 flex flex-col justify-center">
                        <span class="text-brand-600 font-bold uppercase tracking-wider text-xs mb-4">Featured Story</span>
                        <h2 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-6 leading-tight">
                            <a href="post.php?slug=<?= $featured['slug'] ?>" class="hover:text-brand-600 transition"><?= htmlspecialchars($featured['title']) ?></a>
                        </h2>
                        <p class="text-slate-500 mb-8 line-clamp-3 leading-relaxed">
                             <?= isset($featured['meta_desc']) ? $featured['meta_desc'] : strip_tags(substr($featured['content'], 0, 150)).'...' ?>
                        </p>
                        <a href="post.php?slug=<?= $featured['slug'] ?>" class="btn-primary bg-brand-600 text-white px-8 py-3 rounded-full font-bold w-max shadow-lg hover:shadow-brand-500/30 transition">
                            Baca Selengkapnya
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Grid Posts -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-10">
            <?php foreach($remaining_posts as $post): ?>
            <article class="bg-white rounded-[2rem] border border-slate-100 shadow-lg shadow-slate-200/50 hover:-translate-y-2 transition duration-500 group flex flex-col h-full overflow-hidden">
                <div class="relative h-60 overflow-hidden">
                    <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    <div class="absolute top-4 left-4">
                        <span class="bg-white/90 backdrop-blur px-3 py-1 rounded-full text-[10px] font-extrabold uppercase text-slate-800">
                            Article
                        </span>
                    </div>
                </div>
                <div class="p-8 flex-1 flex flex-col">
                    <h3 class="text-xl font-bold text-slate-900 mb-4 leading-snug group-hover:text-brand-600 transition">
                        <a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                    </h3>
                    <p class="text-slate-500 text-sm line-clamp-3 mb-6 flex-1 leading-relaxed">
                        <?= isset($post['meta_desc']) ? $post['meta_desc'] : strip_tags(substr($post['content'], 0, 100)).'...' ?>
                    </p>
                    <div class="flex items-center justify-between pt-6 border-t border-slate-50">
                        <span class="text-xs text-slate-400 font-bold uppercase"><?= date('d M Y', strtotime($post['created_at'])) ?></span>
                        <a href="post.php?slug=<?= $post['slug'] ?>" class="text-brand-600 font-bold text-sm flex items-center gap-1 group-hover:gap-2 transition-all">
                            Read <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>

        <?php if(empty($posts)): ?>
        <div class="text-center py-20">
            <p class="text-slate-400">Belum ada artikel yang diterbitkan.</p>
        </div>
        <?php endif; ?>

    </div>
</div>

<!-- Newsletter -->
<section class="py-20 bg-brand-600 text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl -mr-20 -mt-20"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h2 class="text-3xl font-bold mb-4">Jangan Lewatkan Update Terbaru</h2>
        <p class="text-brand-100 mb-8">Dapatkan tips SEO dan monetisasi langsung ke inbox Anda.</p>
        <form class="max-w-md mx-auto flex gap-3">
            <input type="email" placeholder="Alamat Email Anda" class="flex-1 px-6 py-3 rounded-full text-slate-900 focus:outline-none focus:ring-4 focus:ring-brand-400/50">
            <button type="button" class="bg-slate-900 px-6 py-3 rounded-full font-bold hover:bg-slate-800 transition">Subscribe</button>
        </form>
    </div>
</section>

<script>lucide.createIcons();</script>
</body>
</html>