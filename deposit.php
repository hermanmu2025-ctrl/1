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
        
        $ext = strtolower(pathinfo($_FILES["proof"]["name"], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if(in_array($ext, $allowed)) {
            $filename = uniqid() . "." . $ext;
            $target_file = $target_dir . $filename;

            if (move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file)) {
                $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status, proof_img) VALUES (?, 'deposit', ?, 'Deposit User', 'pending', ?)");
                $stmt->execute([$_SESSION['user_id'], $amount, $target_file]);
                $success = "Deposit berhasil diajukan! Admin akan memverifikasi secepatnya.";
            } else {
                $error = "Gagal mengupload file.";
            }
        } else {
            $error = "Format file tidak didukung. Gunakan JPG/PNG.";
        }
    }
}

$page_title = "Isi Saldo";
include 'header.php';
?>

<div class="container mx-auto px-6 py-16 flex justify-center">
    <div class="w-full max-w-4xl bg-white rounded-3xl shadow-2xl overflow-hidden flex flex-col md:flex-row border border-slate-100">
        
        <!-- Left Info -->
        <div class="md:w-5/12 bg-slate-900 p-10 text-white relative">
            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-6">Metode Pembayaran</h2>
                <div class="bg-white/10 p-6 rounded-2xl mb-6 backdrop-blur-sm border border-white/10">
                    <p class="text-xs text-slate-300 uppercase font-bold tracking-wider mb-2">Transfer Bank / E-Wallet</p>
                    <div class="text-2xl font-mono font-bold tracking-widest mb-2"><?= BANK_ACCOUNT_NUMBER ?></div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold"><?= BANK_NAME ?></span>
                        <span class="text-sm opacity-70"><?= BANK_HOLDER ?></span>
                    </div>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed">
                    Silakan transfer sesuai nominal yang diinginkan. Setelah transfer, upload bukti pembayaran pada form di samping.
                </p>
            </div>
        </div>

        <!-- Right Form -->
        <div class="md:w-7/12 p-10">
            <h2 class="text-2xl font-bold text-slate-900 mb-6">Konfirmasi Deposit</h2>
            
            <?php if($success): ?>
                <div class="bg-green-50 text-green-700 p-4 rounded-xl text-sm font-bold flex items-center gap-2 mb-6">
                    <i data-lucide="check-circle" class="w-5 h-5"></i> <?= $success ?>
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm font-bold flex items-center gap-2 mb-6">
                    <i data-lucide="alert-circle" class="w-5 h-5"></i> <?= $error ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Jumlah (IDR)</label>
                    <input type="number" name="amount" min="10000" placeholder="50000" required 
                           class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl font-bold focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Bukti Transfer</label>
                    <input type="file" name="proof" required accept="image/*" 
                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                <button type="submit" class="w-full btn-primary text-white font-bold py-4 rounded-xl shadow-lg">
                    Kirim Konfirmasi
                </button>
            </form>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>