<?php
/**
 * FF Panel V2 - Order Placement & Coupon Validation Controller
 */
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

// Handle AJAX Coupon Validation
if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'validate_coupon') {
    header('Content-Type: application/json; charset=UTF-8');
    $code = trim($_REQUEST['coupon_code'] ?? '');
    $serviceId = (int)($_REQUEST['service_id'] ?? 0);

    $servicePrice = 0.00;
    if ($serviceId > 0) {
        $sStmt = $db->prepare("SELECT price FROM services WHERE id = ? AND is_active = 1");
        $sStmt->execute([$serviceId]);
        $servicePrice = (float)($sStmt->fetchColumn() ?: 0.00);
    }

    $res = validateCoupon($code, $servicePrice, $currentUser['id'], $db);
    echo json_encode([
        'valid' => $res['valid'],
        'discount' => $res['discount'],
        'discount_formatted' => formatCurrency($res['discount']),
        'final_amount' => max(0, $servicePrice - $res['discount']),
        'final_amount_formatted' => formatCurrency(max(0, $servicePrice - $res['discount'])),
        'message' => $res['valid'] ? "Coupon applied: Save " . formatCurrency($res['discount']) : $res['error']
    ]);
    exit;
}

// Process Order Placement (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $serviceId = (int)($_POST['service_id'] ?? 0);
    $playerUid = trim($_POST['player_uid'] ?? '');
    $couponCode = trim($_POST['coupon_code'] ?? '');
    $saveUid = !empty($_POST['save_uid']);

    if ($serviceId <= 0 || empty($playerUid)) {
        setFlash('error', 'Please select a valid service and provide your Free Fire Player UID.');
        header('Location: /dashboard');
        exit;
    }

    // Fetch service from DB
    $stmt = $db->prepare("SELECT s.*, c.name as category_name FROM services s JOIN categories c ON s.category_id = c.id WHERE s.id = ? AND s.is_active = 1");
    $stmt->execute([$serviceId]);
    $service = $stmt->fetch();

    if (!$service) {
        setFlash('error', 'The requested service is currently unavailable.');
        header('Location: /dashboard');
        exit;
    }

    $basePrice = (float)$service['price'];
    $discountAmount = 0.00;
    $couponId = null;

    // Validate coupon if provided
    if (!empty($couponCode)) {
        $couponCheck = validateCoupon($couponCode, $basePrice, $currentUser['id'], $db);
        if ($couponCheck['valid']) {
            $discountAmount = (float)$couponCheck['discount'];
            $couponId = (int)$couponCheck['coupon']['id'];
        } else {
            setFlash('error', 'Coupon Error: ' . $couponCheck['error']);
            header('Location: /dashboard');
            exit;
        }
    }

    $finalAmount = max(0.00, $basePrice - $discountAmount);

    // Refresh User Wallet Balance
    $uStmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
    $uStmt->execute([$currentUser['id']]);
    $currentBalance = (float)$uStmt->fetchColumn();

    if ($currentBalance < $finalAmount) {
        setFlash('error', 'Insufficient wallet balance. Total required: ' . formatCurrency($finalAmount) . ', Available: ' . formatCurrency($currentBalance) . '. Please top up your wallet.');
        header('Location: /wallet');
        exit;
    }

    // Execute MySQL Transaction for Atomic Balance Deduction & Order Creation
    try {
        $db->beginTransaction();

        // 1. Generate unique order number
        $orderNumber = 'FF-' . date('Y') . '-' . strtoupper(substr(uniqid(), -6));

        // 2. Deduct wallet balance via recordWalletTransaction with row lock
        recordWalletTransaction(
            $currentUser['id'],
            'debit',
            $finalAmount,
            "Order #{$orderNumber} - {$service['title']} (UID: {$playerUid})",
            $orderNumber,
            $db
        );

        // 3. Increment coupon usage if used
        if ($couponId) {
            $cStmt = $db->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE id = ?");
            $cStmt->execute([$couponId]);
        }

        // 4. Optionally save UID to saved_uids
        if ($saveUid) {
            $checkUid = $db->prepare("SELECT id FROM saved_uids WHERE user_id = ? AND uid_number = ?");
            $checkUid->execute([$currentUser['id'], $playerUid]);
            if (!$checkUid->fetch()) {
                $uidInsert = $db->prepare("INSERT INTO saved_uids (user_id, uid_number, player_name, region, is_default) VALUES (?, ?, ?, 'India Server', 0)");
                $uidInsert->execute([$currentUser['id'], $playerUid, $currentUser['name'] . '_FF']);
            }
        }

        // 5. Check Provider Configuration for automated fulfillment
        $orderStatus = 'pending';
        $providerId = $service['provider_id'] ?? null;
        $providerOrderId = null;
        $providerResponse = null;
        $adminNote = null;

        $settings = getSiteSettings();
        $autoOrderEnabled = !empty($settings['auto_order_enabled']) && $settings['auto_order_enabled'] === '1';

        if ($providerId && $autoOrderEnabled) {
            $pStmt = $db->prepare("SELECT * FROM providers WHERE id = ?");
            $pStmt->execute([$providerId]);
            $provider = $pStmt->fetch();

            if ($provider && !empty($provider['is_enabled']) && !empty($provider['api_url']) && !empty($provider['api_key'])) {
                // Execute real provider API call
                $dispatch = dispatchProviderOrder($provider, $service, $playerUid, $orderNumber);
                $orderStatus = $dispatch['status'];
                $providerOrderId = $dispatch['provider_order_id'];
                $providerResponse = $dispatch['raw_response'];
                $adminNote = $dispatch['message'];
            } else {
                $adminNote = 'Queued for fulfillment (External Provider disabled or missing API key).';
            }
        } else {
            $adminNote = 'Order queued for processing. Admin will verify and deliver diamonds.';
        }

        // 6. Insert real order record into MySQL
        $orderStmt = $db->prepare("INSERT INTO orders (order_number, user_id, service_id, provider_id, provider_order_id, player_uid, player_name, amount, coupon_id, discount_amount, payment_method, status, admin_note, provider_response, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Wallet Balance', ?, ?, ?, NOW())");
        $orderStmt->execute([
            $orderNumber,
            $currentUser['id'],
            $service['id'],
            $providerId,
            $providerOrderId,
            $playerUid,
            $currentUser['name'],
            $finalAmount,
            $couponId,
            $discountAmount,
            $orderStatus,
            $adminNote,
            $providerResponse
        ]);
        $newOrderId = (int)$db->lastInsertId();

        // 7. Process Referral Commission if applicable
        processReferralCommission($newOrderId, $currentUser['id'], $finalAmount, $db);

        // 8. Notifications
        createNotification(
            $currentUser['id'],
            "Order #{$orderNumber} Placed",
            "Your order for {$service['title']} (UID: {$playerUid}) for " . formatCurrency($finalAmount) . " has been placed. Status: " . ucfirst($orderStatus),
            $orderStatus === 'completed' ? 'success' : 'info',
            "/order-detail?id={$newOrderId}",
            "user",
            $db
        );

        createNotification(
            null,
            "New Order #{$orderNumber}",
            "User {$currentUser['name']} ordered {$service['title']} (UID: {$playerUid}) for " . formatCurrency($finalAmount),
            "info",
            "/admin/orders",
            "admin",
            $db
        );

        $db->commit();

        $statusMsg = $orderStatus === 'completed' 
            ? "Order #{$orderNumber} completed instantly via game server!" 
            : "Order #{$orderNumber} placed successfully! Diamonds will be delivered to UID {$playerUid} shortly.";
        setFlash('success', $statusMsg);
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
