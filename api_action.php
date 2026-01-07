<?php
require_once 'functions.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized Access']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

if ($action === 'subscribe') {
    // CSRF Protection
    if (($_POST['csrf_token'] ?? '') !== $_SESSION['csrf_token']) {
        echo json_encode(['status' => 'error', 'message' => 'Security Token Invalid']);
        exit;
    }

    $target_user_id = (int)$_POST['target_user_id'];
    $target_channel_id = $_POST['target_channel_id'];
    
    // Logic Checks
    $targetUser = getUser($target_user_id);
    if ($targetUser['balance'] < PRICE_PER_SUB) {
        echo json_encode(['status' => 'error', 'message' => 'Maaf, kuota kampanye channel ini sudah habis.']);
        exit;
    }

    if ($target_user_id == $user_id) {
        echo json_encode(['status' => 'error', 'message' => 'Tidak bisa subscribe diri sendiri.']);
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM subscriptions WHERE subscriber_user_id = ? AND target_channel_id = ?");
    $check->execute([$user_id, $target_channel_id]);
    if($check->rowCount() > 0) {
         echo json_encode(['status' => 'error', 'message' => 'Anda sudah subscribe channel ini sebelumnya.']);
         exit;
    }

    // Transaction
    try {
        $pdo->beginTransaction();

        // 1. Record Subscription
        $stmt = $pdo->prepare("INSERT INTO subscriptions (subscriber_user_id, target_channel_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $target_channel_id]);

        // 2. Charge Target
        $pdo->prepare("UPDATE users SET balance = balance - ?, total_subs_gained = total_subs_gained + 1 WHERE id = ?")
            ->execute([PRICE_PER_SUB, $target_user_id]);
        
        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'sub_expense', ?, ?, 'completed')")
            ->execute([$target_user_id, PRICE_PER_SUB, 'Campaign Cost for ' . $target_channel_id]);

        // 3. Reward Subscriber
        $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")
            ->execute([REWARD_PER_SUB, $user_id]);

        $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'sub_income', ?, ?, 'completed')")
            ->execute([$user_id, REWARD_PER_SUB, 'Reward for subbing ' . $target_channel_id]);

        $pdo->commit();
        echo json_encode(['status' => 'success']);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'System error: ' . $e->getMessage()]);
    }
}
?>