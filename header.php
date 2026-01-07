<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Premium SEO & Meta -->
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Urat ID | #1 Jasa Subscriber & SEO Premium Indonesia</title>
    <meta name="description" content="<?= isset($meta_desc) ? htmlspecialchars($meta_desc) : 'Urat ID adalah platform premium optimasi YouTube dan SEO nomor 1 di Indonesia. Layanan subscriber permanen, views organik, dan tools SEO berbasis AI.' ?>">
    <meta name="keywords" content="jasa subscriber aman, beli subscriber youtube, seo youtube 2024, urat id, cara monetisasi youtube, subscriber gratis, panel sosmed premium">
    <meta name="author" content="Urat ID Professional Team">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Urat ID - Ecosystem Creator Premium">
    <meta property="og:description" content="Tingkatkan performa channel YouTube Anda dengan teknologi Urat ID. Aman, Cepat, dan Bergaransi.">
    <meta property="og:image" content="assets/og-image.jpg">
    <meta property="og:url" content="https://urat.id">
    
    <!-- Favicon (Penting untuk Icon di Search Engine) -->
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/1384/1384060.png" type="image/png">

    <!-- Tech Stack -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Custom Styles -->
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: #f8fafc; 
            color: #1e293b; 
            overflow-x: hidden;
        }
        
        /* Glassmorphism */
        .glass-nav { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px); 
            border-bottom: 1px solid rgba(255,255,255,0.3); 
        }
        .glass-panel { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.5); 
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05); 
        }
        .glass-card {
            background: linear-gradient(145deg, #ffffff, #f3f4f6);
            box-shadow: 20px 20px 60px #d1d5db, -20px -20px 60px #ffffff;
        }

        /* Gradients */
        .gradient-text { background: linear-gradient(135deg, #2563eb 0%, #0891b2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-primary { background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%); }
        .gradient-gold { background: linear-gradient(135deg, #ca8a04 0%, #eab308 100%); }
        .gradient-dark { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); }
        
        /* Animations */
        @keyframes float { 0% { transform: translateY(0px); } 50% { transform: translateY(-10px); } 100% { transform: translateY(0px); } }
        .animate-float { animation: float 6s ease-in-out infinite; }
        
        .hero-bg { 
            background-image: radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
        }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-600 selection:text-white">
    
    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30 group-hover:rotate-12 transition-transform duration-300">
                    <i data-lucide="zap" class="text-white w-6 h-6"></i>
                </div>
                <div class="flex flex-col leading-tight">
                    <span class="text-xl font-extrabold tracking-tight text-slate-800">Urat<span class="text-blue-600">ID</span></span>
                    <span class="text-[10px] uppercase font-bold text-slate-400 tracking-widest">Premium SEO</span>
                </div>
            </a>

            <!-- Desktop Menu -->
            <div class="hidden lg:flex items-center gap-8 text-sm font-semibold text-slate-600">
                <a href="index.php#about" class="hover:text-blue-600 transition">Tentang Kami</a>
                <a href="index.php#services" class="hover:text-blue-600 transition">Layanan</a>
                <a href="index.php#features" class="hover:text-blue-600 transition">Keunggulan</a>
                <a href="blog.php" class="hover:text-blue-600 transition">Blog & Tips</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="flex items-center gap-4 pl-4 border-l border-slate-200">
                        <a href="dashboard.php" class="flex items-center gap-2 text-white gradient-primary px-6 py-2.5 rounded-full shadow-lg shadow-blue-500/30 hover:shadow-blue-500/50 hover:-translate-y-0.5 transition">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                        </a>
                    </div>
                <?php elseif(isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin.php" class="text-white gradient-dark px-6 py-2.5 rounded-full shadow-lg hover:shadow-xl transition">
                        <i data-lucide="shield" class="w-4 h-4 inline mr-1"></i> Admin Area
                    </a>
                <?php else: ?>
                    <div class="flex items-center gap-4">
                        <a href="index.php#login" class="text-slate-600 hover:text-blue-600 font-bold transition">Masuk</a>
                        <a href="index.php#login" class="gradient-primary text-white px-7 py-3 rounded-full shadow-lg shadow-blue-600/30 hover:shadow-blue-600/50 hover:-translate-y-0.5 transition duration-300 font-bold">
                            Daftar Gratis
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button class="lg:hidden text-slate-700 hover:text-blue-600 transition">
                <i data-lucide="menu" class="w-8 h-8"></i>
            </button>
        </div>
    </nav>
    <div class="h-20"></div>