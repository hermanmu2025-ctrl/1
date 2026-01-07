<?php
require_once 'functions.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

if ($action === 'subscribe') {
    // CSRF Check
    if (($_POST['csrf_token'] ?? '') !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Token']);
        exit;
    }

    $target_user_id = (int)$_POST['target_user_id'];
    $target_channel_id = $_POST['target_channel_id'];
    
    // 1. Validate Target Balance
    $targetUser = getUser($target_user_id);
    if ($targetUser['balance'] < PRICE_PER_SUB) {
        echo json_encode(['status' => 'error', 'message' => 'Misi ini sudah tidak valid (Saldo habis).']);
        exit;
    }

    // 2. Prevent Self-Sub
    if ($target_user_id == $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak bisa subscribe diri sendiri.']);
        exit;
    }

    // 3. Prevent Double Sub
    $check = $pdo->prepare("SELECT id FROM subscriptions WHERE subscriber_user_id = ? AND target_channel_id = ?");
    $check->execute([$user_id, $target_channel_id]);
    if($check->rowCount() > 0) {
         echo json_encode(['status' => 'error', 'message' => 'Anda sudah subscribe channel ini.']);
         exit;
    }

    try {
        $pdo->beginTransaction();

        // Record Subscription
        $stmt = $pdo->prepare("INSERT INTO subscriptions (subscriber_user_id, target_channel_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $target_channel_id]);

        // Deduct from Channel Owner
        $pdo->prepare("UPDATE users SET balance = balance - ?, total_subs_gained = total_subs_gained + 1 WHERE id = ?")
            ->execute([PRICE_PER_SUB, $target_user_id]);

        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'sub_expense', ?, ?, 'completed')")
            ->execute([$target_user_id, PRICE_PER_SUB, 'Subs expenses for channel ' . $target_channel_id]);

        // Add Reward to Subscriber
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
            ->execute([REWARD_PER_SUB, $user_id]);

        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'sub_income', ?, ?, 'completed')")
            ->execute([$user_id, REWARD_PER_SUB, 'Reward for subbing ' . $target_channel_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'System error. Try again.']);
    }

} elseif ($action === 'send_message') {
    $msg = trim($_POST['message']);
    if ($msg) {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $msg]);
        header("Location: dashboard.php?msg=sent");
    }
}
?>