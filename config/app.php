<?php
/**
 * FF Panel Application Config & Helpers
 */

if (session_status() === PHP_SESSION_NONE) {
    // Secure session cookies
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/provider.php';

// Check whether application is already installed
function isInstalled(): bool {
    return file_exists(__DIR__ . '/installed.lock');
}

// Generate CSRF Token
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Field output
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

// Verify CSRF Token
function verifyCsrfToken(): bool {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die("Security Error: Invalid or expired CSRF token. Please refresh the page and try again.");
        }
    }
    return true;
}

// Flash Message Helpers
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type' => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Get Current Logged In User (Always fresh from DB)
function getCurrentUser(): ?array {
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, referral_code, referred_by, wallet_balance, status, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user || $user['status'] === 'suspended') {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_role']);
        return null;
    }
    return $user;
}

// Get Current Logged In Admin
function getCurrentAdmin(): ?array {
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT id, name, email, phone, role, wallet_balance, status, created_at FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    if (!$admin || $admin['status'] === 'suspended') {
        unset($_SESSION['admin_id']);
        return null;
    }
    return $admin;
}

// Auth Protection Guards
function requireUser(): array {
    $user = getCurrentUser();
    if (!$user) {
        setFlash('error', 'Please log in to access your user panel.');
        header('Location: /login');
        exit;
    }
    return $user;
}

function requireAdmin(): array {
    $admin = getCurrentAdmin();
    if (!$admin) {
        setFlash('error', 'Administrator credentials required to access this panel.');
        header('Location: /admin/login');
        exit;
    }
    return $admin;
}

function guestOnly(): void {
    if (getCurrentUser()) {
        header('Location: /dashboard');
        exit;
    }
}

// Format Currency
function formatCurrency(float $amount): string {
    return '₹' . number_format($amount, 2);
}

// Load Site Settings from DB
function getSiteSettings(): array {
    static $settings = null;
    if ($settings !== null) {
        return $settings;
    }
    try {
        $db = getDbConnection();
        $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        $settings = [
            'site_name' => 'FF Panel Store',
            'site_tagline' => 'Fast • Safe • Reliable',
            'currency_symbol' => '₹',
            'announcement' => 'Flash Sale: Top up diamonds instantly with 0% gateway fee today!',
            'support_email' => 'support@ffpanel.com',
            'support_whatsapp' => '+91 98765 43210'
        ];
    }
    return $settings;
}

/**
 * V2 Helper: Record Real Wallet Transaction with balance calculation & row lock
 */
function recordWalletTransaction(int $userId, string $type, float $amount, string $description, ?string $referenceId = null, ?PDO $pdo = null): array {
    $db = $pdo ?? getDbConnection();
    $manageTxn = !$db->inTransaction();
    if ($manageTxn) {
        $db->beginTransaction();
    }

    try {
        // Lock row to prevent race conditions
        $stmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
        $stmt->execute([$userId]);
        $currentBalance = $stmt->fetchColumn();

        if ($currentBalance === false) {
            throw new Exception("User not found.");
        }

        $balanceBefore = (float)$currentBalance;

        if ($type === 'debit') {
            if ($balanceBefore < $amount) {
                throw new Exception("Insufficient wallet balance. Required: " . formatCurrency($amount) . ", Available: " . formatCurrency($balanceBefore));
            }
            $balanceAfter = $balanceBefore - $amount;
        } elseif ($type === 'credit') {
            $balanceAfter = $balanceBefore + $amount;
        } else {
            throw new Exception("Invalid wallet transaction type.");
        }

        // Update User Wallet Balance
        $updateStmt = $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
        $updateStmt->execute([$balanceAfter, $userId]);

        // Record in wallet_transactions table
        $insStmt = $db->prepare("INSERT INTO wallet_transactions (user_id, type, amount, balance_before, balance_after, description, reference_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $insStmt->execute([$userId, $type, $amount, $balanceBefore, $balanceAfter, $description, $referenceId]);

        if ($manageTxn) {
            $db->commit();
        }

        return [
            'success' => true,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'amount' => $amount
        ];
    } catch (Exception $e) {
        if ($manageTxn && $db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}

/**
 * V2 Helper: Create Database Notification
 */
function createNotification(?int $userId, string $title, string $message, string $type = 'info', ?string $link = null, string $roleTarget = 'user', ?PDO $pdo = null): bool {
    try {
        $db = $pdo ?? getDbConnection();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, role_target, title, message, type, is_read, link, created_at) VALUES (?, ?, ?, ?, ?, 0, ?, NOW())");
        return $stmt->execute([$userId, $roleTarget, $title, $message, $type, $link]);
    } catch (Exception $e) {
        return false;
    }
}

/**
 * V2 Helper: Get Unread Notification Count
 */
function getUnreadNotificationCount(?int $userId, string $role = 'user'): int {
    try {
        $db = getDbConnection();
        if ($userId) {
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE (user_id = ? OR (user_id IS NULL AND role_target = ?)) AND is_read = 0");
            $stmt->execute([$userId, $role]);
            return (int)$stmt->fetchColumn();
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE role_target = 'admin' AND is_read = 0");
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        }
    } catch (Exception $e) {
        return 0;
    }
}

/**
 * V2 Helper: Validate Coupon Code Server-Side
 */
function validateCoupon(string $code, float $orderAmount, ?int $userId = null, ?PDO $pdo = null): array {
    $code = trim(strtoupper($code));
    if (empty($code)) {
        return ['valid' => false, 'coupon' => null, 'discount' => 0.00, 'error' => 'Please enter a coupon code.'];
    }

    $db = $pdo ?? getDbConnection();
    $stmt = $db->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1");
    $stmt->execute([$code]);
    $coupon = $stmt->fetch();

    if (!$coupon) {
        return ['valid' => false, 'coupon' => null, 'discount' => 0.00, 'error' => 'Coupon code is invalid or expired.'];
    }

    // Check Expiration Date
    if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < strtotime(date('Y-m-d'))) {
        return ['valid' => false, 'coupon' => null, 'discount' => 0.00, 'error' => 'This coupon has expired on ' . date('d M Y', strtotime($coupon['expires_at'])) . '.'];
    }

    // Check Usage Limit
    if ($coupon['usage_limit'] > 0 && $coupon['used_count'] >= $coupon['usage_limit']) {
        return ['valid' => false, 'coupon' => null, 'discount' => 0.00, 'error' => 'This coupon has reached its maximum global usage limit.'];
    }

    // Check Minimum Order Amount
    if ($coupon['min_order_amount'] > 0 && $orderAmount < (float)$coupon['min_order_amount']) {
        return [
            'valid' => false, 
            'coupon' => null, 
            'discount' => 0.00, 
            'error' => 'Minimum order amount for this coupon is ' . formatCurrency((float)$coupon['min_order_amount']) . '.'
        ];
    }

    // Calculate Discount
    $discount = 0.00;
    if ($coupon['discount_type'] === 'percentage') {
        $discount = round($orderAmount * ((float)$coupon['discount_value'] / 100), 2);
        if (!empty($coupon['max_discount_amount']) && $coupon['max_discount_amount'] > 0) {
            $discount = min($discount, (float)$coupon['max_discount_amount']);
        }
    } else {
        // Flat discount
        $discount = (float)$coupon['discount_value'];
    }

    $discount = min($discount, $orderAmount);

    return [
        'valid' => true,
        'coupon' => $coupon,
        'discount' => $discount,
        'error' => null
    ];
}

/**
 * V2 Helper: Process Referral Commission
 */
function processReferralCommission(int $orderId, int $userId, float $orderAmount, ?PDO $pdo = null): ?float {
    $settings = getSiteSettings();
    if (empty($settings['referral_enabled']) || $settings['referral_enabled'] !== '1') {
        return null;
    }

    $commissionPercent = (float)($settings['referral_commission_percent'] ?? 5.00);
    if ($commissionPercent <= 0) {
        return null;
    }

    $db = $pdo ?? getDbConnection();
    
    // Check if user has a referrer
    $uStmt = $db->prepare("SELECT referred_by, name FROM users WHERE id = ?");
    $uStmt->execute([$userId]);
    $uData = $uStmt->fetch();

    if (!$uData || empty($uData['referred_by'])) {
        return null;
    }

    $referrerId = (int)$uData['referred_by'];
    if ($referrerId === $userId) {
        return null;
    }

    // Verify referrer exists and is active
    $rStmt = $db->prepare("SELECT id, name, status FROM users WHERE id = ?");
    $rStmt->execute([$referrerId]);
    $referrer = $rStmt->fetch();

    if (!$referrer || $referrer['status'] !== 'active') {
        return null;
    }

    $commissionAmount = round($orderAmount * ($commissionPercent / 100), 2);
    if ($commissionAmount <= 0) {
        return null;
    }

    try {
        // Record commission in referral_commissions
        $insStmt = $db->prepare("INSERT INTO referral_commissions (referrer_id, referred_user_id, order_id, commission_amount, commission_percentage, status, created_at) VALUES (?, ?, ?, ?, ?, 'credited', NOW())");
        $insStmt->execute([$referrerId, $userId, $orderId, $commissionAmount, $commissionPercent]);

        // Credit Referrer's Wallet
        recordWalletTransaction(
            $referrerId,
            'credit',
            $commissionAmount,
            "Referral Commission ({$commissionPercent}%) from {$uData['name']}'s order #{$orderId}",
            "REF-ORD-{$orderId}",
            $db
        );

        // Notify Referrer
        createNotification(
            $referrerId,
            "Referral Bonus Received!",
            "You received " . formatCurrency($commissionAmount) . " referral reward from your invited friend.",
            "success",
            "/referrals",
            "user",
            $db
        );

        return $commissionAmount;
    } catch (Exception $e) {
        error_log("Referral commission error: " . $e->getMessage());
        return null;
    }
}

