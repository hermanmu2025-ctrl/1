<?php
require_once 'config.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    // Security Requirement: Fixed Password 'Amnet123'
    if ($pass === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Password Salah!";
    }
}

$page_title = "Admin Login";
include 'header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center p-6 bg-slate-50">
    <div class="glass-panel p-10 rounded-3xl w-full max-w-md shadow-2xl border border-white">
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6 shadow-inner">
                <i data-lucide="shield-alert" class="w-10 h-10 text-red-600"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Admin Portal</h1>
            <p class="text-slate-500 font-medium">Restricted Access Only</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-center font-bold text-sm border border-red-100 flex items-center justify-center gap-2">
                <i data-lucide="x-circle" class="w-4 h-4"></i> <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Security Key</label>
                <input type="password" name="password" required placeholder="Enter Password..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl p-5 outline-none focus:ring-2 focus:ring-red-500 transition text-slate-800 font-bold placeholder:font-normal">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold py-4 rounded-xl shadow-lg shadow-red-600/30 transition transform active:scale-95">
                Authenticate
            </button>
        </form>
        
        <div class="mt-8 text-center">
            <a href="index.php" class="text-slate-400 text-xs font-bold hover:text-slate-600 transition">Back to Home</a>
        </div>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>