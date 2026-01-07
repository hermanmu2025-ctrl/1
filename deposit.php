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

<div class="container mx-auto p-4 flex justify-center py-16">
    <div class="bg-white w-full max-w-5xl rounded-[2rem] shadow-2xl shadow-slate-200 overflow-hidden flex flex-col md:flex-row min-h-[650px] border border-slate-100">
        
        <!-- Left: Payment Methods -->
        <div class="md:w-5/12 bg-slate-900 p-10 text-white relative flex flex-col justify-between">
            <!-- Abstract shapes -->
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600 rounded-full blur-[80px] opacity-20 pointer-events-none"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-purple-600 rounded-full blur-[80px] opacity-20 pointer-events-none"></div>

            <div class="relative z-10">
                <h2 class="text-2xl font-bold mb-8 flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center"><i data-lucide="credit-card" class="w-5 h-5"></i></div>
                    Metode Pembayaran
                </h2>
                
                <!-- Tabs -->
                <div class="flex bg-slate-800/50 p-1 rounded-2xl mb-8 backdrop-blur">
                    <button onclick="switchTab('instant')" id="btn-instant" class="flex-1 bg-blue-600 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-600/30 transition text-white">QRIS (Instant)</button>
                    <button onclick="switchTab('manual')" id="btn-manual" class="flex-1 py-3 rounded-xl font-bold text-sm text-slate-400 hover:text-white transition">Bank Transfer</button>
                </div>

                <!-- QRIS View -->
                <div id="view-instant" class="flex flex-col items-center justify-center text-center space-y-6">
                     <div class="bg-white p-6 rounded-3xl shadow-xl w-64 h-64 flex items-center justify-center relative">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=<?= BANK_ACCOUNT_NUMBER ?>" alt="QRIS" class="w-full h-full mix-blend-multiply">
                        <div class="absolute inset-0 border-4 border-slate-900/5 rounded-3xl"></div>
                     </div>
                     <div class="space-y-2">
                         <p class="text-sm text-slate-300 font-medium">Scan menggunakan E-Wallet / Mobile Banking</p>
                         <div class="flex justify-center gap-3 opacity-60 grayscale hover:grayscale-0 transition duration-500">
                             <img src="https://upload.wikimedia.org/wikipedia/commons/8/86/Gopay_logo.svg" class="h-6">
                             <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Logo_ovo_purple.svg/2560px-Logo_ovo_purple.svg.png" class="h-6">
                             <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/2560px-Logo_dana_blue.svg.png" class="h-6">
                         </div>
                     </div>
                </div>

                <!-- Manual View -->
                <div id="view-manual" class="hidden flex flex-col justify-center space-y-6">
                    <div class="bg-gradient-to-br from-slate-800 to-slate-800/50 p-6 rounded-3xl border border-slate-700/50">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-white rounded-xl flex items-center justify-center">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/BANK_BRI_logo.svg/1200px-BANK_BRI_logo.svg.png" class="w-8">
                            </div>
                            <div>
                                <p class="text-xs text-slate-400 uppercase font-bold tracking-wider">Bank Transfer</p>
                                <p class="font-bold text-lg">Bank BRI</p>
                            </div>
                        </div>
                        <p class="text-2xl font-mono font-bold tracking-wider mb-2 text-white/90"><?= BANK_ACCOUNT_NUMBER ?></p>
                        <p class="text-slate-400 text-sm mb-6"><?= BANK_HOLDER ?></p>
                        
                        <button onclick="navigator.clipboard.writeText('<?= BANK_ACCOUNT_NUMBER ?>');alert('Disalin!');" class="w-full py-3 bg-slate-700 rounded-xl text-xs font-bold hover:bg-slate-600 transition flex items-center justify-center gap-2">
                            <i data-lucide="copy" class="w-3 h-3"></i> Salin Nomor Rekening
                        </button>
                    </div>
                    <div class="bg-yellow-500/10 border border-yellow-500/20 p-4 rounded-xl">
                         <p class="text-xs text-yellow-500 text-center font-medium">
                            <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-1"></i> 
                            Harap transfer sesuai nominal unik (jika ada) untuk mempercepat verifikasi.
                         </p>
                    </div>
                </div>
            </div>
            
            <div class="mt-8 pt-6 border-t border-slate-800 text-xs text-slate-500 text-center">
                Secure Payment Gateway Encrypted
            </div>
        </div>

        <!-- Right: Confirmation Form -->
        <div class="md:w-7/12 p-12 bg-white flex flex-col justify-center">
             <h2 class="text-3xl font-bold mb-2 text-slate-800">Konfirmasi Deposit</h2>
             <p class="text-slate-500 mb-10">Upload bukti transfer agar saldo masuk ke akun Anda.</p>

            <?php if($success) echo "<div class='bg-green-50 text-green-700 p-4 rounded-xl text-sm mb-6 border border-green-100 flex gap-2 items-center font-bold'><i data-lucide='check-circle' class='w-5 h-5'></i> $success</div>"; ?>
            <?php if($error) echo "<div class='bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 border border-red-100 flex gap-2 items-center font-bold'><i data-lucide='alert-octagon' class='w-5 h-5'></i> $error</div>"; ?>

            <form method="POST" enctype="multipart/form-data" class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3">Jumlah Deposit (IDR)</label>
                    <div class="relative">
                        <span class="absolute left-5 top-5 text-slate-400 font-bold">Rp</span>
                        <input type="number" name="amount" min="10000" placeholder="50000" required class="w-full bg-slate-50 border border-slate-200 rounded-2xl p-5 pl-12 font-bold text-xl focus:ring-2 focus:ring-blue-500 outline-none transition text-slate-800">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3">Bukti Pembayaran</label>
                    <div class="relative border-2 border-dashed border-slate-200 rounded-2xl p-8 text-center hover:bg-slate-50 transition hover:border-blue-400 cursor-pointer">
                        <input type="file" name="proof" required accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <i data-lucide="upload-cloud" class="w-8 h-8 text-slate-400 mx-auto mb-2"></i>
                        <p class="text-sm font-bold text-blue-600">Klik untuk upload</p>
                        <p class="text-xs text-slate-400">JPG, PNG, atau PDF (Max 2MB)</p>
                    </div>
                </div>

                <button type="submit" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold py-5 rounded-2xl shadow-xl shadow-slate-900/20 transition transform active:scale-[0.98] flex justify-center items-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i> Saya Sudah Transfer
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
        document.getElementById('btn-instant').classList.remove('bg-transparent', 'text-slate-400');
        document.getElementById('btn-instant').classList.add('bg-blue-600', 'text-white', 'shadow-lg');
        
        document.getElementById('btn-manual').classList.add('bg-transparent', 'text-slate-400');
        document.getElementById('btn-manual').classList.remove('bg-blue-600', 'text-white', 'shadow-lg');
    } else {
        document.getElementById('view-instant').classList.add('hidden');
        document.getElementById('view-manual').classList.remove('hidden');
        
        document.getElementById('btn-manual').classList.remove('bg-transparent', 'text-slate-400');
        document.getElementById('btn-manual').classList.add('bg-blue-600', 'text-white', 'shadow-lg');
        
        document.getElementById('btn-instant').classList.add('bg-transparent', 'text-slate-400');
        document.getElementById('btn-instant').classList.remove('bg-blue-600', 'text-white', 'shadow-lg');
    }
}
lucide.createIcons();
</script>
</body>
</html>