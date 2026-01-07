<?php
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

$user = getUser($_SESSION['user_id']);

// Campaign Logic
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? AND balance >= ? ORDER BY RAND() LIMIT 9");
$stmt->execute([$user['id'], PRICE_PER_SUB]);
$campaigns = $stmt->fetchAll();

// History
$histStmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
$histStmt->execute([$user['id']]);
$history = $histStmt->fetchAll();

$page_title = "Dashboard Creator";
include 'header.php';
?>

<div class="container mx-auto px-6 py-10">
    
    <!-- Page Title -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-10">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900">Dashboard</h1>
            <p class="text-slate-500">Kelola kampanye dan dapatkan poin.</p>
        </div>
        <div class="flex items-center gap-3 bg-white p-2 pr-6 rounded-full shadow-sm border border-slate-200">
            <img src="<?= $user['avatar_url'] ?>" class="w-10 h-10 rounded-full border border-slate-200">
            <div>
                <p class="text-sm font-bold text-slate-900 leading-none"><?= htmlspecialchars(substr($user['channel_name'], 0, 15)) ?></p>
                <p class="text-[10px] text-green-600 font-bold uppercase tracking-wider">Online</p>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid md:grid-cols-3 gap-6 mb-10">
        <!-- Balance Card -->
        <div class="bg-gradient-to-br from-brand-600 to-brand-700 rounded-3xl p-8 text-white relative overflow-hidden shadow-xl shadow-brand-500/20">
            <div class="absolute top-0 right-0 p-6 opacity-20">
                <i data-lucide="wallet" class="w-24 h-24"></i>
            </div>
            <p class="text-brand-100 text-sm font-bold uppercase tracking-widest mb-2">Saldo Aktif</p>
            <h2 class="text-4xl font-extrabold mb-6"><?= formatRupiah($user['balance']) ?></h2>
            <div class="flex gap-3 relative z-10">
                <a href="deposit.php" class="bg-white text-brand-700 px-5 py-2.5 rounded-xl font-bold text-sm shadow hover:bg-brand-50 transition flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i> Isi Saldo
                </a>
                <button class="bg-brand-800 text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-brand-900 transition">
                    Withdraw
                </button>
            </div>
        </div>

        <!-- Performance Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-lg shadow-slate-200/50">
             <div class="flex justify-between items-start mb-4">
                 <div class="p-3 bg-purple-50 text-purple-600 rounded-xl">
                     <i data-lucide="bar-chart-2" class="w-6 h-6"></i>
                 </div>
                 <span class="text-xs font-bold bg-green-100 text-green-600 px-2 py-1 rounded-lg">+12% Week</span>
             </div>
             <p class="text-slate-500 text-xs font-bold uppercase">Total Subscribers Didapat</p>
             <h2 class="text-3xl font-extrabold text-slate-900 mt-2"><?= $user['total_subs_gained'] ?> <span class="text-lg text-slate-400 font-medium">Subs</span></h2>
             <div class="w-full bg-slate-100 h-2 rounded-full mt-4 overflow-hidden">
                 <div class="bg-purple-600 h-full w-[60%] rounded-full"></div>
             </div>
        </div>

        <!-- Quick Actions / Status -->
        <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-lg shadow-slate-200/50 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 mb-4 flex items-center gap-2">
                    <i data-lucide="shield-check" class="w-5 h-5 text-green-500"></i> Status Akun
                </h3>
                <ul class="space-y-3 text-sm">
                    <li class="flex justify-between text-slate-600">
                        <span>Reputasi</span>
                        <span class="font-bold text-slate-900">Baik (100%)</span>
                    </li>
                    <li class="flex justify-between text-slate-600">
                        <span>Level</span>
                        <span class="font-bold text-brand-600">Creator Basic</span>
                    </li>
                </ul>
            </div>
            <a href="logout.php" class="mt-6 w-full py-3 border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 hover:text-red-600 transition text-center text-sm">
                Sign Out
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main: Active Missions -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-xl font-bold text-slate-900">Misi Tersedia</h3>
                        <p class="text-slate-500 text-sm">Dapatkan <span class="text-green-600 font-bold"><?= formatRupiah(REWARD_PER_SUB) ?></span> per subscribe.</p>
                    </div>
                    <button onclick="location.reload()" class="p-2 hover:bg-slate-100 rounded-full transition text-slate-500">
                        <i data-lucide="refresh-cw" class="w-5 h-5"></i>
                    </button>
                </div>

                <?php if(empty($campaigns)): ?>
                    <div class="p-12 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Misi Habis!</h3>
                        <p class="text-slate-500">Coba refresh dalam beberapa menit.</p>
                    </div>
                <?php else: ?>
                    <div class="p-6 grid md:grid-cols-2 gap-4">
                        <?php foreach($campaigns as $camp): ?>
                        <div class="p-5 rounded-2xl border border-slate-100 hover:border-brand-300 hover:shadow-md transition group bg-slate-50/50 hover:bg-white">
                            <div class="flex items-center gap-4 mb-4">
                                <img src="<?= $camp['avatar_url'] ?>" class="w-12 h-12 rounded-full shadow-sm">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-900 truncate pr-2 group-hover:text-brand-600 transition"><?= htmlspecialchars($camp['channel_name']) ?></h4>
                                    <span class="text-[10px] uppercase font-bold text-slate-400 bg-slate-200 px-2 py-0.5 rounded">Youtube</span>
                                </div>
                            </div>
                            <button onclick="doSubscribe('<?= $camp['channel_id'] ?>', <?= $camp['id'] ?>)" 
                                    class="w-full bg-white border-2 border-red-50 text-red-600 hover:bg-red-600 hover:text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
                                <i data-lucide="youtube" class="w-4 h-4"></i> Subscribe +<?= REWARD_PER_SUB ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar: History -->
        <div class="space-y-8">
            <div class="bg-white rounded-[2rem] border border-slate-200 shadow-sm p-6">
                <h3 class="font-bold text-slate-900 mb-6 flex items-center gap-2">
                    <i data-lucide="clock" class="w-5 h-5 text-slate-400"></i> Aktivitas Terakhir
                </h3>
                <div class="relative border-l-2 border-slate-100 ml-3 space-y-6">
                    <?php foreach($history as $h): ?>
                    <div class="pl-6 relative">
                        <div class="absolute -left-[9px] top-1 w-4 h-4 rounded-full border-2 border-white <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? 'bg-green-500' : 'bg-orange-500' ?>"></div>
                        <p class="text-xs text-slate-400 font-bold mb-1"><?= date('H:i, d M', strtotime($h['created_at'])) ?></p>
                        <p class="font-bold text-slate-800 text-sm capitalize"><?= str_replace('_', ' ', $h['type']) ?></p>
                        <span class="text-xs font-bold <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? 'text-green-600' : 'text-red-500' ?>">
                            <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? '+' : '-' ?> <?= formatRupiah($h['amount']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Promo Box -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-3xl p-6 text-white text-center">
                <div class="w-12 h-12 bg-white/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="star" class="w-6 h-6 text-yellow-400"></i>
                </div>
                <h4 class="font-bold mb-2">Upgrade Premium</h4>
                <p class="text-xs text-slate-400 mb-4">Dapatkan 2x Poin lebih banyak.</p>
                <button class="w-full py-2 bg-brand-600 hover:bg-brand-500 rounded-lg text-xs font-bold transition">Coming Soon</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function doSubscribe(channelId, targetUid) {
    if(!confirm("SYSTEM: Membuka Youtube...\n\n1. Klik SUBSCRIBE\n2. Tunggu 3 detik\n3. Tutup tab Youtube untuk klaim poin.")) return;
    
    window.open('https://www.youtube.com/channel/' + channelId + '?sub_confirmation=1', '_blank');
    
    // Simulate Verification Delay for User Experience
    setTimeout(() => {
        $.post('api_action.php', {
            action: 'subscribe',
            target_user_id: targetUid,
            target_channel_id: channelId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(res) {
            if(res.status === 'success') {
                // Create a nice toast or reload
                location.reload();
            } else {
                alert(res.message);
            }
        }, 'json');
    }, 5000);
}
lucide.createIcons();
</script>
</body>
</html>