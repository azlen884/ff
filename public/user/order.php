<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

// Process Order Placement (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $serviceId = (int)($_POST['service_id'] ?? 0);
    $playerUid = trim($_POST['player_uid'] ?? '');
    $saveUid = !empty($_POST['save_uid']);

    if ($serviceId <= 0 || empty($playerUid)) {
        setFlash('error', 'Please select a valid service and provide your Free Fire Player UID.');
        header('Location: /user/dashboard.php');
        exit;
    }

    // Fetch service from DB
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ? AND is_active = 1");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        setFlash('error', 'The requested service is currently unavailable.');
        header('Location: /user/dashboard.php');
        exit;
    }

    $cost = (float)$service['price'];
    $currentBalance = (float)$currentUser['wallet_balance'];

    // Check Wallet Balance
    if ($currentBalance < $cost) {
        setFlash('error', 'Insufficient wallet balance. You need ' . formatCurrency($cost) . ' but your balance is ' . formatCurrency($currentBalance) . '. Please top up your wallet.');
        header('Location: /user/dashboard.php');
        exit;
    }

    // Execute MySQL Transaction for Atomic Balance Deduction & Order Creation
    try {
        $db->beginTransaction();

        // 1. Deduct user wallet balance
        $updateWallet = $db->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE id = ? AND wallet_balance >= ?");
        $updateWallet->execute([$cost, $currentUser['id'], $cost]);
        if ($updateWallet->rowCount() === 0) {
            throw new Exception("Wallet balance changed during checkout. Please try again.");
        }

        // 2. Generate unique order number (e.g. FF-2026-XXXX)
        $orderNumber = 'FF-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

        // 3. Insert real order record
        $orderStmt = $db->prepare("INSERT INTO orders (order_number, user_id, service_id, player_uid, player_name, amount, payment_method, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Wallet Balance', 'completed', NOW())");
        $orderStmt->execute([
            $orderNumber,
            $currentUser['id'],
            $service['id'],
            $playerUid,
            $currentUser['name'],
            $cost
        ]);
        $newOrderId = (int)$db->lastInsertId();

        // 4. Optionally save UID to saved_uids
        if ($saveUid) {
            $checkUid = $db->prepare("SELECT id FROM saved_uids WHERE user_id = ? AND uid_number = ?");
            $checkUid->execute([$currentUser['id'], $playerUid]);
            if (!$checkUid->fetch()) {
                $uidInsert = $db->prepare("INSERT INTO saved_uids (user_id, uid_number, player_name, region, is_default) VALUES (?, ?, ?, 'India Server', 0)");
                $uidInsert->execute([$currentUser['id'], $playerUid, $currentUser['name'] . '_FF']);
            }
        }

        $db->commit();

        setFlash('success', "Order #{$orderNumber} placed successfully! {$service['title']} has been processed for UID {$playerUid}.");
        header("Location: /order-detail?id={$newOrderId}");
        exit;

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        setFlash('error', 'Order processing failed: ' . $e->getMessage());
        header('Location: /dashboard');
        exit;
    }
}

// Fallback if accessed via GET
header('Location: /dashboard');
exit;
