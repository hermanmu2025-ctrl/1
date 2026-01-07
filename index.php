<?php
require_once 'functions.php';

// Logic Login (User)
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['channel_id'])) {
    $channel_id = trim($_POST['channel_id']);
    if (empty($channel_id)) {
        $error = "Mohon masukkan Channel ID.";
    } else {
        $info = getChannelInfo($channel_id);
        if ($info) {
            $channel_name = $info['snippet']['title'];
            $avatar = $info['snippet']['thumbnails']['default']['url'];
            
            $stmt = $pdo->prepare("SELECT * FROM users WHERE channel_id = ?");
            $stmt->execute([$channel_id]);
            $user = $stmt->fetch();
            
            if (!$user) {
                $stmt = $pdo->prepare("INSERT INTO users (channel_id, channel_name, avatar_url) VALUES (?, ?, ?)");
                $stmt->execute([$channel_id, $channel_name, $avatar]);
                $user_id = $pdo->lastInsertId();
            } else {
                $user_id = $user['id'];
                $pdo->prepare("UPDATE users SET channel_name=?, avatar_url=? WHERE id=?")->execute([$channel_name, $avatar, $user_id]);
            }
            $_SESSION['user_id'] = $user_id;
            $_SESSION['channel_id'] = $channel_id;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Channel tidak ditemukan.";
        }
    }
}

$latest_posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 3")->fetchAll();
$page_title = "Home";
include 'header.php';
?>

<main>
    <!-- HERO SECTION -->
    <section class="relative pt-20 pb-32 overflow-hidden bg-white">
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-blue-50 to-transparent -z-10"></div>
        <div class="container mx-auto px-6 text-center md:text-left grid md:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-bold tracking-wide mb-6 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span> SYSTEM V2.0 LIVE
                </div>
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight text-slate-900">
                    Tingkatkan <span class="gradient-text">Otoritas</span> <br> Channel Anda.
                </h1>
                <p class="text-slate-500 text-lg mb-8 leading-relaxed max-w-lg">
                    Dapatkan subscriber aktif, tingkatkan SEO, dan monetisasi lebih cepat dengan teknologi AI dan sistem keamanan terbaru.
                </p>
                
                <div id="login" class="glass-panel p-8 rounded-2xl shadow-xl relative z-10 max-w-md">
                    <h3 class="text-xl font-bold mb-1">Mulai Sekarang</h3>
                    <p class="text-slate-400 text-sm mb-6">Masukkan Channel ID untuk akses Dashboard</p>
                    <?php if($error): ?><div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-xs font-medium"><?= $error ?></div><?php endif; ?>
                    <form method="POST" class="space-y-4">
                        <input type="text" name="channel_id" required placeholder="UCxxxxxxxxxxxx..." class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-sm outline-none focus:ring-2 focus:ring-blue-500">
                        <button type="submit" class="w-full gradient-primary text-white font-bold py-4 rounded-xl shadow-lg hover:opacity-90 transition">Masuk Dashboard</button>
                    </form>
                </div>
            </div>
            <div class="hidden md:block">
                <img src="https://illustrations.popsy.co/amber/success.svg" class="w-full drop-shadow-2xl hover:scale-105 transition duration-500">
            </div>
        </div>
    </section>

    <!-- BLOG SECTION -->
    <section class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Artikel & SEO</h2>
                    <p class="text-slate-500">Tips terbaru seputar YouTube Growth.</p>
                </div>
                <a href="blog.php" class="text-blue-600 font-bold flex items-center gap-1">Lihat Blog <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($latest_posts as $post): ?>
                <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition group">
                    <div class="h-48 overflow-hidden bg-slate-200">
                        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="p-6">
                        <h3 class="text-lg font-bold mb-2 group-hover:text-blue-600 line-clamp-2"><a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                        <p class="text-slate-500 text-sm line-clamp-3"><?= strip_tags(substr($post['content'], 0, 150)) ?>...</p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>
<footer class="bg-slate-900 text-slate-300 py-12 text-center">
    <p>&copy; <?= date('Y') ?> Urat ID. All rights reserved.</p>
</footer>
<script>lucide.createIcons();</script>
</body>
</html>