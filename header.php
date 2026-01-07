<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Urat ID | Premium Growth</title>
    <meta name="description" content="Platform optimasi YouTube dan SEO premium nomor 1 di Indonesia.">
    
    <!-- Tech Stack -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        
        /* Glassmorphism Light */
        .glass-panel { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03); }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        
        .gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-primary { background: linear-gradient(135deg, #2563eb 0%, #4338ca 100%); }
        .shadow-glow { box-shadow: 0 10px 40px -10px rgba(37, 99, 235, 0.4); }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-500 selection:text-white overflow-x-hidden">
    
    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <!-- Brand -->
            <a href="index.php" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
                    <i data-lucide="zap" class="text-white w-5 h-5"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-slate-800">Urat<span class="text-blue-600">ID</span></span>
            </a>

            <!-- Navigation -->
            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-500">
                <a href="index.php#services" class="hover:text-blue-600 transition">Layanan</a>
                <a href="index.php#features" class="hover:text-blue-600 transition">Keunggulan</a>
                <a href="blog.php" class="hover:text-blue-600 transition">Blog</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="text-blue-600 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition">Dashboard</a>
                    <a href="logout.php" class="text-slate-500 hover:text-red-500 transition">Keluar</a>
                <?php else: ?>
                    <a href="#login" class="gradient-primary text-white px-6 py-2.5 rounded-full shadow-lg shadow-blue-500/25 hover:shadow-blue-600/30 transition transform hover:-translate-y-0.5">Masuk Sekarang</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="h-20"></div>