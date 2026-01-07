<?php
// CRON JOB: Run Hourly to check for Unsubscribes
require_once 'db.php';
require_once 'functions.php';

echo "Checking integrity...\n";

$stmt = $pdo->prepare("SELECT s.*, u.channel_id as sub_cid FROM subscriptions s JOIN users u ON s.subscriber_user_id = u.id WHERE s.status = 'active' ORDER BY s.check_timestamp ASC LIMIT 20");
$stmt->execute();
$subs = $stmt->fetchAll();

foreach ($subs as $sub) {
    $isSubbed = checkYoutubeSubscription($sub['sub_cid'], $sub['target_channel_id']);
    
    if (!$isSubbed) {
        echo "Unsub detected: User {$sub['subscriber_user_id']}\n";
        try {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE subscriptions SET status = 'unsubbed' WHERE id = ?")->execute([$sub['id']]);
            // Penalty
            $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([REWARD_PER_SUB, $sub['subscriber_user_id']]);
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'penalty', ?, 'Unsubscribe Penalty', 'completed')")
                ->execute([$sub['subscriber_user_id'], REWARD_PER_SUB]);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
        }
    } else {
        $pdo->prepare("UPDATE subscriptions SET check_timestamp = NOW() WHERE id = ?")->execute([$sub['id']]);
    }
}
echo "Done.";
?>