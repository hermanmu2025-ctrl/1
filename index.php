<?php
require_once 'functions.php';

// Login Logic
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['channel_id'])) {
    $channel_id = trim($_POST['channel_id']);
    if (empty($channel_id)) {
        $error = "Silakan masukkan Channel ID Anda.";
    } else {
        $info = getChannelInfo($channel_id);
        $channel_name = $info['snippet']['title'];
        $avatar = $info['snippet']['thumbnails']['default']['url'];
        
        // Check User
        $stmt = $pdo->prepare("SELECT * FROM users WHERE channel_id = ?");
        $stmt->execute([$channel_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Registration
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO users (channel_id, channel_name, avatar_url, balance) VALUES (?, ?, ?, ?)");
                $stmt->execute([$channel_id, $channel_name, $avatar, WELCOME_BONUS]);
                $newId = $pdo->lastInsertId();
                
                // Welcome Bonus Trx
                $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'deposit', ?, 'Welcome Bonus', 'completed')")
                    ->execute([$newId, WELCOME_BONUS]);
                $pdo->commit();
                $user_id = $newId;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Gagal mendaftar. Silakan coba lagi.";
            }
        } else {
            $user_id = $user['id'];
            // Update info
            $pdo->prepare("UPDATE users SET channel_name=?, avatar_url=? WHERE id=?")->execute([$channel_name, $avatar, $user_id]);
        }

        if (empty($error)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['channel_id'] = $channel_id;
            header("Location: dashboard.php");
            exit;
        }
    }
}

$posts = $pdo->query("SELECT * FROM posts ORDER BY created_at DESC LIMIT 3")->fetchAll();
$page_title = "Platform Growth YouTube #1";
include 'header.php';
?>

<main>
    <!-- HERO SECTION -->
    <section class="relative pt-20 pb-32 overflow-hidden">
        <div class="absolute top-0 right-0 w-1/2 h-full bg-blue-50 -z-10 rounded-l-[100px] opacity-60"></div>
        <div class="container mx-auto px-6 flex flex-col lg:flex-row items-center gap-16">
            
            <!-- Left Content -->
            <div class="lg:w-1/2 space-y-8">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 text-blue-700 rounded-full text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></span> Teknologi AI Terbaru v2.0
                </div>
                <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 leading-tight tracking-tight">
                    Akselerasi <span class="text-gradient">YouTube</span> <br> Secara Profesional.
                </h1>
                <p class="text-lg text-slate-500 leading-relaxed max-w-xl">
                    Urat ID bukan sekadar platform saling subscribe. Kami adalah ekosistem digital yang membantu Anda membangun pondasi channel yang kuat, organik, dan tahan lama.
                </p>
                <div class="flex gap-4">
                    <a href="#auth" class="btn-primary text-white px-8 py-4 rounded-xl font-bold shadow-xl shadow-blue-500/30 flex items-center gap-3">
                        Mulai Sekarang <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                    <a href="#features" class="px-8 py-4 rounded-xl font-bold border border-slate-200 text-slate-600 hover:bg-white hover:shadow-lg transition">
                        Pelajari Cara Kerja
                    </a>
                </div>
                
                <div class="flex items-center gap-6 pt-4 grayscale opacity-60">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Logo_of_YouTube_%282015-2017%29.svg" class="h-6">
                    <span class="text-sm font-bold text-slate-400">Compatible System</span>
                </div>
            </div>

            <!-- Right Form (Login) -->
            <div class="lg:w-1/2 w-full" id="auth">
                <div class="glass-card p-10 rounded-3xl shadow-2xl relative">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-yellow-400 rounded-full blur-3xl opacity-20"></div>
                    
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-white shadow-lg">
                            <i data-lucide="lock" class="w-8 h-8"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-slate-900">Akses Member Area</h2>
                        <p class="text-slate-500 text-sm mt-2">Login aman tanpa password, hanya menggunakan Channel ID Publik.</p>
                    </div>

                    <?php if($error): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-bold flex items-center gap-2 border border-red-100">
                            <i data-lucide="alert-circle" class="w-5 h-5"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-5">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-700 uppercase ml-1">Channel ID Youtube</label>
                            <input type="text" name="channel_id" required placeholder="Contoh: UC123abc..." 
                                   class="w-full px-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 outline-none transition font-medium text-slate-800">
                        </div>
                        <button type="submit" class="w-full btn-primary text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition flex justify-center items-center gap-2">
                            <i data-lucide="log-in" class="w-5 h-5"></i> Masuk Dashboard
                        </button>
                    </form>
                    <div class="mt-6 text-center">
                        <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                            <i data-lucide="gift" class="w-3 h-3 inline mr-1"></i> Bonus Saldo <?= formatRupiah(WELCOME_BONUS) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES GRID -->
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-3xl font-bold text-slate-900 mb-4">Kenapa Memilih Urat ID?</h2>
                <p class="text-slate-500">Kami menggabungkan teknologi keamanan tingkat tinggi dengan strategi marketing organik.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-blue-200 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                        <i data-lucide="shield-check" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Keamanan 100%</h3>
                    <p class="text-slate-500 leading-relaxed">Tanpa meminta password akun Google Anda. Sistem kami bekerja hanya dengan Channel ID publik yang aman.</p>
                </div>
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-blue-200 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm text-purple-600 group-hover:bg-purple-600 group-hover:text-white transition">
                        <i data-lucide="cpu" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Algoritma Anti-Drop</h3>
                    <p class="text-slate-500 leading-relaxed">Sistem monitoring otomatis mendeteksi pengguna yang melakukan unsubscribe dan memberikan sanksi tegas.</p>
                </div>
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-100 hover:border-blue-200 hover:shadow-xl transition group">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-sm text-green-600 group-hover:bg-green-600 group-hover:text-white transition">
                        <i data-lucide="trending-up" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Organic Growth</h3>
                    <p class="text-slate-500 leading-relaxed">Interaksi dilakukan oleh manusia asli (Real Human), bukan bot, sehingga aman untuk monetisasi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- BLOG SECTION -->
    <section class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <span class="text-blue-600 font-bold uppercase text-xs tracking-wider">Edukasi Kreator</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Strategi & Insight Terbaru</h2>
                </div>
                <a href="blog.php" class="hidden md:flex items-center gap-2 text-slate-600 hover:text-blue-600 font-bold transition">Lihat Semua <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($posts as $post): ?>
                <article class="bg-white rounded-3xl overflow-hidden shadow-lg shadow-slate-200/50 hover:-translate-y-2 transition duration-300">
                    <div class="h-48 overflow-hidden">
                        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover">
                    </div>
                    <div class="p-8">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 line-clamp-2 leading-snug">
                            <a href="post.php?slug=<?= $post['slug'] ?>" class="hover:text-blue-600 transition"><?= htmlspecialchars($post['title']) ?></a>
                        </h3>
                        <p class="text-slate-500 text-sm mb-4 line-clamp-3 leading-relaxed">
                            <?= isset($post['meta_desc']) ? $post['meta_desc'] : strip_tags(substr($post['content'], 0, 100)).'...' ?>
                        </p>
                        <a href="post.php?slug=<?= $post['slug'] ?>" class="text-sm font-bold text-blue-600 flex items-center gap-1">Baca Selengkapnya <i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<footer class="bg-slate-900 text-white py-16 border-t border-slate-800">
    <div class="container mx-auto px-6 text-center">
        <div class="flex justify-center items-center gap-2 mb-8">
             <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
                 <i data-lucide="zap" class="w-6 h-6"></i>
             </div>
             <span class="text-2xl font-bold">Urat<span class="text-blue-500">ID</span></span>
        </div>
        <p class="text-slate-400 max-w-lg mx-auto mb-8 leading-relaxed">
            Platform komunitas Youtuber Indonesia yang berfokus pada pertumbuhan channel yang sehat, aman, dan berkelanjutan.
        </p>
        <p class="text-slate-600 text-sm">&copy; <?= date('Y') ?> Urat Digital Indonesia. All Rights Reserved.</p>
    </div>
</footer>

<script>lucide.createIcons();</script>
</body>
</html>