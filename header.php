<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Professional SEO Meta -->
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Urat ID | Ekosistem Kreator YouTube Premium</title>
    <meta name="description" content="<?= isset($meta_desc) ? htmlspecialchars($meta_desc) : 'Platform akselerasi channel YouTube #1 di Indonesia. Solusi SEO, subscriber organik, dan komunitas kreator profesional.' ?>">
    <meta name="author" content="Urat ID Official">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; color: #1E293B; }
        .glass-nav { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .glass-card { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.5); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05); }
        .text-gradient { background: linear-gradient(135deg, #2563EB 0%, #0F172A 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-primary { background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%); transition: all 0.3s ease; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -10px rgba(37, 99, 235, 0.5); }
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <!-- Brand -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:rotate-6 transition">
                    <i data-lucide="zap" class="w-6 h-6"></i>
                </div>
                <div>
                    <span class="text-xl font-extrabold tracking-tight text-slate-900">Urat<span class="text-blue-600">ID</span></span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest leading-none">Growth Ecosystem</p>
                </div>
            </a>

            <!-- Desktop Links -->
            <div class="hidden lg:flex items-center gap-8 font-semibold text-sm text-slate-600">
                <a href="index.php#features" class="hover:text-blue-600 transition">Fitur</a>
                <a href="index.php#services" class="hover:text-blue-600 transition">Layanan</a>
                <a href="blog.php" class="hover:text-blue-600 transition">Blog & SEO</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="pl-6 border-l border-slate-200">
                        <a href="dashboard.php" class="btn-primary text-white px-6 py-2.5 rounded-full font-bold shadow-lg flex items-center gap-2">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <a href="index.php#auth" class="btn-primary text-white px-7 py-3 rounded-full font-bold shadow-lg">
                        Mulai Gratis
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu -->
            <button class="lg:hidden text-slate-700 hover:text-blue-600">
                <i data-lucide="menu" class="w-8 h-8"></i>
            </button>
        </div>
    </nav>
    <div class="h-20"></div>