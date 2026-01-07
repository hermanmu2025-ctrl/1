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

$page_title = "Dashboard";
include 'header.php';
?>

<div class="container mx-auto p-4 max-w-6xl py-10">
    
    <!-- Welcome Banner -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Halo, <?= htmlspecialchars($user['channel_name']) ?>! 👋</h1>
        <p class="text-slate-500">Siap mengembangkan channelmu hari ini?</p>
    </div>

    <?php if($promo): ?>
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 rounded-2xl shadow-lg text-white mb-8 flex items-start gap-4">
        <div class="bg-white/20 p-3 rounded-lg">
            <i data-lucide="megaphone" class="w-6 h-6 text-white"></i>
        </div>
        <div>
            <h3 class="font-bold text-lg mb-1">Informasi Penting</h3>
            <p class="opacity-90 leading-relaxed"><?= htmlspecialchars($promo['content']) ?></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- LEFT SIDE: Stats & Personal -->
        <div class="space-y-8">
            <!-- Stats Card -->
            <div class="bg-white p-6 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex items-center gap-4 mb-6">
                    <img src="<?= $user['avatar_url'] ?>" class="w-16 h-16 rounded-full border-4 border-slate-50">
                    <div>
                        <h2 class="font-bold text-lg truncate w-40 text-slate-800"><?= htmlspecialchars($user['channel_name']) ?></h2>
                        <div class="flex items-center gap-1 text-xs text-slate-400 font-mono bg-slate-100 px-2 py-1 rounded">
                             <?= substr($user['channel_id'], 0, 10) ?>...
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 text-center mb-6">
                    <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                        <p class="text-xs text-blue-600 font-bold uppercase tracking-wide mb-1">Saldo Aktif</p>
                        <p class="font-bold text-slate-800 text-lg"><?= formatRupiah($user['balance']) ?></p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-xl border border-purple-100">
                        <p class="text-xs text-purple-600 font-bold uppercase tracking-wide mb-1">Total Subs</p>
                        <p class="font-bold text-slate-800 text-lg">+<?= $user['total_subs_gained'] ?></p>
                    </div>
                </div>
                
                <a href="deposit.php" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-xl font-bold shadow-lg shadow-blue-600/20 transition transform active:scale-95">
                    <i data-lucide="wallet" class="w-4 h-4 inline mr-2"></i> Isi Saldo (Deposit)
                </a>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white p-6 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="history" class="w-4 h-4 text-slate-400"></i> Riwayat Transaksi
                </h3>
                <div class="space-y-4">
                    <?php foreach($history as $h): ?>
                    <div class="flex justify-between items-center pb-3 border-b border-slate-50 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-slate-700 capitalize"><?= str_replace('_', ' ', $h['type']) ?></p>
                            <p class="text-[10px] text-slate-400"><?= date('d M H:i', strtotime($h['created_at'])) ?></p>
                        </div>
                        <span class="font-bold text-xs <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? 'text-green-600 bg-green-50 px-2 py-1 rounded' : 'text-red-500' ?>">
                            <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? '+' : '-' ?><?= formatRupiah($h['amount']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Support -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100">
                <h3 class="font-bold text-sm mb-3">Pusat Bantuan</h3>
                <form action="api_action.php" method="POST">
                    <input type="hidden" name="action" value="send_message">
                    <textarea name="message" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-sm focus:ring-1 focus:ring-blue-500 outline-none" rows="3" placeholder="Kirim pesan ke admin..."></textarea>
                    <button class="mt-3 bg-slate-800 text-white text-xs px-4 py-2 rounded-lg hover:bg-slate-700 w-full transition">Kirim Tiket</button>
                </form>
            </div>
        </div>

        <!-- RIGHT SIDE: Campaigns -->
        <div class="lg:col-span-2">
            <div class="bg-white p-6 rounded-2xl shadow-xl shadow-slate-200/50 border border-slate-100">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                        <span class="bg-red-100 text-red-600 p-1.5 rounded-lg"><i data-lucide="youtube" class="w-5 h-5"></i></span>
                        Misi Tersedia
                    </h2>
                    <span class="text-xs bg-slate-100 text-slate-500 px-3 py-1 rounded-full">Auto Refresh</span>
                </div>

                <?php if(count($campaigns) == 0): ?>
                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                            <i data-lucide="inbox" class="w-10 h-10 text-slate-300"></i>
                        </div>
                        <p class="text-slate-500">Tidak ada misi tersedia saat ini.</p>
                    </div>
                <?php else: ?>
                    <div class="grid gap-4">
                        <?php foreach($campaigns as $camp): ?>
                        <div class="group bg-slate-50 hover:bg-white p-4 rounded-xl flex flex-col sm:flex-row justify-between items-center transition border border-transparent hover:border-blue-100 hover:shadow-lg">
                            <div class="flex items-center gap-4 mb-4 sm:mb-0 w-full">
                                <img src="<?= $camp['avatar_url'] ?>" class="w-14 h-14 rounded-full border-2 border-white shadow-sm">
                                <div>
                                    <h4 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($camp['channel_name']) ?></h4>
                                    <div class="flex items-center gap-2">
                                        <p class="text-xs text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded">Reward: <?= formatRupiah(REWARD_PER_SUB) ?></p>
                                    </div>
                                </div>
                            </div>
                            <button onclick="subscribeChannel('<?= $camp['channel_id'] ?>', <?= $camp['id'] ?>)" 
                                    class="w-full sm:w-auto bg-red-600 hover:bg-red-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-red-600/20 active:scale-95 transition flex items-center justify-center gap-2">
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
                // Success UI Feedback
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