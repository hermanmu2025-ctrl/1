<?php
require_once 'functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) header("Location: blog.php");

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
                        <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600">A</div>
                        <span>Admin Team</span>
                    </div>
                    <span>&bull;</span>
                    <span><?= date('F d, Y', strtotime($post['created_at'])) ?></span>
                </div>
            </header>

            <div class="rounded-3xl overflow-hidden shadow-2xl mb-16">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full object-cover">
            </div>

            <!-- Content formatted with Tailwind Typography plugin standard styling -->
            <div class="prose prose-lg prose-slate mx-auto prose-headings:font-bold prose-headings:text-slate-900 prose-a:text-blue-600 prose-img:rounded-2xl">
                <?= $post['content'] ?>
            </div>
        </article>
        
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