<?php
// This script should be run via Cron Job (e.g., every 1 hour)
// Example: 0 * * * * /usr/bin/php /path/to/cron_protect.php

require_once 'db.php';
require_once 'functions.php';

echo "Starting Protection Protocol...\n";

// Get active subscriptions to check
// To save API quota, we can limit to checking 50 oldest unchecked records
$stmt = $pdo->prepare("SELECT s.*, u.channel_id as subscriber_channel_id 
                       FROM subscriptions s 
                       JOIN users u ON s.subscriber_user_id = u.id 
                       WHERE s.status = 'active' 
                       ORDER BY s.check_timestamp ASC LIMIT 50");
$stmt->execute();
$subs_to_check = $stmt->fetchAll();

foreach ($subs_to_check as $sub) {
    $subscriber_id = $sub['subscriber_user_id'];
    $subscriber_channel_id = $sub['subscriber_channel_id'];
    $target_channel_id = $sub['target_channel_id'];

    // Verify via API
    $is_still_subbed = checkYoutubeSubscription($subscriber_channel_id, $target_channel_id);

    if (!$is_still_subbed) {
        // PENALTY LOGIC
        echo "User $subscriber_id unsubscribed from $target_channel_id. Applying Penalty.\n";

        try {
            $pdo->beginTransaction();

            // 1. Mark as unsubbed
            $pdo->prepare("UPDATE subscriptions SET status = 'unsubbed', check_timestamp = NOW() WHERE id = ?")
                ->execute([$sub['id']]);

            // 2. Remove reward from balance (Chargeback)
            // We deduct the reward they got (52.5) or more as penalty
            $penalty_amount = REWARD_PER_SUB;
            $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")
                ->execute([$penalty_amount, $subscriber_id]);

            // 3. Log transaction
            $pdo->prepare("INSERT INTO transactions (user_id, type, amount, description, status) VALUES (?, 'penalty', ?, 'Penalty for unsubscribing', 'completed')")
                ->execute([$subscriber_id, $penalty_amount]);

            // 4. Send Warning Message
            $msg = "Sistem mendeteksi Anda melakukan Unsubscribe pada channel $target_channel_id. Saldo Anda telah dipotong sebesar " . formatRupiah($penalty_amount);
            $pdo->prepare("INSERT INTO messages (user_id, message, status) VALUES (?, ?, 'closed')")
                ->execute([$subscriber_id, $msg]);

            $pdo->commit();

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error applying penalty: " . $e->getMessage() . "\n";
        }
    } else {
        // Just update timestamp
        $pdo->prepare("UPDATE subscriptions SET check_timestamp = NOW() WHERE id = ?")
            ->execute([$sub['id']]);
    }
}

echo "Protection Protocol Completed.";
?>