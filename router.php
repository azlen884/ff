<?php
/**
 * FF Panel Built-in PHP Server Router
 * Fully self-contained router, asset server, and clean URL handler
 */

$rawUri = $_SERVER['REQUEST_URI'];
$uri = parse_url($rawUri, PHP_URL_PATH);
$file = __DIR__ . '/public' . $uri;

// 1. Security Guard: Block direct access to sensitive system paths & files
$blockedPatterns = [
    '/^\/\.env/i',
    '/^\/config(\/|$)/i',
    '/^\/database(\/|$)/i',
    '/^\/includes(\/|$)/i',
    '/^\/src(\/|$)/i',
    '/\.(lock|sql|json|md|log|sh|yml|yaml|bak|example)$/i',
];
foreach ($blockedPatterns as $pattern) {
    if (preg_match($pattern, $uri)) {
        http_response_code(403);
        require __DIR__ . '/public/404.php';
        return;
    }
}

// 2. Static Assets (CSS, JS, Images, Web Fonts)
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $mimes = [
        'css'   => 'text/css; charset=UTF-8',
        'js'    => 'application/javascript; charset=UTF-8',
        'json'  => 'application/json; charset=UTF-8',
        'png'   => 'image/png',
        'jpg'   => 'image/jpeg',
        'jpeg'  => 'image/jpeg',
        'gif'   => 'image/gif',
        'svg'   => 'image/svg+xml',
        'ico'   => 'image/x-icon',
        'webp'  => 'image/webp',
        'woff'  => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf'   => 'font/ttf',
    ];

    if (isset($mimes[$ext])) {
        header("Content-Type: " . $mimes[$ext]);
        header("Cache-Control: public, max-age=86400");
        readfile($file);
        return;
    }
}

// 3. Installation Check
$isInstalled = file_exists(__DIR__ . '/config/installed.lock');

if (!$isInstalled) {
    // If not installed, redirect everything except installer and static assets to /install
    if (!str_starts_with($uri, '/install') && !str_starts_with($uri, '/css') && !str_starts_with($uri, '/js')) {
        header('Location: /install');
        exit;
    }
} else {
    // If already installed, block direct access to installer routes
    if (str_starts_with($uri, '/install') || $uri === '/install.php') {
        http_response_code(403);
        header('Location: /admin/login');
        exit;
    }
}

// 4. Redirect legacy .php URLs to clean URLs on GET/HEAD (prevents duplicate URLs & loops)
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (($method === 'GET' || $method === 'HEAD') && str_ends_with($uri, '.php')) {
    $legacyMap = [
        '/index.php'              => '/',
        '/login.php'              => '/login',
        '/register.php'           => '/register',
        '/logout.php'             => '/logout',
        '/install.php'            => '/install',
        '/404.php'                => '/404',
        '/user/dashboard.php'     => '/dashboard',
        '/user/orders.php'        => '/orders',
        '/user/order.php'         => '/order',
        '/user/order-detail.php'  => '/order-detail',
        '/user/services.php'      => '/services',
        '/user/uids.php'          => '/uids',
        '/user/profile.php'       => '/profile',
        '/user/wallet.php'        => '/wallet',
        '/user/deposit.php'       => '/deposit',
        '/user/notifications.php' => '/notifications',
        '/user/referrals.php'     => '/referrals',
        '/admin/dashboard.php'    => '/admin',
        '/admin/login.php'        => '/admin/login',
        '/admin/logout.php'       => '/admin/logout',
        '/admin/orders.php'       => '/admin/orders',
        '/admin/services.php'     => '/admin/services',
        '/admin/users.php'        => '/admin/users',
        '/admin/settings.php'     => '/admin/settings',
        '/admin/gateways.php'     => '/admin/gateways',
        '/admin/providers.php'    => '/admin/providers',
        '/admin/payments.php'     => '/admin/payments',
        '/admin/coupons.php'      => '/admin/coupons',
        '/admin/referrals.php'    => '/admin/referrals',
        '/admin/wallet-transactions.php' => '/admin/wallet-transactions',
        '/admin/notifications.php'=> '/admin/notifications',
    ];
    $cleanDest = $legacyMap[$uri] ?? preg_replace('/\.php$/', '', $uri);
    $query = parse_url($rawUri, PHP_URL_QUERY);
    if (!empty($query)) {
        $cleanDest .= '?' . $query;
    }
    header("Location: {$cleanDest}", true, 301);
    exit;
}

// 5. Clean Route Dictionary
$routes = [
    '/'                    => '/public/index.php',
    '/login'               => '/public/login.php',
    '/register'            => '/public/register.php',
    '/logout'              => '/public/logout.php',
    '/install'             => '/public/install.php',
    '/installer'           => '/public/install.php',
    '/404'                 => '/public/404.php',

    // User Portal Clean Routes (Root style & /user prefix style)
    '/dashboard'           => '/public/user/dashboard.php',
    '/orders'              => '/public/user/orders.php',
    '/order'               => '/public/user/order.php',
    '/order-detail'        => '/public/user/order-detail.php',
    '/services'            => '/public/user/services.php',
    '/uids'                => '/public/user/uids.php',
    '/profile'             => '/public/user/profile.php',
    '/wallet'              => '/public/user/wallet.php',
    '/deposit'             => '/public/user/deposit.php',
    '/notifications'       => '/public/user/notifications.php',
    '/referrals'           => '/public/user/referrals.php',

    '/user'                => '/public/user/dashboard.php',
    '/user/'               => '/public/user/dashboard.php',
    '/user/dashboard'      => '/public/user/dashboard.php',
    '/user/orders'         => '/public/user/orders.php',
    '/user/order'          => '/public/user/order.php',
    '/user/order-detail'   => '/public/user/order-detail.php',
    '/user/services'       => '/public/user/services.php',
    '/user/uids'           => '/public/user/uids.php',
    '/user/profile'        => '/public/user/profile.php',
    '/user/wallet'         => '/public/user/wallet.php',
    '/user/deposit'        => '/public/user/deposit.php',
    '/user/notifications'  => '/public/user/notifications.php',
    '/user/referrals'      => '/public/user/referrals.php',

    // Admin Portal Clean Routes
    '/admin'                     => '/public/admin/dashboard.php',
    '/admin/'                    => '/public/admin/dashboard.php',
    '/admin/dashboard'           => '/public/admin/dashboard.php',
    '/admin/login'               => '/public/admin/login.php',
    '/admin/logout'              => '/public/admin/logout.php',
    '/admin/orders'              => '/public/admin/orders.php',
    '/admin/services'            => '/public/admin/services.php',
    '/admin/users'               => '/public/admin/users.php',
    '/admin/settings'            => '/public/admin/settings.php',
    '/admin/gateways'            => '/public/admin/gateways.php',
    '/admin/providers'           => '/public/admin/providers.php',
    '/admin/payments'            => '/public/admin/payments.php',
    '/admin/coupons'             => '/public/admin/coupons.php',
    '/admin/referrals'           => '/public/admin/referrals.php',
    '/admin/wallet-transactions' => '/public/admin/wallet-transactions.php',
    '/admin/notifications'       => '/public/admin/notifications.php',
];

// Check if matched in route dictionary
if (isset($routes[$uri])) {
    require __DIR__ . $routes[$uri];
    return;
}

// 6. Direct execution of files if requested via POST (e.g. form handlers)
if (file_exists($file) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    require $file;
    return;
}

// 7. Fallback for clean URLs matching .php in public
if (file_exists($file . '.php') && is_file($file . '.php')) {
    require $file . '.php';
    return;
}

// 8. 404 Handler for all invalid or unrecognized routes
http_response_code(404);
require __DIR__ . '/public/404.php';
