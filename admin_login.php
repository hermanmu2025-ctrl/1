<?php
require_once 'config.php';

if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    if ($pass === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit;
    } else {
        $error = "Invalid Security Key";
    }
}

$page_title = "Restricted Access";
include 'header.php';
?>
<div class="flex items-center justify-center min-h-[80vh]">
    <div class="w-full max-w-md p-10 bg-white rounded-3xl shadow-2xl border border-slate-100 text-center">
        <div class="w-20 h-20 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i data-lucide="shield-alert" class="w-10 h-10 text-red-600"></i>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 mb-2">Admin Portal</h1>
        <p class="text-slate-400 mb-8">Authorized Personnel Only</p>
        
        <?php if($error): ?>
            <div class="bg-red-50 text-red-600 p-3 rounded-lg text-sm font-bold mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="password" name="password" placeholder="Security Key..." required class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl mb-4 focus:ring-2 focus:ring-red-500 outline-none">
            <button class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-4 rounded-xl transition">Authenticate</button>
        </form>
    </div>
</div>
<script>lucide.createIcons();</script>
</body>
</html>