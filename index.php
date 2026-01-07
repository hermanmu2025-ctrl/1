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
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE channel_id = ?");
        $stmt->execute([$channel_id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("INSERT INTO users (channel_id, channel_name, avatar_url, balance) VALUES (?, ?, ?, ?)");
                $stmt->execute([$channel_id, $channel_name, $avatar, WELCOME_BONUS]);
                $newId = $pdo->lastInsertId();
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
$page_title = "Akselerator Youtube Premium";
include 'header.php';
?>

<main class="overflow-hidden">
    <!-- HERO SECTION -->
    <section class="relative pt-20 pb-32 lg:pt-32 overflow-hidden">
        <!-- Decorations -->
        <div class="absolute top-0 right-0 w-2/3 h-full bg-brand-50/50 -z-10 rounded-bl-[200px]"></div>
        <div class="absolute top-20 left-10 w-20 h-20 bg-yellow-400/20 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-10 right-10 w-32 h-32 bg-purple-500/20 rounded-full blur-3xl"></div>
        <div class="absolute top-40 right-[20%] w-40 h-40 bg-brand-500/10 rounded-full blur-3xl"></div>

        <div class="container mx-auto px-6 grid lg:grid-cols-2 gap-16 items-center relative z-10">
            <!-- Left Content -->
            <div class="space-y-8 animate-fade-in-up">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-blue-100 rounded-full shadow-sm hover:shadow-md transition cursor-default">
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-brand-500"></span>
                    </span>
                    <span class="text-sm font-bold text-slate-600 tracking-wide uppercase">Platform Monetisasi #1 Indonesia</span>
                </div>
                
                <h1 class="text-5xl lg:text-7xl font-extrabold text-slate-900 leading-[1.1] tracking-tight">
                    Bangun Otoritas <br>
                    <span class="text-gradient">YouTube</span> Anda.
                </h1>
                
                <p class="text-lg text-slate-500 leading-relaxed max-w-xl font-medium">
                    <strong>URAT ID</strong> adalah ekosistem pertumbuhan digital premium untuk Content Creator. Raih syarat monetisasi (1000 Subs & 4000 Jam Tayang) dengan metode <strong>Organik</strong>, <strong>Aman</strong>, dan <strong>Cepat</strong>.
                </p>

                <div class="flex flex-col sm:flex-row gap-4 pt-4">
                    <a href="#auth" class="btn-primary bg-brand-600 hover:bg-brand-700 text-white px-8 py-4 rounded-xl font-bold shadow-xl shadow-brand-500/30 flex items-center justify-center gap-3 transition hover:-translate-y-1 group">
                        Dapatkan Bonus Poin <i data-lucide="gift" class="w-5 h-5 group-hover:rotate-12 transition"></i>
                    </a>
                    <a href="#how-it-works" class="px-8 py-4 rounded-xl font-bold text-slate-600 hover:bg-slate-100 transition flex items-center justify-center gap-2">
                        <i data-lucide="play-circle" class="w-5 h-5"></i> Pelajari Cara Kerja
                    </a>
                </div>
            </div>

            <!-- Right Form (Login Card) -->
            <div class="relative" id="auth">
                <div class="absolute -inset-1 bg-gradient-to-r from-brand-600 to-purple-600 rounded-[2rem] blur opacity-30 animate-pulse"></div>
                <div class="bg-white p-8 md:p-10 rounded-[2rem] shadow-2xl relative border border-slate-100">
                    <div class="text-center mb-8">
                        <div class="w-20 h-20 bg-brand-50 rounded-2xl flex items-center justify-center mx-auto mb-6 text-brand-600 shadow-inner ring-4 ring-brand-50">
                            <i data-lucide="rocket" class="w-10 h-10"></i>
                        </div>
                        <h2 class="text-3xl font-bold text-slate-900">Mulai Gratis</h2>
                        <p class="text-slate-500 mt-2 font-medium">Tidak perlu password. Aman & Instan.</p>
                    </div>

                    <?php if($error): ?>
                        <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-sm font-bold flex items-center gap-2 animate-pulse">
                            <i data-lucide="alert-triangle" class="w-5 h-5"></i> <?= $error ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label class="text-xs font-extrabold text-slate-400 uppercase tracking-widest ml-1">YouTube Channel ID</label>
                            <div class="relative group">
                                <i data-lucide="youtube" class="absolute left-4 top-4 w-5 h-5 text-slate-400 group-focus-within:text-red-500 transition"></i>
                                <input type="text" name="channel_id" required placeholder="Contoh: UC_x5XG1..." 
                                       class="w-full pl-12 pr-5 py-4 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none transition font-semibold text-slate-800 placeholder-slate-400">
                            </div>
                        </div>
                        <button type="submit" class="w-full bg-slate-900 text-white font-bold py-4 rounded-xl shadow-lg hover:bg-slate-800 transition transform hover:scale-[1.02] flex justify-center items-center gap-3">
                            <i data-lucide="unlock" class="w-5 h-5"></i> Masuk Dashboard
                        </button>
                    </form>
                    
                    <div class="mt-8 pt-6 border-t border-slate-100 flex justify-between items-center text-sm">
                        <span class="text-slate-500 font-medium">Bonus Pendaftaran</span>
                        <span class="flex items-center gap-1 text-green-600 font-bold bg-green-50 px-3 py-1 rounded-full">
                            <i data-lucide="check-circle" class="w-4 h-4"></i> <?= formatRupiah(WELCOME_BONUS) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TENTANG URAT ID (ABOUT) -->
    <section id="about" class="py-24 bg-white relative overflow-hidden">
        <div class="container mx-auto px-6 relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-16">
                <div class="md:w-1/2">
                    <div class="relative rounded-[2.5rem] overflow-hidden shadow-2xl border-4 border-white transform rotate-3 hover:rotate-0 transition duration-500">
                        <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?q=80&w=1000&auto=format&fit=crop" alt="Tentang Kami" class="w-full">
                        <div class="absolute inset-0 bg-brand-900/20"></div>
                    </div>
                </div>
                <div class="md:w-1/2">
                    <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Tentang URAT ID</span>
                    <h2 class="text-4xl font-extrabold text-slate-900 mt-3 mb-6">Komunitas Gotong Royong Digital Terbesar.</h2>
                    <p class="text-slate-500 text-lg leading-relaxed mb-6">
                        URAT ID lahir dari keresahan konten kreator pemula yang sulit menembus algoritma YouTube. Kami menciptakan platform berbasis <strong>Mutualisme</strong> dimana setiap anggota saling membantu untuk mencapai target monetisasi.
                    </p>
                    <p class="text-slate-500 text-lg leading-relaxed mb-8">
                        Berbeda dengan panel SMM biasa yang menggunakan bot, URAT ID menjamin interaksi berasal dari <strong>Real Human (Manusia Asli)</strong> yang merupakan sesama kreator Indonesia.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <h4 class="text-2xl font-bold text-brand-600">10K+</h4>
                            <p class="text-sm text-slate-500 font-medium">Active Creators</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <h4 class="text-2xl font-bold text-green-600">100%</h4>
                            <p class="text-sm text-slate-500 font-medium">Success Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- TENTANG LAYANAN (SERVICES) -->
    <section id="services" class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
             <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Layanan Premium</span>
                <h2 class="text-4xl font-extrabold text-slate-900 mt-3">Solusi All-In-One Kreator</h2>
                <p class="text-slate-500 mt-4 text-lg">Fokus membuat konten, biarkan kami yang mengurus trafik dan statistik channel Anda.</p>
            </div>

            <div class="grid md:grid-cols-4 gap-6">
                <!-- Service 1 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-slate-100 group">
                    <div class="w-14 h-14 bg-red-100 text-red-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i data-lucide="youtube" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Subscriber Organik</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Dapatkan subscriber permanen dari pengguna aktif Indonesia. Garansi anti-drop dengan sistem proteksi AI.</p>
                </div>
                <!-- Service 2 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-slate-100 group">
                    <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i data-lucide="clock" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">4000 Jam Tayang</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Percepat syarat monetisasi dengan view durasi panjang yang aman dan terdeteksi valid oleh YouTube Studio.</p>
                </div>
                <!-- Service 3 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-slate-100 group">
                    <div class="w-14 h-14 bg-purple-100 text-purple-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i data-lucide="search" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Optimasi SEO</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Tingkatkan ranking video di pencarian YouTube dengan optimasi keyword dan interaksi sinyal sosial.</p>
                </div>
                <!-- Service 4 -->
                <div class="bg-white p-8 rounded-3xl shadow-sm hover:shadow-xl transition duration-300 border border-slate-100 group">
                    <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition">
                        <i data-lucide="dollar-sign" class="w-7 h-7"></i>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Konsultasi AdSense</h3>
                    <p class="text-slate-500 text-sm leading-relaxed">Bimbingan eksklusif cara mendaftar dan menjaga keamanan akun AdSense agar tidak terkena dismonetisasi.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KEUNGGULAN & KEISTIMEWAAN (FEATURES) -->
    <section id="features" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-16">
                <!-- Left: Text -->
                <div class="md:w-1/2 space-y-8">
                     <div>
                        <span class="text-brand-600 font-bold uppercase tracking-widest text-sm">Keunggulan & Keistimewaan</span>
                        <h2 class="text-4xl font-extrabold text-slate-900 mt-3">Mengapa Ribuan Kreator Percaya URAT ID?</h2>
                     </div>
                     
                     <div class="flex gap-5">
                         <div class="flex-shrink-0 w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center">
                             <i data-lucide="shield-check" class="w-6 h-6"></i>
                         </div>
                         <div>
                             <h4 class="text-xl font-bold text-slate-900">Keamanan Privasi Mutlak</h4>
                             <p class="text-slate-500 mt-2">Satu-satunya platform yang <strong>TIDAK MEMINTA</strong> password email/akun Google Anda. Login hanya menggunakan Channel ID publik.</p>
                         </div>
                     </div>

                     <div class="flex gap-5">
                         <div class="flex-shrink-0 w-12 h-12 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center">
                             <i data-lucide="cpu" class="w-6 h-6"></i>
                         </div>
                         <div>
                             <h4 class="text-xl font-bold text-slate-900">AI Protection System</h4>
                             <p class="text-slate-500 mt-2">Bot cerdas kami memantau aktivitas 24/7. Member yang melakukan <em>unsubscribe</em> akan terdeteksi dan didenda saldo otomatis.</p>
                         </div>
                     </div>

                     <div class="flex gap-5">
                         <div class="flex-shrink-0 w-12 h-12 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
                             <i data-lucide="users" class="w-6 h-6"></i>
                         </div>
                         <div>
                             <h4 class="text-xl font-bold text-slate-900">Komunitas Organik</h4>
                             <p class="text-slate-500 mt-2">Basis pengguna kami adalah manusia asli, bukan bot server. Ini menciptakan engagement yang sehat di mata algoritma YouTube.</p>
                         </div>
                     </div>
                </div>

                <!-- Right: Visual -->
                <div class="md:w-1/2 relative">
                     <div class="absolute inset-0 bg-gradient-to-tr from-brand-600 to-purple-600 rounded-[3rem] rotate-6 opacity-20 blur-xl"></div>
                     <div class="relative bg-slate-900 rounded-[3rem] p-10 text-white shadow-2xl overflow-hidden border border-slate-700">
                         <div class="absolute top-0 right-0 p-10 opacity-10">
                             <i data-lucide="fingerprint" class="w-40 h-40"></i>
                         </div>
                         <h3 class="text-2xl font-bold mb-6">Statistik Real-Time</h3>
                         <div class="space-y-6">
                             <div class="flex justify-between items-center border-b border-white/10 pb-4">
                                 <span class="text-slate-400">Total Transaksi</span>
                                 <span class="text-2xl font-mono font-bold text-green-400">RP 1.2M+</span>
                             </div>
                             <div class="flex justify-between items-center border-b border-white/10 pb-4">
                                 <span class="text-slate-400">Kampanye Sukses</span>
                                 <span class="text-2xl font-mono font-bold text-brand-400">15,420</span>
                             </div>
                             <div class="flex justify-between items-center">
                                 <span class="text-slate-400">Total Subscriptions</span>
                                 <span class="text-2xl font-mono font-bold text-yellow-400">850,000+</span>
                             </div>
                         </div>
                         <div class="mt-10 bg-white/10 rounded-xl p-4 flex items-center gap-3 backdrop-blur-sm">
                             <i data-lucide="lock" class="w-5 h-5 text-green-400"></i>
                             <span class="text-sm font-bold">Enkripsi SSL 256-bit Terverifikasi</span>
                         </div>
                     </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HARAPAN & VISI -->
    <section class="py-20 bg-brand-900 text-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full opacity-10">
            <svg class="h-full w-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M0 100 C 20 0 50 0 100 100 Z" fill="white" />
            </svg>
        </div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <div class="w-16 h-16 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-8 border border-white/20">
                <i data-lucide="heart" class="w-8 h-8 text-red-500 fill-current"></i>
            </div>
            <h2 class="text-4xl md:text-5xl font-extrabold mb-6">Harapan Kami</h2>
            <p class="text-xl md:text-2xl text-brand-100 max-w-4xl mx-auto leading-relaxed font-light">
                "Mewujudkan ekosistem digital Indonesia yang inklusif, di mana setiap anak bangsa memiliki kesempatan yang sama untuk berkarya, didengar, dan mandiri secara finansial melalui platform YouTube tanpa terhalang oleh rumitnya algoritma awal."
            </p>
            <div class="mt-10">
                <span class="text-sm font-bold tracking-widest uppercase opacity-70">— Tim URAT ID —</span>
            </div>
        </div>
    </section>

    <!-- PROMOSI & BONUS -->
    <section class="py-24 bg-white relative overflow-hidden">
         <div class="container mx-auto px-6">
             <div class="bg-gradient-to-r from-yellow-500 to-orange-600 rounded-[3rem] p-10 md:p-16 text-white shadow-2xl relative overflow-hidden">
                 <div class="absolute top-0 right-0 w-96 h-96 bg-white opacity-10 rounded-full blur-3xl -mr-20 -mt-20">
                 </div>
                 <div class="grid md:grid-cols-2 gap-10 items-center relative z-10">
                     <div>
                         <div class="inline-block bg-black/20 px-4 py-1 rounded-full text-xs font-bold uppercase tracking-wider mb-4 border border-white/20">
                             Promo Terbatas
                         </div>
                         <h2 class="text-4xl md:text-5xl font-extrabold mb-6 leading-tight">Daftar Hari Ini,<br>Klaim Bonus Spesial!</h2>
                         <ul class="space-y-4 mb-8">
                             <li class="flex items-center gap-3 text-lg font-medium">
                                 <i data-lucide="check-circle" class="w-6 h-6 text-yellow-200"></i>
                                 Gratis Saldo <?= formatRupiah(WELCOME_BONUS) ?>
                             </li>
                             <li class="flex items-center gap-3 text-lg font-medium">
                                 <i data-lucide="check-circle" class="w-6 h-6 text-yellow-200"></i>
                                 Akses Fitur Premium Trial
                             </li>
                             <li class="flex items-center gap-3 text-lg font-medium">
                                 <i data-lucide="check-circle" class="w-6 h-6 text-yellow-200"></i>
                                 E-Book Panduan SEO 2024
                             </li>
                         </ul>
                         <a href="#auth" class="inline-flex items-center gap-3 bg-white text-orange-600 px-8 py-4 rounded-xl font-bold hover:bg-orange-50 transition shadow-xl">
                             Ambil Bonus Sekarang <i data-lucide="arrow-right" class="w-5 h-5"></i>
                         </a>
                     </div>
                     <div class="hidden md:flex justify-center">
                         <div class="relative">
                             <div class="absolute inset-0 bg-white rounded-full blur-2xl opacity-30 animate-pulse"></div>
                             <i data-lucide="gift" class="w-64 h-64 text-white drop-shadow-2xl animate-float"></i>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
    </section>

    <!-- CARA KERJA (HOW IT WORKS) -->
    <section id="how-it-works" class="py-24 bg-slate-900 text-white overflow-hidden relative">
        <!-- BG Effects -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden">
             <div class="absolute top-[-10%] right-[-10%] w-[500px] h-[500px] bg-brand-600/20 rounded-full blur-[100px]"></div>
             <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full blur-[100px]"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-brand-400 font-bold uppercase tracking-widest text-sm">Workflow</span>
                <h2 class="text-4xl font-extrabold mb-6">Cara Kerja Sistem</h2>
                <p class="text-slate-400 text-lg">Hanya butuh 3 langkah sederhana untuk mulai mengembangkan channel Anda.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white/5 border border-white/10 p-8 rounded-3xl hover:bg-white/10 transition group">
                    <div class="w-14 h-14 bg-brand-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mb-6 shadow-lg shadow-brand-600/30 group-hover:scale-110 transition">1</div>
                    <h4 class="text-xl font-bold mb-4">Registrasi Simple</h4>
                    <p class="text-slate-400 leading-relaxed">Masukkan Channel ID YouTube Anda. Sistem akan otomatis memverifikasi channel tanpa password.</p>
                </div>
                <div class="bg-white/5 border border-white/10 p-8 rounded-3xl hover:bg-white/10 transition group">
                    <div class="w-14 h-14 bg-purple-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mb-6 shadow-lg shadow-purple-600/30 group-hover:scale-110 transition">2</div>
                    <h4 class="text-xl font-bold mb-4">Kumpulkan Poin</h4>
                    <p class="text-slate-400 leading-relaxed">Subscribe channel member lain untuk mendapatkan saldo. Semakin banyak interaksi, semakin banyak saldo.</p>
                </div>
                <div class="bg-white/5 border border-white/10 p-8 rounded-3xl hover:bg-white/10 transition group">
                    <div class="w-14 h-14 bg-green-600 rounded-2xl flex items-center justify-center text-white font-bold text-2xl mb-6 shadow-lg shadow-green-600/30 group-hover:scale-110 transition">3</div>
                    <h4 class="text-xl font-bold mb-4">Promosikan Channel</h4>
                    <p class="text-slate-400 leading-relaxed">Gunakan saldo yang terkumpul untuk membuat kampanye. Channel Anda akan disubscribe oleh member lain.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- KEAMANAN (SECURITY) -->
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6 text-center">
            <div class="inline-block p-4 rounded-full bg-green-50 text-green-600 mb-6">
                <i data-lucide="shield-check" class="w-8 h-8"></i>
            </div>
            <h2 class="text-4xl font-extrabold text-slate-900 mb-6">Keamanan Tingkat Bank</h2>
            <p class="text-slate-500 max-w-2xl mx-auto mb-12 text-lg">
                Kami menggunakan protokol keamanan terkini untuk memastikan data dan channel Anda tetap aman 100%.
            </p>
        </div>
    </section>

    <!-- LATEST BLOG -->
    <section class="py-24 bg-slate-50">
        <div class="container mx-auto px-6">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <span class="text-brand-600 font-bold uppercase text-xs tracking-wider">URAT ID News</span>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">Wawasan Terbaru Creator</h2>
                </div>
                <a href="blog.php" class="hidden md:flex items-center gap-2 text-slate-600 hover:text-brand-600 font-bold transition">Lihat Semua Artikel <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <?php foreach($posts as $post): ?>
                <article class="bg-white rounded-[2rem] overflow-hidden shadow-lg shadow-slate-200/50 hover:-translate-y-2 transition duration-300 flex flex-col h-full">
                    <div class="h-52 overflow-hidden relative">
                        <img src="<?= htmlspecialchars($post['thumbnail']) ?>" class="w-full h-full object-cover transform hover:scale-110 transition duration-700">
                        <div class="absolute top-4 left-4 bg-white/90 backdrop-blur px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide text-brand-600">SEO</div>
                    </div>
                    <div class="p-8 flex-1 flex flex-col">
                        <h3 class="text-lg font-bold text-slate-900 mb-3 line-clamp-2 leading-snug">
                            <a href="post.php?slug=<?= $post['slug'] ?>" class="hover:text-brand-600 transition"><?= htmlspecialchars($post['title']) ?></a>
                        </h3>
                        <p class="text-slate-500 text-sm mb-6 line-clamp-3 leading-relaxed flex-1">
                            <?= isset($post['meta_desc']) ? $post['meta_desc'] : strip_tags(substr($post['content'], 0, 100)).'...' ?>
                        </p>
                        <div class="flex items-center justify-between border-t border-slate-100 pt-4">
                            <span class="text-xs font-bold text-slate-400"><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                            <a href="post.php?slug=<?= $post['slug'] ?>" class="text-sm font-bold text-brand-600 flex items-center gap-1">Baca <i data-lucide="chevron-right" class="w-4 h-4"></i></a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</main>

<!-- FOOTER PREMIUM IMPROVED -->
<footer class="bg-slate-900 text-white pt-24 pb-12 border-t border-slate-800 relative overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand-600/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-purple-600/20 rounded-full blur-3xl"></div>
    </div>

    <div class="container mx-auto px-6 relative z-10 text-center">
        <!-- Premium Brand -->
        <div class="mb-8 animate-float">
             <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-tr from-brand-600 to-brand-400 rounded-3xl shadow-2xl shadow-brand-500/30 mb-6">
                 <i data-lucide="activity" class="w-10 h-10 text-white"></i>
             </div>
             <h2 class="text-5xl md:text-6xl font-extrabold tracking-tighter mb-4">
                URAT <span class="text-transparent bg-clip-text bg-gradient-to-r from-brand-400 to-purple-400">ID</span>
             </h2>
             <p class="text-sm font-bold text-brand-400 uppercase tracking-[0.3em]">Premium Digital Ecosystem</p>
        </div>

        <!-- Description -->
        <p class="text-slate-400 text-lg md:text-xl max-w-2xl mx-auto leading-relaxed mb-12 font-light">
            Platform akselerasi YouTube #1 di Indonesia. Kami membantu kreator mencapai potensi monetisasi maksimal dengan strategi organik, aman, dan terukur.
        </p>

        <!-- Divider -->
        <div class="w-24 h-1 bg-gradient-to-r from-transparent via-slate-700 to-transparent mx-auto mb-12"></div>

        <!-- Copyright -->
        <div class="text-slate-500 text-sm font-medium">
            &copy; <?= date('Y') ?> URAT DIGITAL INDONESIA. All rights reserved.
        </div>
    </div>
</footer>

<script>lucide.createIcons();</script>
</body>
</html>