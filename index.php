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
            $error = "Channel tidak ditemukan. Pastikan ID benar.";
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
        <div class="absolute top-0 right-0 w-3/4 h-full bg-gradient-to-l from-blue-50/80 to-transparent -z-10 clip-path-polygon"></div>
        <div class="absolute bottom-0 left-0 w-full h-32 bg-gradient-to-t from-white to-transparent z-10"></div>
        
        <div class="container mx-auto px-6 text-center md:text-left grid md:grid-cols-2 gap-16 items-center relative z-20">
            <div class="animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-blue-50 border border-blue-100 text-blue-700 text-xs font-bold tracking-wide mb-8 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span> SYSTEM V2.0 LIVE UPDATE
                </div>
                <h1 class="text-5xl md:text-7xl font-extrabold mb-6 leading-tight text-slate-900 tracking-tight">
                    Dominasi <br> <span class="gradient-text">YouTube</span> Anda.
                </h1>
                <p class="text-slate-500 text-lg mb-10 leading-relaxed max-w-lg">
                    Platform <b class="text-slate-800">Urat ID</b> membantu kreator meningkatkan subscriber, reputasi SEO, dan engagement secara organik dengan teknologi keamanan enterprise.
                </p>
                
                <div id="login" class="glass-panel p-8 rounded-3xl shadow-2xl relative z-10 max-w-md border border-slate-100">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="bg-blue-600 p-2 rounded-lg text-white">
                            <i data-lucide="log-in" class="w-5 h-5"></i>
                        </div>
                        <div>
                             <h3 class="text-lg font-bold text-slate-800">Akses Dashboard</h3>
                             <p class="text-slate-400 text-xs">Gratis pendaftaran selamanya</p>
                        </div>
                    </div>
                    
                    <?php if($error): ?><div class="bg-red-50 border border-red-100 text-red-600 p-3 rounded-xl mb-4 text-xs font-bold flex gap-2 items-center"><i data-lucide="alert-circle" class="w-4 h-4"></i><?= $error ?></div><?php endif; ?>
                    
                    <form method="POST" class="space-y-4">
                        <div class="relative">
                            <i data-lucide="youtube" class="absolute left-4 top-4 text-slate-400 w-5 h-5"></i>
                            <input type="text" name="channel_id" required placeholder="Masukkan Channel ID (UCxxx...)" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 pl-12 text-sm outline-none focus:ring-2 focus:ring-blue-500 transition font-medium text-slate-700">
                        </div>
                        <button type="submit" class="w-full gradient-primary text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-600/20 hover:shadow-blue-600/40 hover:-translate-y-1 transition-all duration-300 flex justify-center items-center gap-2">
                            Masuk Sekarang <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
            <div class="hidden md:block relative">
                <!-- Abstract Decorative Elements -->
                <div class="absolute top-10 right-10 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
                <div class="absolute top-0 -left-4 w-72 h-72 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
                
                <img src="https://illustrations.popsy.co/amber/digital-nomad.svg" class="w-full drop-shadow-2xl hover:scale-105 transition duration-700 relative z-10">
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="py-20 bg-white">
        <div class="container mx-auto px-6 text-center max-w-4xl">
            <h2 class="text-3xl font-bold text-slate-900 mb-6">Tentang <span class="text-blue-600">Urat ID</span></h2>
            <p class="text-slate-600 text-lg leading-relaxed mb-12">
                Urat ID adalah ekosistem pertumbuhan digital premium yang dirancang untuk Content Creator modern. 
                Kami menggabungkan algoritma pertukaran organik dengan keamanan database terenkripsi untuk memastikan setiap pertumbuhan channel Anda aman, valid, dan berdampak positif pada SEO Youtube.
            </p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <h3 class="text-3xl font-extrabold text-blue-600 mb-1">10K+</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Users</p>
                </div>
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <h3 class="text-3xl font-extrabold text-blue-600 mb-1">500K+</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transactions</p>
                </div>
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <h3 class="text-3xl font-extrabold text-blue-600 mb-1">24/7</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Monitoring</p>
                </div>
                <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100">
                    <h3 class="text-3xl font-extrabold text-blue-600 mb-1">100%</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Secure</p>
                </div>
            </div>
        </div>
    </section>

    <!-- FEATURES & ADVANTAGES -->
    <section id="features" class="py-24 bg-slate-900 text-white relative overflow-hidden">
        <!-- Background Glow -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-30 pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-blue-600 rounded-full blur-[100px]"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-purple-600 rounded-full blur-[100px]"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16">
                <span class="text-blue-400 font-bold tracking-widest text-xs uppercase mb-2 block">Keunggulan Premium</span>
                <h2 class="text-4xl font-bold mb-4">Fitur Berkelas Profesional</h2>
                <p class="text-slate-400 max-w-2xl mx-auto">Kami tidak sekadar platform tukar subscriber. Kami adalah alat bantu growth hacking.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-8 rounded-3xl hover:bg-slate-800 transition duration-300">
                    <div class="w-14 h-14 bg-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-600/20">
                        <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Anti-Drop Protection</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Sistem kami memiliki algoritma Cron Job otomatis yang mendeteksi user yang melakukan unsubscribe dan memberikan penalti instan.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-8 rounded-3xl hover:bg-slate-800 transition duration-300">
                    <div class="w-14 h-14 bg-purple-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-purple-600/20">
                        <i data-lucide="zap" class="w-7 h-7 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">Instant Verification</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Setiap aksi subscribe diverifikasi secara real-time untuk memastikan saldo reward Anda cair tanpa menunggu lama.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700 p-8 rounded-3xl hover:bg-slate-800 transition duration-300">
                    <div class="w-14 h-14 bg-green-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-green-600/20">
                        <i data-lucide="trending-up" class="w-7 h-7 text-white"></i>
                    </div>
                    <h3 class="text-xl font-bold mb-3">SEO Booster</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Trafik yang datang berasal dari real human user, memberikan sinyal positif ke algoritma YouTube untuk rekomendasi video.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICES & PRICING -->
    <section id="services" class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-6">
                <div class="md:w-1/2">
                    <h2 class="text-4xl font-bold text-slate-900 mb-4">Layanan & Marketing</h2>
                    <p class="text-slate-500">Pilih metode pertumbuhan yang sesuai dengan target audience channel Anda.</p>
                </div>
                <div class="flex gap-2">
                     <span class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm font-bold text-slate-600 shadow-sm flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Termurah</span>
                     <span class="px-4 py-2 bg-white border border-slate-200 rounded-full text-sm font-bold text-slate-600 shadow-sm flex items-center gap-2"><i data-lucide="check" class="w-4 h-4 text-green-500"></i> Tercepat</span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Service Card 1 -->
                <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 hover:-translate-y-2 transition duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="bg-blue-100 p-3 rounded-xl">
                            <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <span class="bg-blue-600 text-white text-[10px] font-bold px-2 py-1 rounded uppercase">Best Seller</span>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Organic Subscriber</h3>
                    <p class="text-slate-500 text-sm mb-6">Dapatkan subscriber dari user aktif lain dengan sistem mutualisme yang adil.</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i> Real Human Users</li>
                        <li class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i> Permanen (Garansi)</li>
                        <li class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i> Harga: Rp <?= PRICE_PER_SUB ?> / Sub</li>
                    </ul>
                    <a href="#login" class="block w-full text-center py-3 rounded-xl border-2 border-blue-600 text-blue-600 font-bold hover:bg-blue-600 hover:text-white transition">Pesan Sekarang</a>
                </div>

                <!-- Service Card 2 -->
                <div class="bg-gradient-to-br from-slate-900 to-slate-800 p-8 rounded-3xl shadow-xl hover:-translate-y-2 transition duration-300 text-white relative overflow-hidden">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-blue-600 rounded-full blur-[60px] opacity-50"></div>
                    
                    <div class="flex justify-between items-start mb-6 relative z-10">
                        <div class="bg-white/10 p-3 rounded-xl backdrop-blur">
                            <i data-lucide="bar-chart-2" class="w-6 h-6 text-white"></i>
                        </div>
                        <span class="bg-yellow-500 text-slate-900 text-[10px] font-bold px-2 py-1 rounded uppercase">Premium</span>
                    </div>
                    <h3 class="text-xl font-bold mb-2 relative z-10">VIP SEO Content</h3>
                    <p class="text-slate-400 text-sm mb-6 relative z-10">Artikel dan backlink berkualitas untuk mendongkrak ranking video di pencarian.</p>
                    <ul class="space-y-3 mb-8 relative z-10">
                        <li class="flex items-center gap-2 text-sm text-slate-300"><i data-lucide="check-circle" class="w-4 h-4 text-yellow-500"></i> High Retention</li>
                        <li class="flex items-center gap-2 text-sm text-slate-300"><i data-lucide="check-circle" class="w-4 h-4 text-yellow-500"></i> Keyword Targeted</li>
                        <li class="flex items-center gap-2 text-sm text-slate-300"><i data-lucide="check-circle" class="w-4 h-4 text-yellow-500"></i> Konsultasi Gratis</li>
                    </ul>
                    <a href="blog.php" class="block w-full text-center py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-500 transition relative z-10">Pelajari Lebih Lanjut</a>
                </div>

                 <!-- Service Card 3 -->
                 <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 hover:-translate-y-2 transition duration-300">
                    <div class="flex justify-between items-start mb-6">
                        <div class="bg-purple-100 p-3 rounded-xl">
                            <i data-lucide="gem" class="w-6 h-6 text-purple-600"></i>
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800 mb-2">Channel Audit</h3>
                    <p class="text-slate-500 text-sm mb-6">Analisa mendalam kesehatan channel Anda menggunakan AI Intelligence.</p>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i> Metadata Check</li>
                        <li class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i> Competitor Analysis</li>
                        <li class="flex items-center gap-2 text-sm text-slate-600"><i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i> Growth Strategy</li>
                    </ul>
                    <a href="#login" class="block w-full text-center py-3 rounded-xl border-2 border-slate-200 text-slate-600 font-bold hover:border-purple-600 hover:text-purple-600 transition">Coba Gratis</a>
                </div>
            </div>
        </div>
    </section>

    <!-- BLOG SECTION -->
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold mb-2">Artikel & Wawasan</h2>
                    <p class="text-slate-500">Tips terbaru seputar YouTube Growth & Algoritma.</p>
                </div>
                <a href="blog.php" class="text-blue-600 font-bold flex items-center gap-1 hover:gap-2 transition-all">Lihat Blog <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>
            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($latest_posts as $post): ?>
                <article class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl transition group border border-slate-100">
                    <div class="h-48 overflow-hidden bg-slate-200 relative">
                        <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition z-10"></div>
                        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover group-hover:scale-110 transition duration-700">
                    </div>
                    <div class="p-6">
                        <div class="text-xs font-bold text-blue-600 mb-2 uppercase tracking-wide">Tips & Trick</div>
                        <h3 class="text-lg font-bold mb-3 group-hover:text-blue-600 line-clamp-2 leading-snug">
                            <a href="post.php?slug=<?= $post['slug'] ?>"><?= htmlspecialchars($post['title']) ?></a>
                        </h3>
                        <p class="text-slate-500 text-sm line-clamp-3 leading-relaxed"><?= strip_tags(substr($post['content'], 0, 150)) ?>...</p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<!-- PREMIUM FOOTER -->
<footer class="bg-slate-950 text-slate-300 pt-20 pb-10 border-t border-slate-900">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-4 gap-12 mb-16">
            <!-- Brand -->
            <div class="col-span-1 md:col-span-1">
                <a href="index.php" class="flex items-center gap-2 mb-6">
                    <div class="w-8 h-8 gradient-primary rounded-lg flex items-center justify-center">
                        <i data-lucide="activity" class="text-white w-5 h-5"></i>
                    </div>
                    <span class="text-2xl font-bold text-white">Urat<span class="text-blue-600">ID</span></span>
                </a>
                <p class="text-slate-500 text-sm leading-relaxed mb-6">
                    Platform growth hacking YouTube #1 di Indonesia dengan teknologi keamanan mutakhir dan komunitas kreator terbesar.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"><i data-lucide="instagram" class="w-4 h-4"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"><i data-lucide="twitter" class="w-4 h-4"></i></a>
                    <a href="#" class="w-10 h-10 rounded-full bg-slate-900 flex items-center justify-center hover:bg-blue-600 hover:text-white transition"><i data-lucide="youtube" class="w-4 h-4"></i></a>
                </div>
            </div>

            <!-- Links -->
            <div>
                <h4 class="text-white font-bold mb-6">Perusahaan</h4>
                <ul class="space-y-4 text-sm">
                    <li><a href="#" class="hover:text-blue-500 transition">Tentang Kami</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Karir</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Hubungi Kami</a></li>
                    <li><a href="blog.php" class="hover:text-blue-500 transition">Blog Media</a></li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h4 class="text-white font-bold mb-6">Legal & Bantuan</h4>
                <ul class="space-y-4 text-sm">
                    <li><a href="#" class="hover:text-blue-500 transition">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Kebijakan Privasi</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Disclaimer</a></li>
                    <li><a href="#" class="hover:text-blue-500 transition">Pusat Bantuan</a></li>
                </ul>
            </div>

            <!-- Payment -->
            <div>
                <h4 class="text-white font-bold mb-6">Metode Pembayaran</h4>
                <p class="text-xs text-slate-500 mb-4">Transaksi aman dengan enkripsi SSL 256-bit.</p>
                <div class="grid grid-cols-3 gap-3">
                     <div class="bg-white rounded h-8 flex items-center justify-center"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/1200px-BANK_BRI_logo.svg.png" class="h-4"></div>
                     <div class="bg-white rounded h-8 flex items-center justify-center"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/2560px-Bank_Central_Asia.svg.png" class="h-3"></div>
                     <div class="bg-white rounded h-8 flex items-center justify-center text-xs font-bold text-slate-800">QRIS</div>
                     <div class="bg-white rounded h-8 flex items-center justify-center"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Logo_ovo_purple.svg/2560px-Logo_ovo_purple.svg.png" class="h-3"></div>
                     <div class="bg-white rounded h-8 flex items-center justify-center"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/2560px-Logo_dana_blue.svg.png" class="h-3"></div>
                     <div class="bg-white rounded h-8 flex items-center justify-center"><img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" class="h-3"></div>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-slate-900 text-center text-sm text-slate-600">
            <p>&copy; <?= date('Y') ?> PT Urat Digital Indonesia. All rights reserved.</p>
        </div>
    </div>
</footer>

<script>
lucide.createIcons();
</script>
</body>
</html>