<?php
require_once 'functions.php';

// Login Logic
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['channel_id'])) {
    $channel_id = trim($_POST['channel_id']);
    
    // Simple validation
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
                // New User
                $stmt = $pdo->prepare("INSERT INTO users (channel_id, channel_name, avatar_url) VALUES (?, ?, ?)");
                $stmt->execute([$channel_id, $channel_name, $avatar]);
                $user_id = $pdo->lastInsertId();
            } else {
                // Existing User
                $user_id = $user['id'];
                $pdo->prepare("UPDATE users SET channel_name=?, avatar_url=? WHERE id=?")->execute([$channel_name, $avatar, $user_id]);
            }
            $_SESSION['user_id'] = $user_id;
            $_SESSION['channel_id'] = $channel_id;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Channel tidak ditemukan. Pastikan ID benar.";
        }
    }
}

$latest_posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 3")->fetchAll();
$page_title = "Home";
include 'header.php';
?>

<main>
    <!-- HERO -->
    <section class="relative pt-20 pb-32 overflow-hidden bg-white">
        <!-- Decor -->
        <div class="absolute top-0 right-0 w-1/2 h-full bg-gradient-to-l from-blue-50 to-transparent -z-10"></div>
        <div class="absolute -top-20 -right-20 w-96 h-96 bg-blue-200 rounded-full blur-3xl opacity-30"></div>
        
        <div class="container mx-auto px-6 text-center md:text-left grid md:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-100 text-blue-600 text-xs font-bold tracking-wide mb-6 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    ECOSYSTEM YOUTUBE INDONESIA #1
                </div>
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight text-slate-900">
                    Tingkatkan <span class="gradient-text">Otoritas</span> <br>
                    Channel Anda.
                </h1>
                <p class="text-slate-500 text-lg mb-8 leading-relaxed max-w-lg">
                    Bergabung dengan ribuan kreator cerdas. Dapatkan subscriber aktif, tingkatkan SEO, dan monetisasi lebih cepat dengan sistem aman kami.
                </p>
                
                <!-- LOGIN FORM CARD -->
                <div id="login" class="glass-panel p-8 rounded-2xl shadow-glow relative z-10 max-w-md">
                    <h3 class="text-xl font-bold mb-1">Mulai Gratis</h3>
                    <p class="text-slate-400 text-sm mb-6">Cukup masukkan Channel ID Anda</p>
                    
                    <?php if($error): ?>
                        <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-xs font-medium border border-red-100 flex items-center gap-2">
                            <i data-lucide="alert-circle" class="w-4 h-4"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-4">
                        <div class="relative">
                            <input type="text" name="channel_id" required placeholder="UCxxxxxxxxxxxx..." 
                                   class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 pl-12 text-slate-800 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition font-mono text-sm">
                            <span class="absolute left-4 top-4 text-slate-400">
                                <i data-lucide="youtube" class="w-5 h-5"></i>
                            </span>
                        </div>
                        <button type="submit" class="w-full gradient-primary hover:opacity-90 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-blue-600/20 flex justify-center items-center gap-2">
                            Masuk Dashboard <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Hero Image/Graphic -->
            <div class="hidden md:block relative">
                <img src="https://illustrations.popsy.co/amber/success.svg" alt="Growth" class="w-full drop-shadow-2xl hover:scale-105 transition duration-500">
            </div>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="features" class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16 max-w-2xl mx-auto">
                <h2 class="text-3xl font-bold mb-4 text-slate-900">Fitur <span class="text-blue-600">Premium</span></h2>
                <p class="text-slate-500">Desain sistem kami berfokus pada kecepatan, keamanan, dan kenyamanan pengguna.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-blue-50 rounded-xl flex items-center justify-center text-blue-600 mb-6">
                        <i data-lucide="zap" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Proses Kilat</h3>
                    <p class="text-slate-500">Sistem otomatis memverifikasi setiap tindakan secara real-time untuk memastikan saldo Anda aman.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-purple-50 rounded-xl flex items-center justify-center text-purple-600 mb-6">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Anti-Cheat</h3>
                    <p class="text-slate-500">Algoritma proteksi canggih yang mendeteksi dan menghukum user yang melakukan unsubscribe.</p>
                </div>
                <div class="bg-white p-8 rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/50 hover:-translate-y-1 transition duration-300">
                    <div class="w-14 h-14 bg-green-50 rounded-xl flex items-center justify-center text-green-600 mb-6">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Komunitas Nyata</h3>
                    <p class="text-slate-500">Hanya channel aktif yang diizinkan masuk, menciptakan ekosistem pertumbuhan organik.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- BLOG -->
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Wawasan Terbaru</h2>
                    <p class="text-slate-500">Tips SEO dan YouTube Marketing.</p>
                </div>
                <a href="blog.php" class="text-blue-600 hover:text-blue-700 font-semibold flex items-center gap-1">
                    Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($latest_posts as $post): ?>
                <article class="group cursor-pointer bg-slate-50 rounded-2xl overflow-hidden hover:shadow-lg transition">
                    <div class="overflow-hidden aspect-video bg-slate-200">
                        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" alt="thumb" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="p-6">
                        <div class="text-xs text-blue-600 font-bold uppercase tracking-wider mb-2">Artikel</div>
                        <h3 class="text-lg font-bold mb-3 group-hover:text-blue-600 transition"><a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a></h3>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-slate-900 text-slate-300 pt-20 pb-10">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-2">
                     <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="zap" class="text-white w-4 h-4"></i>
                        </div>
                        <span class="text-2xl font-bold text-white">Urat<span class="text-blue-500">ID</span></span>
                    </div>
                    <p class="text-slate-400 max-w-sm">Platform nomor 1 untuk pertumbuhan aset digital Anda.</p>
                </div>
                <div>
                    <h4 class="font-bold text-white mb-6">Menu</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="hover:text-blue-400">Home</a></li>
                        <li><a href="#" class="hover:text-blue-400">Deposit</a></li>
                        <li><a href="#" class="hover:text-blue-400">Kontak</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-slate-800 pt-8 text-center text-sm">
                &copy; <?= date('Y') ?> Urat ID. All rights reserved.
            </div>
        </div>
    </footer>
</main>
<script>
    lucide.createIcons();
</script>
</body>
</html>