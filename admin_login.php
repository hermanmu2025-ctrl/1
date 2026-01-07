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

<div class="min-h-[80vh] flex items-center justify-center p-6">
    <div class="glass-panel p-8 rounded-2xl w-full max-w-md shadow-2xl">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="lock" class="w-8 h-8 text-red-600"></i>
            </div>
            <h1 class="text-2xl font-bold text-slate-800">Admin Access</h1>
            <p class="text-slate-500">Restricted Area</p>
        </div>

        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-6 text-center font-bold text-sm border border-red-100">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-2">Password Admin</label>
                <input type="password" name="password" required placeholder="Masukkan Password..." 
                       class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 outline-none focus:ring-2 focus:ring-red-500 transition">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-red-600/20 transition">
                Masuk System
            </button>
        </form>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>