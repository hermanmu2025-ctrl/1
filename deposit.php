<?php
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['proof'])) {
    $amount = (float)$_POST['amount'];
    if ($amount < 10000) {
        $error = "Minimal deposit Rp 10.000";
    } else {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $filename = uniqid() . "_" . basename($_FILES["proof"]["name"]);
        $target_file = $target_dir . $filename;

        if (move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status, proof_img) VALUES (?, 'deposit', ?, 'Deposit User', 'pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $amount, $target_file]);
            $success = "Permintaan deposit dikirim! Saldo akan masuk otomatis setelah admin atau sistem memverifikasi mutasi.";
        } else {
            $error = "Gagal upload bukti transfer.";
        }
    }
}

$page_title = "Deposit Saldo";
include 'header.php';
?>

<div class="container mx-auto p-4 flex justify-center py-12">
    <div class="bg-white w-full max-w-5xl rounded-3xl shadow-2xl shadow-slate-200 overflow-hidden flex flex-col md:flex-row min-h-[600px]">
        
        <!-- Left: Payment Methods -->
        <div class="md:w-5/12 bg-slate-900 p-8 text-white relative flex flex-col">
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600 rounded-full blur-3xl opacity-20"></div>
            <h2 class="text-2xl font-bold mb-8 z-10">Metode Pembayaran</h2>
            
            <!-- Tabs -->
            <div class="flex gap-2 mb-8 z-10">
                <button onclick="switchTab('instant')" id="btn-instant" class="flex-1 bg-blue-600 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/30 transition">Instant QRIS</button>
                <button onclick="switchTab('manual')" id="btn-manual" class="flex-1 bg-slate-800 py-3 rounded-xl font-bold text-sm hover:bg-slate-700 transition">Transfer Bank</button>
            </div>

            <!-- QRIS View -->
            <div id="view-instant" class="flex-1 flex flex-col items-center justify-center text-center space-y-4 z-10">
                 <div class="bg-white p-4 rounded-2xl shadow-xl w-64 h-64 flex items-center justify-center">
                    <!-- Real generated QR based on Account -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= BANK_ACCOUNT_NUMBER ?>" alt="QRIS" class="w-full h-full">
                 </div>
                 <p class="text-sm text-slate-400">Scan menggunakan OVO, DANA, GoPay, atau M-Banking.</p>
                 <div class="bg-green-500/20 text-green-400 px-4 py-2 rounded-full text-xs font-bold animate-pulse">
                    Sistem Cek Mutasi Otomatis Aktif
                 </div>
            </div>

            <!-- Manual View -->
            <div id="view-manual" class="hidden flex-1 flex flex-col justify-center space-y-6 z-10">
                <div class="bg-slate-800 p-6 rounded-2xl border border-slate-700">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/1200px-BANK_BRI_logo.svg.png" class="w-8">
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase">Bank Transfer</p>
                            <p class="font-bold">Bank BRI</p>
                        </div>
                    </div>
                    <p class="text-2xl font-mono font-bold tracking-wider mb-2"><?= BANK_ACCOUNT_NUMBER ?></p>
                    <p class="text-slate-400 text-sm"><?= BANK_HOLDER ?></p>
                    <button onclick="navigator.clipboard.writeText('<?= BANK_ACCOUNT_NUMBER ?>');alert('Disalin!');" class="mt-4 w-full py-2 bg-slate-700 rounded-lg text-xs hover:bg-slate-600 transition">Salin Nomor Rekening</button>
                </div>
                <div class="text-xs text-slate-400 text-center">
                    Harap transfer sesuai nominal unik (jika ada) untuk mempercepat verifikasi.
                </div>
            </div>
        </div>

        <!-- Right: Confirmation Form -->
        <div class="md:w-7/12 p-10 bg-white flex flex-col justify-center">
             <h2 class="text-2xl font-bold mb-2 text-slate-800">Konfirmasi Pembayaran</h2>
             <p class="text-slate-500 mb-8">Wajib upload bukti transfer agar saldo masuk.</p>

            <?php if($success) echo "<div class='bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 flex gap-2'><i data-lucide='check-circle'></i> $success</div>"; ?>
            <?php if($error) echo "<div class='bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100 flex gap-2'><i data-lucide='alert-octagon'></i> $error</div>"; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Deposit (IDR)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-4 text-slate-400 font-bold">Rp</span>
                        <input type="number" name="amount" min="10000" placeholder="50000" required class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 pl-12 font-bold text-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Bukti Pembayaran</label>
                    <input type="file" name="proof" required accept="image/*" class="block w-full text-sm text-slate-500 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-4 rounded-xl shadow-lg transition">
                    Saya Sudah Transfer
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function switchTab(tab) {
    if(tab === 'instant') {
        document.getElementById('view-instant').classList.remove('hidden');
        document.getElementById('view-manual').classList.add('hidden');
        document.getElementById('btn-instant').classList.replace('bg-slate-800', 'bg-blue-600');
        document.getElementById('btn-manual').classList.replace('bg-blue-600', 'bg-slate-800');
    } else {
        document.getElementById('view-instant').classList.add('hidden');
        document.getElementById('view-manual').classList.remove('hidden');
        document.getElementById('btn-manual').classList.replace('bg-slate-800', 'bg-blue-600');
        document.getElementById('btn-instant').classList.replace('bg-blue-600', 'bg-slate-800');
    }
}
lucide.createIcons();
</script>
</body>
</html>