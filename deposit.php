<?php
require_once 'functions.php';
if (!isset($_SESSION['user_id'])) header("Location: index.php");

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
            $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status, proof_img) VALUES (?, 'deposit', ?, 'Deposit QRIS BRI', 'pending', ?)");
            $stmt->execute([$_SESSION['user_id'], $amount, $target_file]);
            $success = "Deposit berhasil dikirim! Sistem kami sedang memverifikasi mutasi bank. Saldo akan masuk otomatis setelah terverifikasi (Max 5 Menit).";
        } else {
            $error = "Gagal upload bukti transfer.";
        }
    }
}

$page_title = "Deposit Saldo";
include 'header.php';
?>

<div class="container mx-auto p-4 flex justify-center py-12">
    <div class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl shadow-slate-200 overflow-hidden flex flex-col md:flex-row">
        
        <!-- Left: Payment Info -->
        <div class="md:w-1/2 bg-slate-900 p-8 text-white relative overflow-hidden">
            <!-- Abstract BG -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600 rounded-full blur-3xl opacity-20 -translate-y-1/2 translate-x-1/2"></div>
            
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                     <i data-lucide="qr-code" class="w-6 h-6"></i> Scan QRIS
                </h2>
                
                <div class="bg-white p-4 rounded-xl w-fit mx-auto mb-6 shadow-lg">
                    <!-- Use a reliable QR Code API with the specific data -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=<?= BANK_ACCOUNT_NUMBER ?>" 
                         alt="QRIS BRI" class="w-48 h-48">
                </div>

                <div class="text-center space-y-4">
                    <div class="bg-white/10 p-4 rounded-xl border border-white/10">
                        <p class="text-slate-400 text-xs uppercase tracking-widest mb-1">Transfer Manual (BRI)</p>
                        <p class="text-2xl font-mono font-bold tracking-wider"><?= BANK_ACCOUNT_NUMBER ?></p>
                        <button onclick="navigator.clipboard.writeText('<?= BANK_ACCOUNT_NUMBER ?>');alert('Disalin!');" class="text-xs text-blue-300 hover:text-white mt-2 flex items-center justify-center gap-1 cursor-pointer">
                            <i data-lucide="copy" class="w-3 h-3"></i> Salin Nomor
                        </button>
                    </div>
                    <div>
                        <p class="text-sm text-slate-400">Atas Nama</p>
                        <p class="font-bold text-lg"><?= BANK_HOLDER ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Upload Form -->
        <div class="md:w-1/2 p-8 bg-white">
             <h2 class="text-2xl font-bold mb-2 text-slate-800">Konfirmasi</h2>
             <p class="text-slate-500 mb-6 text-sm">Upload bukti pembayaran agar saldo masuk otomatis.</p>

            <?php if(isset($success)) echo "<div class='bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 flex gap-2'><i data-lucide='check-circle' class='w-5 h-5 flex-shrink-0'></i> $success</div>"; ?>
            <?php if(isset($error)) echo "<div class='bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100 flex gap-2'><i data-lucide='alert-octagon' class='w-5 h-5 flex-shrink-0'></i> $error</div>"; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah Deposit (IDR)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-3.5 text-slate-400 font-bold">Rp</span>
                        <input type="number" name="amount" min="10000" placeholder="Contoh: 50000" required 
                               class="w-full bg-slate-50 border border-slate-200 rounded-xl p-3 pl-12 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none font-bold text-slate-800">
                    </div>
                    <p class="text-xs text-slate-400 mt-1">*Minimal Rp 10.000</p>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Bukti Transfer</label>
                    <div class="border-2 border-dashed border-slate-200 rounded-xl p-6 text-center hover:bg-slate-50 transition cursor-pointer relative">
                        <input type="file" name="proof" required accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                        <i data-lucide="upload-cloud" class="w-8 h-8 text-blue-500 mx-auto mb-2"></i>
                        <p class="text-sm text-slate-500">Klik untuk upload gambar</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition shadow-lg shadow-blue-600/20">
                    Saya Sudah Bayar
                </button>
            </form>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>