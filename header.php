<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Professional SEO Meta -->
    <title><?= isset($page_title) ? $page_title . ' - ' : '' ?>URAT ID | Ekosistem & Akselerator YouTube No. #1 Indonesia</title>
    <meta name="description" content="<?= isset($meta_desc) ? htmlspecialchars($meta_desc) : 'Platform URAT ID: Solusi SEO YouTube Premium, Tambah Subscriber Organik, Kejar Jam Tayang, dan Monetisasi Cepat dengan Keamanan Tingkat Tinggi.' ?>">
    <meta name="keywords" content="Jasa Subscriber Youtube, SEO Youtube, Jam Tayang, Urat ID, Content Creator Indonesia, Monetisasi Youtube">
    <meta name="author" content="URAT ID Official">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="URAT ID - Premium YouTube Accelerator">
    <meta property="og:description" content="Bergabung dengan komunitas kreator elit. Aman, Cepat, dan Terpercaya.">
    <meta property="og:image" content="assets/urat-id-banner.jpg">
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        },
                        accent: {
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'shine': 'shine 2s infinite',
                        'fade-in-up': 'fadeInUp 0.8s ease-out forwards',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-20px)' },
                        },
                        shine: {
                            '0%': { transform: 'translateX(-100%)' },
                            '100%': { transform: 'translateX(100%)' }
                        },
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        }
                    }
                }
            }
        }
    </script>

    <style>
        body { 
            background-color: #F8FAFC; 
            color: #1E293B; 
            overflow-x: hidden;
        }
        .glass { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(20px); 
            -webkit-backdrop-filter: blur(20px); 
            border: 1px solid rgba(255, 255, 255, 0.5); 
        }
        .glass-nav {
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(10px); 
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        .text-gradient {
            background: linear-gradient(135deg, #1d4ed8 0%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        /* Premium scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 5px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="antialiased flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="glass-nav fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 h-20 flex justify-between items-center">
            <!-- Brand Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="relative">
                    <div class="w-11 h-11 bg-gradient-to-tr from-brand-700 to-brand-500 rounded-xl flex items-center justify-center text-white shadow-lg shadow-brand-500/30 group-hover:rotate-12 transition-transform duration-300">
                        <i data-lucide="activity" class="w-7 h-7"></i>
                    </div>
                    <div class="absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full border-2 border-white animate-pulse"></div>
                </div>
                <div class="flex flex-col">
                    <span class="text-2xl font-extrabold tracking-tighter text-slate-900 leading-none">URAT <span class="text-brand-600">ID</span></span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] leading-tight">Premium Growth</span>
                </div>
            </a>

            <!-- Desktop Links -->
            <div class="hidden lg:flex items-center gap-6 font-semibold text-sm text-slate-600">
                <a href="index.php#about" class="hover:text-brand-600 transition">Tentang</a>
                <a href="index.php#services" class="hover:text-brand-600 transition">Layanan</a>
                <a href="index.php#features" class="hover:text-brand-600 transition">Keunggulan</a>
                <a href="index.php#how-it-works" class="hover:text-brand-600 transition">Cara Kerja</a>
                <a href="blog.php" class="hover:text-brand-600 transition">Blog</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="pl-6 border-l border-slate-200 flex items-center gap-4">
                        <a href="deposit.php" class="text-slate-600 hover:text-green-600 font-bold flex items-center gap-1">
                            <i data-lucide="wallet" class="w-4 h-4"></i> Top Up
                        </a>
                        <a href="dashboard.php" class="bg-slate-900 text-white px-6 py-2.5 rounded-full font-bold shadow-lg shadow-slate-900/20 hover:bg-slate-800 transition flex items-center gap-2">
                            <i data-lucide="layout-dashboard" class="w-4 h-4"></i> Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <a href="index.php#auth" class="bg-gradient-to-r from-brand-600 to-brand-700 text-white px-8 py-3 rounded-full font-bold shadow-xl shadow-brand-500/30 hover:shadow-2xl hover:-translate-y-1 transition flex items-center gap-2">
                        <i data-lucide="zap" class="w-4 h-4 fill-current"></i> Mulai Gratis
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Toggle -->
            <button class="lg:hidden text-slate-700 hover:text-brand-600 transition">
                <i data-lucide="menu-square" class="w-9 h-9"></i>
            </button>
        </div>
    </nav>
    <div class="h-20"></div>