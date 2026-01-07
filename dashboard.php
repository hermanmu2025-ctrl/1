<?php
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

$user = getUser($_SESSION['user_id']);

// Active Promo
$promo = $pdo->query("SELECT * FROM promos WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch();

// Campaigns (Logic: Not Self, Balance >= Min)
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? AND balance >= ? ORDER BY RAND() LIMIT 8");
$stmt->execute([$user['id'], MIN_CAMPAIGN_BALANCE]);
$campaigns = $stmt->fetchAll();

// Transaction History
$histStmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$histStmt->execute([$user['id']]);
$history = $histStmt->fetchAll();

$page_title = "Dashboard User";
include 'header.php';
?>

<div class="container mx-auto p-6 max-w-7xl py-12">
    
    <!-- Welcome Banner -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-10 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-800 tracking-tight">Halo, <?= htmlspecialchars($user['channel_name']) ?>! 👋</h1>
            <p class="text-slate-500 font-medium">Selamat datang di pusat kendali channel Anda.</p>
        </div>
        <div class="flex gap-3">
            <a href="deposit.php" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg shadow-blue-600/20 hover:bg-blue-700 transition flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4"></i> Isi Saldo
            </a>
        </div>
    </div>

    <?php if($promo): ?>
    <div class="bg-gradient-to-r from-purple-600 to-indigo-600 p-6 rounded-2xl shadow-xl shadow-purple-200 text-white mb-10 flex items-start gap-5 relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
        <div class="bg-white/20 p-3 rounded-xl backdrop-blur">
            <i data-lucide="megaphone" class="w-6 h-6 text-white"></i>
        </div>
        <div class="relative z-10">
            <h3 class="font-bold text-lg mb-1">Pengumuman Sistem</h3>
            <p class="opacity-90 leading-relaxed text-sm"><?= htmlspecialchars($promo['content']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- LEFT SIDE: Stats & Personal -->
        <div class="space-y-8">
            <!-- Stats Card -->
            <div class="glass-panel p-8 rounded-3xl relative overflow-hidden">
                 <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-10 -mt-10 z-0"></div>
                 
                <div class="flex items-center gap-4 mb-8 relative z-10">
                    <div class="p-1 bg-white rounded-full shadow-md">
                        <img src="<?= $user['avatar_url'] ?>" class="w-16 h-16 rounded-full border-2 border-slate-100">
                    </div>
                    <div>
                        <h2 class="font-bold text-lg truncate w-40 text-slate-800"><?= htmlspecialchars($user['channel_name']) ?></h2>
                        <div class="flex items-center gap-1 text-[10px] text-slate-400 font-mono bg-slate-100 px-2 py-1 rounded w-fit">
                             ID: <?= substr($user['channel_id'], 0, 10) ?>...
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-center relative z-10">
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 group hover:border-blue-200 transition">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-2">Saldo</p>
                        <p class="font-extrabold text-blue-600 text-lg group-hover:scale-105 transition"><?= formatRupiah($user['balance']) ?></p>
                    </div>
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-100 group hover:border-purple-200 transition">
                        <p class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-2">Total Subs</p>
                        <p class="font-extrabold text-purple-600 text-lg group-hover:scale-105 transition">+<?= $user['total_subs_gained'] ?></p>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="glass-panel p-8 rounded-3xl">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-slate-400"></i> Mutasi Terakhir
                </h3>
                <div class="space-y-6">
                    <?php foreach($history as $h): ?>
                    <div class="flex justify-between items-center relative">
                         <!-- Connecting Line (Visual) -->
                         <div class="absolute left-[7px] top-8 bottom-[-20px] w-px bg-slate-100 -z-10"></div>
                         
                        <div class="flex gap-3">
                            <div class="w-4 h-4 rounded-full mt-1 shrink-0 <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? 'bg-green-500' : 'bg-red-400' ?>"></div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 capitalize"><?= str_replace('_', ' ', $h['type']) ?></p>
                                <p class="text-[10px] text-slate-400"><?= date('d M H:i', strtotime($h['created_at'])) ?></p>
                            </div>
                        </div>
                        <span class="font-bold text-xs <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? 'text-green-600' : 'text-red-500' ?>">
                            <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? '+' : '-' ?><?= formatRupiah($h['amount']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Support -->
            <div class="glass-panel p-6 rounded-3xl border-t-4 border-slate-800">
                <h3 class="font-bold text-sm mb-3 flex items-center gap-2"><i data-lucide="life-buoy" class="w-4 h-4"></i> Bantuan Teknis</h3>
                <form action="api_action.php" method="POST">
                    <input type="hidden" name="action" value="send_message">
                    <textarea name="message" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 text-sm focus:ring-1 focus:ring-slate-800 outline-none transition mb-3" rows="3" placeholder="Kirim pesan ke admin..."></textarea>
                    <button class="bg-slate-800 text-white text-xs px-4 py-3 rounded-xl hover:bg-slate-700 w-full transition font-bold">Kirim Tiket</button>
                </form>
            </div>
        </div>

        <!-- RIGHT SIDE: Campaigns -->
        <div class="lg:col-span-2">
            <div class="glass-panel p-8 rounded-3xl min-h-[500px]">
                <div class="flex justify-between items-center mb-8">
                    <div>
                        <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                            <span class="bg-red-50 text-red-600 p-2 rounded-lg"><i data-lucide="youtube" class="w-5 h-5"></i></span>
                            Misi Tersedia
                        </h2>
                        <p class="text-slate-500 text-xs mt-1">Selesaikan misi untuk mendapatkan saldo gratis.</p>
                    </div>
                    <span class="text-[10px] bg-blue-50 text-blue-600 px-3 py-1 rounded-full font-bold uppercase tracking-wide">Auto Refresh</span>
                </div>

                <?php if(count($campaigns) == 0): ?>
                    <div class="flex flex-col items-center justify-center py-24 text-center">
                        <div class="w-24 h-24 bg-slate-50 rounded-full flex items-center justify-center mb-6">
                            <i data-lucide="inbox" class="w-10 h-10 text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Tidak ada misi</h3>
                        <p class="text-slate-500 text-sm">Semua misi telah selesai. Coba lagi nanti.</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-5">
                        <?php foreach($campaigns as $camp): ?>
                        <div class="group bg-white hover:bg-slate-50 p-5 rounded-2xl flex flex-col sm:flex-row justify-between items-center transition border border-slate-100 hover:border-blue-200 hover:shadow-lg">
                            <div class="flex items-center gap-5 mb-4 sm:mb-0 w-full">
                                <img src="<?= $camp['avatar_url'] ?>" class="w-16 h-16 rounded-full border-2 border-white shadow-md group-hover:scale-105 transition">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg mb-1 group-hover:text-blue-600 transition"><?= htmlspecialchars($camp['channel_name']) ?></h4>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs text-green-700 font-bold bg-green-100 px-2 py-1 rounded-lg flex items-center gap-1">
                                            <i data-lucide="dollar-sign" class="w-3 h-3"></i> Reward: <?= formatRupiah(REWARD_PER_SUB) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button onclick="subscribeChannel('<?= $camp['channel_id'] ?>', <?= $camp['id'] ?>)" 
                                    class="w-full sm:w-auto bg-gradient-to-r from-red-600 to-red-500 hover:from-red-700 hover:to-red-600 text-white px-8 py-3.5 rounded-xl font-bold shadow-lg shadow-red-600/20 active:scale-95 transition flex items-center justify-center gap-2">
                                <i data-lucide="youtube" class="w-4 h-4"></i> Subscribe
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function subscribeChannel(channelId, targetUserId) {
    if(!confirm("PENTING: Jangan Unsubscribe channel ini nanti atau saldo Anda akan dipotong denda!")) return;
    
    // Open YouTube
    window.open('https://www.youtube.com/channel/' + channelId + '?sub_confirmation=1', '_blank');
    
    // Simulate API delay slightly for realism
    setTimeout(() => {
        $.post('api_action.php', {
            action: 'subscribe',
            target_user_id: targetUserId,
            target_channel_id: channelId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(res) {
            if(res.status === 'success') {
                alert("Berhasil! Saldo reward telah ditambahkan.");
                location.reload();
            } else {
                alert("Gagal: " + res.message);
            }
        }, 'json');
    }, 2000);
}

lucide.createIcons();
</script>
</body>
</html>