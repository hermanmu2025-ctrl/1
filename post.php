<?php
require_once 'functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM posts WHERE slug = ?");
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) header("Location: blog.php");

$page_title = htmlspecialchars($post['title']);
$meta_desc = isset($post['meta_desc']) ? $post['meta_desc'] : substr(strip_tags($post['content']), 0, 160);
include 'header.php';
?>

<div class="bg-white min-h-screen pb-20">
    <!-- Progress Bar -->
    <div class="fixed top-0 left-0 w-full h-1 bg-slate-100 z-50">
        <div class="h-full bg-blue-600 w-0" id="progress"></div>
    </div>

    <div class="container mx-auto px-6 max-w-4xl py-20">
        <article>
            <header class="mb-10 text-center">
                <div class="inline-block px-4 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-full mb-6 uppercase tracking-wider">Marketing Insight</div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-6 leading-tight capitalize"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="flex items-center justify-center gap-4 text-slate-500 text-sm font-medium">
                    <div class="flex items-center gap-2">
                         <img src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" class="w-8 h-8 rounded-full">
                         <span>Admin Team</span>
                    </div>
                    <span>•</span>
                    <span><?= date('d F Y', strtotime($post['created_at'])) ?></span>
                </div>
            </header>

            <div class="rounded-2xl overflow-hidden mb-12 shadow-2xl shadow-blue-900/10">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full max-h-[500px] object-cover hover:scale-105 transition duration-[2s]">
            </div>

            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-bold prose-headings:text-slate-800 prose-a:text-blue-600 prose-img:rounded-xl leading-8">
                <?= $post['content'] // Content is typically HTML from AI or Editor ?>
            </div>
        </article>
    </div>
</div>

<script>
// Reading Progress Bar
window.onscroll = function() {
  var winScroll = document.body.scrollTop || document.documentElement.scrollTop;
  var height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
  var scrolled = (winScroll / height) * 100;
  document.getElementById("progress").style.width = scrolled + "%";
};
lucide.createIcons();
</script>
</body>
</html>