<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>Urat ID | Premium Growth</title>
    <meta name="description" content="<?= isset($meta_desc) ? htmlspecialchars($meta_desc) : 'Platform optimasi YouTube dan SEO premium nomor 1 di Indonesia.' ?>">
    
    <!-- Tech Stack -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8fafc; color: #1e293b; }
        .glass-panel { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(16px); border: 1px solid rgba(255,255,255,0.5); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .glass-nav { background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(20px); border-bottom: 1px solid rgba(226, 232, 240, 0.8); }
        .gradient-text { background: linear-gradient(135deg, #2563eb, #4f46e5); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .gradient-primary { background: linear-gradient(135deg, #2563eb 0%, #4338ca 100%); }
    </style>
</head>
<body class="antialiased min-h-screen flex flex-col selection:bg-blue-500 selection:text-white overflow-x-hidden">
    
    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <a href="index.php" class="flex items-center gap-2.5 group">
                <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center shadow-lg group-hover:scale-105 transition-transform duration-300">
                    <i data-lucide="zap" class="text-white w-5 h-5"></i>
                </div>
                <span class="text-2xl font-bold tracking-tight text-slate-800">Urat<span class="text-blue-600">ID</span></span>
            </a>

            <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-500">
                <a href="index.php#services" class="hover:text-blue-600 transition">Layanan</a>
                <a href="blog.php" class="hover:text-blue-600 transition">Blog</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="text-blue-600 bg-blue-50 px-4 py-2 rounded-lg hover:bg-blue-100 transition">Dashboard</a>
                    <a href="logout.php" class="text-slate-500 hover:text-red-500 transition">Keluar</a>
                <?php elseif(isset($_SESSION['admin_logged_in'])): ?>
                    <a href="admin.php" class="text-purple-600 bg-purple-50 px-4 py-2 rounded-lg hover:bg-purple-100 transition">Admin Panel</a>
                    <a href="logout.php" class="text-slate-500 hover:text-red-500 transition">Keluar</a>
                <?php else: ?>
                    <a href="admin_login.php" class="text-slate-400 hover:text-slate-600">Admin</a>
                    <a href="index.php#login" class="gradient-primary text-white px-6 py-2.5 rounded-full shadow-lg hover:shadow-blue-600/30 transition">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <div class="h-20"></div>