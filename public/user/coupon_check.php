<?php
/**
 * FF Panel V2 - AJAX Server-Side Coupon Checker
 */
require_once __DIR__ . '/../../config/app.php';

header('Content-Type: application/json; charset=UTF-8');

$currentUser = getCurrentUser();
if (!$currentUser) {
    http_response_code(401);
    echo json_encode(['valid' => false, 'error' => 'Please sign in to apply coupons.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['valid' => false, 'error' => 'Method not allowed.']);
    exit;
}

$code = trim($_POST['code'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);

if (empty($code)) {
    echo json_encode(['valid' => false, 'error' => 'Please enter a coupon code.']);
    exit;
}

if ($amount <= 0) {
    echo json_encode(['valid' => false, 'error' => 'Invalid order amount for coupon validation.']);
    exit;
}

$result = validateCoupon($code, $amount, $currentUser['id']);
echo json_encode($result);
exit;
