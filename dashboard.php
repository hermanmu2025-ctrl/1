<?php
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

$user = getUser($_SESSION['user_id']);

// Campaign Logic: Get users who have balance >= PRICE (105) and are not me
// Order by RAND() ensures fair distribution
$stmt = $pdo->prepare("SELECT * FROM users WHERE id != ? AND balance >= ? ORDER BY RAND() LIMIT 6");
$stmt->execute([$user['id'], PRICE_PER_SUB]);
$campaigns = $stmt->fetchAll();

// History
$histStmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$histStmt->execute([$user['id']]);
$history = $histStmt->fetchAll();

$page_title = "Dashboard Creator";
include 'header.php';
?>

<div class="container mx-auto px-6 py-12">
    
    <!-- Welcome Header -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-6 mb-12">
        <div class="flex items-center gap-5">
            <img src="<?= $user['avatar_url'] ?>" class="w-20 h-20 rounded-full border-4 border-white shadow-lg">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Halo, <?= htmlspecialchars($user['channel_name']) ?> 👋</h1>
                <div class="flex items-center gap-2 text-sm text-slate-500 mt-1">
                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-bold uppercase">Member Active</span>
                    <span>ID: <?= substr($user['channel_id'], 0, 10) ?>...</span>
                </div>
            </div>
        </div>
        <div class="flex gap-3">
            <a href="deposit.php" class="btn-primary text-white px-6 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2">
                <i data-lucide="wallet" class="w-4 h-4"></i> Isi Saldo
            </a>
            <a href="logout.php" class="bg-white border border-slate-200 text-slate-700 px-6 py-3 rounded-xl font-bold hover:bg-slate-50 transition">
                Keluar
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        
        <!-- Left: Stats & History -->
        <div class="space-y-8">
            <div class="bg-white p-8 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40">
                <h3 class="font-bold text-slate-800 mb-6">Dompet Saya</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-5 rounded-2xl border border-blue-100">
                        <p class="text-xs text-blue-600 font-bold uppercase mb-1">Saldo Aktif</p>
                        <p class="text-2xl font-extrabold text-blue-700"><?= formatRupiah($user['balance']) ?></p>
                    </div>
                    <div class="bg-purple-50 p-5 rounded-2xl border border-purple-100">
                        <p class="text-xs text-purple-600 font-bold uppercase mb-1">Total Subs</p>
                        <p class="text-2xl font-extrabold text-purple-700">+<?= $user['total_subs_gained'] ?></p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-3xl border border-slate-100">
                <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="history" class="w-5 h-5 text-slate-400"></i> Riwayat Transaksi
                </h3>
                <div class="space-y-6">
                    <?php foreach($history as $h): ?>
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-sm font-bold text-slate-700 capitalize"><?= str_replace('_', ' ', $h['type']) ?></p>
                            <p class="text-xs text-slate-400"><?= date('d M H:i', strtotime($h['created_at'])) ?></p>
                        </div>
                        <span class="font-bold text-sm <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? 'text-green-600' : 'text-red-500' ?>">
                            <?= ($h['type'] == 'deposit' || $h['type'] == 'sub_income') ? '+' : '-' ?><?= formatRupiah($h['amount']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right: Tasks -->
        <div class="lg:col-span-2">
            <div class="bg-white p-8 rounded-3xl border border-slate-200 shadow-sm">
                <div class="flex justify-between items-center mb-8 border-b border-slate-100 pb-6">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i> Misi Tersedia
                        </h2>
                        <p class="text-slate-500 text-sm mt-1">Subscribe channel berikut untuk mendapatkan <span class="text-green-600 font-bold"><?= formatRupiah(REWARD_PER_SUB) ?></span>.</p>
                    </div>
                    <button onclick="location.reload()" class="p-2 bg-slate-100 hover:bg-slate-200 rounded-full transition">
                        <i data-lucide="refresh-ccw" class="w-5 h-5 text-slate-600"></i>
                    </button>
                </div>

                <?php if(empty($campaigns)): ?>
                    <div class="text-center py-20">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-700">Tidak ada misi saat ini.</h3>
                        <p class="text-slate-500">Silakan cek kembali nanti.</p>
                    </div>
                <?php else: ?>
                    <div class="grid md:grid-cols-2 gap-5">
                        <?php foreach($campaigns as $camp): ?>
                        <div class="p-6 rounded-2xl bg-slate-50 border border-slate-100 hover:border-blue-300 hover:shadow-md transition flex items-center gap-4 group">
                            <img src="<?= $camp['avatar_url'] ?>" class="w-14 h-14 rounded-full border-2 border-white shadow-sm">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-slate-900 truncate group-hover:text-blue-600 transition"><?= htmlspecialchars($camp['channel_name']) ?></h4>
                                <p class="text-xs text-slate-500">Public Channel</p>
                            </div>
                            <button onclick="doSubscribe('<?= $camp['channel_id'] ?>', <?= $camp['id'] ?>)" 
                                    class="bg-red-600 hover:bg-red-700 text-white p-3 rounded-xl shadow-lg shadow-red-500/30 transition active:scale-95">
                                <i data-lucide="youtube" class="w-5 h-5"></i>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function doSubscribe(channelId, targetUid) {
    if(!confirm("PENTING: Membuka Youtube... Jangan lupa Subscribe! Jika Anda Unsubscribe nanti, saldo akan dipotong denda.")) return;
    
    window.open('https://www.youtube.com/channel/' + channelId + '?sub_confirmation=1', '_blank');
    
    // Simulate Verification Delay
    setTimeout(() => {
        $.post('api_action.php', {
            action: 'subscribe',
            target_user_id: targetUid,
            target_channel_id: channelId,
            csrf_token: '<?= $_SESSION['csrf_token'] ?>'
        }, function(res) {
            if(res.status === 'success') {
                alert("Sukses! Saldo ditambahkan.");
                location.reload();
            } else {
                alert(res.message);
            }
        }, 'json');
    }, 3000);
}
lucide.createIcons();
</script>
</body>
</html>