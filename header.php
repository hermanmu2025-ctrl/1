<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO & Meta -->
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Urat ID | Premium YouTube & SEO Ecosystem</title>
    <meta name="description" content="<?= isset($meta_desc) ? htmlspecialchars($meta_desc) : 'Urat ID adalah platform premium optimasi YouTube dan SEO nomor 1 di Indonesia. Tingkatkan subscriber, views, dan otoritas digital Anda dengan teknologi AI terbaru.' ?>">
    <meta name="keywords" content="jasa subscriber, youtube seo, optimasi youtube, beli subscriber, urat id, growth hacking youtube">
    <meta name="author" content="Urat ID Team">
    <meta property="og:title" content="Urat ID - Premium YouTube Ecosystem">
    <meta property="og:description" content="Platform komunitas kreator terbesar untuk meningkatkan statistik channel secara organik dan aman.">
    <meta property="og:image" content="https://source.unsplash.com/1200x630/?technology,growth">
    
    <!-- Tech Stack -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-panel { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .gradient-text { background: linear-gradient(135deg, #2563eb, #1d4ed8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-primary { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
        .gradient-dark { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        .hero-pattern { background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 32px 32px; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white overflow-x-hidden">
    
    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:rotate-12 transition-transform duration-300">
                    <i data-lucide="activity" class="text-white w-6 h-6"></i>
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-xl font-extrabold tracking-tight text-slate-800">Urat<span class="text-blue-600">ID</span></span>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Growth Ecosystem</span>
                </div>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="index.php#features" class="hover:text-blue-600 transition duration-300">Fitur</a>
                <a href="index.php#services" class="hover:text-blue-600 transition duration-300">Layanan</a>
                <a href="blog.php" class="hover:text-blue-600 transition duration-300">Blog & SEO</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-4 pl-4 border-l border-slate-200">
                        <a href="dashboard.php" class="flex items-center gap-2 text-blue-700 bg-blue-50 px-5 py-2.5 rounded-full hover:bg-blue-100 transition">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                        </a>
                        <a href="logout.php" class="w-9 h-9 flex items-center justify-center rounded-full border border-slate-200 text-slate-400 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </a>
                    </div>
                <?php elseif(isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin.php" class="text-purple-600 bg-purple-50 px-5 py-2.5 rounded-full hover:bg-purple-100 transition font-bold">
                        <i data-lucide="shield" class="w-4 h-4 inline mr-1"></i> Admin Panel
                    </a>
                    <a href="logout.php" class="text-slate-500 hover:text-red-500 transition">Keluar</a>
                <?php else: ?>
                    <a href="admin_login.php" class="text-slate-400 hover:text-slate-600 text-xs uppercase font-bold tracking-wider">Admin</a>
                    <a href="index.php#login" class="gradient-primary text-white px-7 py-3 rounded-full shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5 transition duration-300">
                        Masuk Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="h-20"></div>