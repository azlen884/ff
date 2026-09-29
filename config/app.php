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
    $stmt = $db->prepare("SELECT id, name, email, phone, role, wallet_balance, status, created_at FROM users WHERE id = ?");
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
        header('Location: /login.php');
        exit;
    }
    return $user;
}

function requireAdmin(): array {
    $admin = getCurrentAdmin();
    if (!$admin) {
        setFlash('error', 'Administrator credentials required to access this panel.');
        header('Location: /admin/login.php');
        exit;
    }
    return $admin;
}

function guestOnly(): void {
    if (getCurrentUser()) {
        header('Location: /user/dashboard.php');
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
