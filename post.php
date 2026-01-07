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
    <div class="fixed top-0 left-0 w-full h-1.5 bg-slate-100 z-[60]">
        <div class="h-full bg-gradient-primary w-0 transition-all duration-100 ease-out" id="progress"></div>
    </div>

    <div class="container mx-auto px-6 max-w-4xl py-24">
        <article>
            <header class="mb-12 text-center max-w-3xl mx-auto">
                <div class="inline-block px-4 py-1.5 bg-blue-50 text-blue-600 text-xs font-bold rounded-full mb-8 uppercase tracking-wider">Marketing Insight</div>
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 mb-8 leading-tight capitalize tracking-tight"><?= htmlspecialchars($post['title']) ?></h1>
                <div class="flex items-center justify-center gap-6 text-slate-500 text-sm font-medium">
                    <div class="flex items-center gap-3">
                         <img src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" class="w-10 h-10 rounded-full border-2 border-white shadow-sm">
                         <span>Admin Team</span>
                    </div>
                    <span class="w-1.5 h-1.5 bg-slate-300 rounded-full"></span>
                    <span><?= date('d F Y', strtotime($post['created_at'])) ?></span>
                </div>
            </header>

            <div class="rounded-3xl overflow-hidden mb-16 shadow-2xl shadow-blue-900/10">
                <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full max-h-[500px] object-cover hover:scale-105 transition duration-[3s]">
            </div>

            <div class="prose prose-lg prose-slate max-w-none prose-headings:font-bold prose-headings:text-slate-900 prose-a:text-blue-600 prose-img:rounded-3xl prose-img:shadow-lg prose-p:leading-8 prose-p:text-slate-600 prose-strong:text-slate-800">
                <?= $post['content'] // Content is typically HTML from AI or Editor ?>
            </div>
            
            <!-- Share -->
            <div class="mt-16 pt-10 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="font-bold text-slate-800">Bagikan artikel ini:</p>
                <div class="flex gap-4">
                    <button class="bg-blue-600 text-white px-6 py-2 rounded-full text-sm font-bold flex items-center gap-2 hover:bg-blue-700 transition"><i data-lucide="facebook" class="w-4 h-4"></i> Facebook</button>
                    <button class="bg-sky-500 text-white px-6 py-2 rounded-full text-sm font-bold flex items-center gap-2 hover:bg-sky-600 transition"><i data-lucide="twitter" class="w-4 h-4"></i> Twitter</button>
                    <button class="bg-slate-800 text-white px-6 py-2 rounded-full text-sm font-bold flex items-center gap-2 hover:bg-slate-900 transition"><i data-lucide="link" class="w-4 h-4"></i> Copy Link</button>
                </div>
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