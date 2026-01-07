<?php
require_once 'functions.php';

// Logic Login (User) - Preserved from original architecture
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
                // --- NEW USER REGISTRATION WITH WELCOME BONUS ---
                try {
                    $pdo->beginTransaction();
                    $stmt = $pdo->prepare("INSERT INTO users (channel_id, channel_name, avatar_url, balance) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$channel_id, $channel_name, $avatar, WELCOME_BONUS]);
                    $user_id = $pdo->lastInsertId();

                    $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'deposit', ?, 'Welcome Bonus (New User)', 'completed')");
                    $stmt->execute([$user_id, WELCOME_BONUS]);

                    $pdo->commit();
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = "System Error: Gagal registrasi user baru.";
                }
            } else {
                $user_id = $user['id'];
                $pdo->prepare("UPDATE users SET channel_name=?, avatar_url=? WHERE id=?")->execute([$channel_name, $avatar, $user_id]);
            }

            if (empty($error)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['channel_id'] = $channel_id;
                header("Location: dashboard.php");
                exit;
            }
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
    <!-- HERO SECTION: High Conversion Design -->
    <section class="relative pt-16 pb-32 overflow-hidden bg-white">
        <div class="absolute top-0 right-0 w-2/3 h-full bg-gradient-to-l from-blue-50 to-transparent -z-10 clip-path-polygon opacity-70"></div>
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-purple-200/30 rounded-full blur-3xl"></div>
        
        <div class="container mx-auto px-6 flex flex-col-reverse lg:flex-row items-center gap-16 relative z-10">
            
            <!-- Hero Text -->
            <div class="lg:w-1/2 animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold tracking-wide mb-6 shadow-sm">
                    <span class="relative flex h-2 w-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                    </span>
                    PLATFORM YOUTUBE GROWTH #1
                </div>
                
                <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight text-slate-900 tracking-tight">
                    Solusi <span class="gradient-text">Premium</span> untuk <br>
                    Youtuber Profesional.
                </h1>
                <p class="text-slate-500 text-lg mb-8 leading-relaxed">
                    <strong class="text-slate-800">Urat ID</strong> bukan sekadar tukar subscriber. Kami adalah ekosistem SEO cerdas yang membantu channel Anda meledak di algoritma pencarian dengan aman, cepat, dan berkelas.
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <a href="#login" class="gradient-primary text-white px-8 py-4 rounded-xl font-bold shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-1 transition-all duration-300 flex items-center gap-2">
                        Mulai Sekarang <i data-lucide="arrow-right" class="w-5 h-5"></i>
                    </a>
                    <a href="#about" class="bg-white text-slate-700 border border-slate-200 px-8 py-4 rounded-xl font-bold hover:bg-slate-50 transition flex items-center gap-2">
                        <i data-lucide="play-circle" class="w-5 h-5"></i> Pelajari Cara Kerja
                    </a>
                </div>

                <div class="mt-10 flex items-center gap-6 text-slate-400 grayscale opacity-70">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/e/e1/Logo_of_YouTube_%282015-2017%29.svg" class="h-6">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg" class="h-6">
                    <span class="text-sm font-bold">Partner Terpercaya</span>
                </div>
            </div>

            <!-- Login / Interactive Card -->
            <div class="lg:w-1/2 w-full relative">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-purple-600 rounded-[2.5rem] blur opacity-30 animate-pulse"></div>
                <div id="login" class="relative glass-panel p-8 md:p-10 rounded-[2rem] shadow-2xl border border-white">
                    <div class="text-center mb-8">
                        <div class="w-16 h-16 bg-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-600/20 text-white">
                             <i data-lucide="lock" class="w-8 h-8"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-slate-800">Login Secure Dashboard</h3>
                        <p class="text-slate-500 text-sm mt-2">Masukkan Channel ID untuk akses instan.</p>
                    </div>
                    
                    <?php if($error): ?><div class="bg-red-50 border border-red-100 text-red-600 p-4 rounded-xl mb-6 text-sm font-bold flex gap-2 items-center animate-bounce"><i data-lucide="alert-circle" class="w-5 h-5"></i><?= $error ?></div><?php endif; ?>
                    
                    <form method="POST" class="space-y-5">
                        <div class="relative group">
                            <i data-lucide="youtube" class="absolute left-5 top-5 text-slate-400 w-5 h-5 group-focus-within:text-blue-600 transition"></i>
                            <input type="text" name="channel_id" required placeholder="Contoh: UCxxx... (Channel ID)" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-5 pl-14 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition font-medium text-slate-800 placeholder:text-slate-400">
                        </div>
                        
                        <button type="submit" class="w-full gradient-dark text-white font-bold py-5 rounded-xl shadow-xl hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex justify-center items-center gap-2 text-base">
                            <i data-lucide="log-in" class="w-5 h-5"></i> Masuk Dashboard
                        </button>
                    </form>
                    
                    <div class="mt-6 text-center">
                        <span class="text-xs font-bold text-green-600 bg-green-50 px-3 py-1 rounded-full">
                            <i data-lucide="gift" class="w-3 h-3 inline mr-1"></i> Bonus Saldo <?= formatRupiah(WELCOME_BONUS) ?> untuk Pengguna Baru
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- STATS STRIP -->
    <section class="bg-slate-900 py-10 border-y border-slate-800">
        <div class="container mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <h4 class="text-3xl font-extrabold text-white mb-1">15K+</h4>
                <p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Pengguna Aktif</p>
            </div>
            <div>
                <h4 class="text-3xl font-extrabold text-blue-500 mb-1">2.5M+</h4>
                <p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Subscriber Terkirim</p>
            </div>
            <div>
                <h4 class="text-3xl font-extrabold text-white mb-1">100%</h4>
                <p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Aman & Legal</p>
            </div>
            <div>
                <h4 class="text-3xl font-extrabold text-purple-500 mb-1">24/7</h4>
                <p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Support System</p>
            </div>
        </div>
    </section>

    <!-- TENTANG KAMI (About) -->
    <section id="about" class="py-24 bg-white">
        <div class="container mx-auto px-6 grid md:grid-cols-2 gap-16 items-center">
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1552664730-d307ca884978?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" class="rounded-[2rem] shadow-2xl rotate-3 hover:rotate-0 transition duration-500">
                <div class="absolute -bottom-10 -left-10 bg-white p-8 rounded-2xl shadow-xl max-w-xs border border-slate-100 hidden md:block">
                    <p class="text-4xl font-extrabold text-blue-600 mb-2">#1</p>
                    <p class="font-bold text-slate-800">Platform Optimasi Youtube Terpercaya di Indonesia</p>
                </div>
            </div>
            <div>
                <span class="text-blue-600 font-bold tracking-widest text-xs uppercase mb-4 block">Tentang Urat ID</span>
                <h2 class="text-4xl font-bold mb-6 text-slate-900 leading-tight">Membangun Komunitas Kreator yang Saling Mendukung.</h2>
                <p class="text-slate-500 mb-6 leading-relaxed">
                    Urat ID hadir sebagai solusi bagi Youtuber pemula maupun profesional yang kesulitan mendapatkan trafik organik. Sistem kami menggunakan algoritma <strong>"Mutual Growth"</strong> yang memastikan setiap interaksi (Subscribe, Like, View) dilakukan oleh manusia asli, bukan bot.
                </p>
                <ul class="space-y-4">
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600"><i data-lucide="check" class="w-4 h-4"></i></div>
                        <span class="font-bold text-slate-700">Verifikasi Akun Otomatis</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600"><i data-lucide="check" class="w-4 h-4"></i></div>
                        <span class="font-bold text-slate-700">Algoritma Anti-Drop & Proteksi</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <div class="w-6 h-6 bg-green-100 rounded-full flex items-center justify-center text-green-600"><i data-lucide="check" class="w-4 h-4"></i></div>
                        <span class="font-bold text-slate-700">Dukungan SEO Booster AI</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- LAYANAN KAMI (Services) -->
    <section id="services" class="py-24 bg-slate-50 relative overflow-hidden">
         <!-- Decorative Bg -->
         <div class="absolute top-0 right-0 w-96 h-96 bg-blue-100 rounded-full blur-[100px] opacity-50"></div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 max-w-2xl mx-auto">
                <h2 class="text-4xl font-bold mb-4 text-slate-900">Layanan Premium</h2>
                <p class="text-slate-500">Kami menyediakan alat lengkap untuk kebutuhan digital marketing channel Anda.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-lg shadow-slate-200/50 hover:-translate-y-2 transition duration-300 border border-slate-100">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-6 text-white shadow-lg shadow-blue-600/30">
                        <i data-lucide="users" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Subscriber Organik</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Dapatkan subscriber dari pengguna aktif real human. Membantu membuka fitur monetisasi lebih cepat.
                    </p>
                </div>
                <!-- Service 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-lg shadow-slate-200/50 hover:-translate-y-2 transition duration-300 border border-slate-100">
                    <div class="w-14 h-14 bg-red-600 rounded-2xl flex items-center justify-center mb-6 text-white shadow-lg shadow-red-600/30">
                        <i data-lucide="youtube" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Jam Tayang (Views)</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Tingkatkan retensi penonton dengan view berkualitas tinggi yang aman untuk AdSense.
                    </p>
                </div>
                <!-- Service 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-lg shadow-slate-200/50 hover:-translate-y-2 transition duration-300 border border-slate-100">
                    <div class="w-14 h-14 bg-purple-600 rounded-2xl flex items-center justify-center mb-6 text-white shadow-lg shadow-purple-600/30">
                        <i data-lucide="bar-chart-2" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">SEO Audit</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">
                        Analisa kata kunci dan optimasi metadata video agar mudah ditemukan di pencarian Google & YouTube.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- PROMOSI / SALDO GRATIS -->
    <section class="py-20">
        <div class="container mx-auto px-6">
            <div class="bg-gradient-to-r from-indigo-900 to-slate-900 rounded-[3rem] p-10 md:p-16 text-white relative overflow-hidden shadow-2xl">
                <!-- Abstract Shapes -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400 rounded-full blur-[100px] opacity-20"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-600 rounded-full blur-[100px] opacity-20"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-10">
                    <div class="md:w-2/3">
                        <div class="inline-block bg-yellow-400 text-yellow-900 font-extrabold px-4 py-1 rounded-lg text-xs mb-6 uppercase tracking-wider">
                            Promo Spesial Hari Ini
                        </div>
                        <h2 class="text-4xl md:text-5xl font-bold mb-6">Daftar Sekarang, Klaim <br> Saldo Gratis <span class="text-yellow-400"><?= formatRupiah(WELCOME_BONUS) ?></span></h2>
                        <p class="text-slate-300 text-lg mb-8 max-w-xl">
                            Tanpa syarat rumit! Cukup masukkan Channel ID Anda, sistem kami akan otomatis memberikan modal awal untuk kampanye pertama Anda.
                        </p>
                        <div class="flex gap-4">
                            <a href="#login" class="bg-white text-slate-900 px-8 py-4 rounded-xl font-bold hover:bg-slate-100 transition shadow-lg flex items-center gap-2">
                                <i data-lucide="gift" class="w-5 h-5 text-red-500"></i> Ambil Bonus Saya
                            </a>
                        </div>
                    </div>
                    <div class="md:w-1/3 flex justify-center relative perspective-1000">
                        <!-- Premium Back Glow -->
                        <div class="absolute inset-0 bg-gradient-to-tr from-yellow-400/40 to-purple-500/40 rounded-full blur-[80px] animate-pulse"></div>
                        
                        <!-- Main 3D Composition -->
                        <div class="relative z-10 group cursor-pointer">
                            <div class="animate-float relative">
                                <img src="https://cdn3d.iconscout.com/3d/premium/thumb/gift-box-and-gold-coin-5379685-4496468.png" 
                                     class="w-72 md:w-80 drop-shadow-2xl transition-transform duration-500 transform group-hover:scale-110 group-hover:rotate-6" 
                                     alt="Premium Bonus Gift">
                                
                                <!-- Floating Badge 1 -->
                                <div class="absolute -top-4 -right-4 bg-white/90 backdrop-blur-md p-3 rounded-2xl shadow-xl shadow-yellow-500/20 animate-bounce delay-700 border border-white/50">
                                    <div class="flex items-center gap-2">
                                        <div class="bg-green-100 p-1.5 rounded-lg text-green-600">
                                            <i data-lucide="check-circle" class="w-4 h-4"></i>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-slate-400 font-bold uppercase">Status</p>
                                            <p class="text-xs font-extrabold text-slate-800">Available</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Floating Badge 2 -->
                                <div class="absolute bottom-10 -left-8 bg-white/90 backdrop-blur-md p-3 rounded-2xl shadow-xl shadow-blue-500/20 animate-bounce delay-1000 border border-white/50 hidden md:block">
                                    <div class="flex items-center gap-2">
                                        <div class="bg-blue-100 p-1.5 rounded-lg text-blue-600">
                                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                                        </div>
                                        <p class="text-xs font-extrabold text-slate-800">Instant Claim</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- KEUNGGULAN (Advantages) -->
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold mb-4 text-slate-900">Mengapa Urat ID Istimewa?</h2>
                <p class="text-slate-500">Fitur berkelas yang tidak akan Anda temukan di platform lain.</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Feat 1 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">100% Aman</h4>
                    <p class="text-sm text-slate-500">Tanpa password akun. Kami hanya butuh Channel ID publik.</p>
                </div>
                 <!-- Feat 2 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-purple-600 group-hover:text-white transition">
                        <i data-lucide="zap" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Proses Instan</h4>
                    <p class="text-sm text-slate-500">Saldo masuk detik itu juga setelah sistem memverifikasi aksi Anda.</p>
                </div>
                 <!-- Feat 3 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-green-600 group-hover:text-white transition">
                        <i data-lucide="dollar-sign" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Reward Tinggi</h4>
                    <p class="text-sm text-slate-500">Dapatkan bayaran per subscribe tertinggi di kelasnya.</p>
                </div>
                 <!-- Feat 4 -->
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-200 transition group">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-xl flex items-center justify-center mb-4 group-hover:bg-red-600 group-hover:text-white transition">
                        <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                    </div>
                    <h4 class="font-bold text-lg mb-2">Sanksi Tegas</h4>
                    <p class="text-sm text-slate-500">User yang unsubscribe akan didenda otomatis oleh sistem.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- BLOG SECTION -->
    <section class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-4">
                <div>
                    <span class="text-blue-600 font-bold tracking-widest text-xs uppercase mb-2 block">Wawasan Digital</span>
                    <h2 class="text-3xl font-bold text-slate-900">Blog & SEO Tips</h2>
                </div>
                <a href="blog.php" class="px-6 py-3 bg-white border border-slate-200 rounded-full font-bold text-sm hover:bg-blue-600 hover:text-white transition flex items-center gap-2">
                    Baca Semua Artikel <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($latest_posts as $post): ?>
                <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition group border border-slate-200">
                    <div class="h-48 overflow-hidden bg-slate-200 relative">
                        <div class="absolute inset-0 bg-black/10 group-hover:bg-transparent transition z-10"></div>
                        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="p-6">
                        <div class="text-[10px] font-bold text-blue-600 mb-2 uppercase tracking-wide bg-blue-50 w-fit px-2 py-1 rounded">Strategy</div>
                        <h3 class="text-lg font-bold mb-3 group-hover:text-blue-600 line-clamp-2 leading-snug">
                            <a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                        </h3>
                        <p class="text-slate-500 text-sm line-clamp-3 leading-relaxed">
                            <?= isset($post['meta_desc']) && $post['meta_desc'] ? $post['meta_desc'] : strip_tags(substr($post['content'], 0, 100)).'...' ?>
                        </p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<!-- PREMIUM FOOTER (CLEAN VERSION) -->
<footer class="bg-slate-950 text-slate-300 pt-24 pb-10 border-t border-slate-900 font-sans">
    <div class="container mx-auto px-6 flex flex-col items-center text-center">
        <!-- Brand (Centered) -->
        <div class="mb-12 max-w-lg">
            <a href="index.php" class="flex items-center justify-center gap-2 mb-6">
                <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center">
                    <i data-lucide="zap" class="text-white w-6 h-6"></i>
                </div>
                <span class="text-2xl font-extrabold text-white tracking-tight">Urat<span class="text-blue-600">ID</span></span>
            </a>
            <p class="text-slate-500 text-sm leading-relaxed mb-6">
                Platform growth hacking YouTube #1 di Indonesia dengan teknologi keamanan mutakhir, fitur SEO AI, dan komunitas kreator terbesar.
            </p>
            <div class="flex justify-center gap-4">
                <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-blue-600 transition text-white"><i data-lucide="instagram" class="w-4 h-4"></i></a>
                <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-red-600 transition text-white"><i data-lucide="youtube" class="w-4 h-4"></i></a>
                <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-sky-500 transition text-white"><i data-lucide="twitter" class="w-4 h-4"></i></a>
            </div>
        </div>

        <div class="w-full pt-8 border-t border-slate-900 text-center text-sm text-slate-600 flex flex-col md:flex-row justify-between items-center">
            <p>&copy; <?= date('Y') ?> PT Urat Digital Indonesia. All rights reserved.</p>
            <p class="flex items-center gap-2 mt-4 md:mt-0">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> System Operational
            </p>
        </div>
    </div>
</footer>

<script>
lucide.createIcons();
</script>
</body>
</html>