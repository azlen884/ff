<?php
/**
 * FF Panel Built-in PHP Server Router
 * Fully self-contained router & asset server
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public' . $uri;

// 1. Static assets only (css, js, images, fonts)
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

// 2. Installation Check: Auto-redirect if not installed; block installer once installed
$isInstalled = file_exists(__DIR__ . '/config/installed.lock');

if (!$isInstalled) {
    // If not installed, redirect everything except installer and static assets to /install.php
    if (!str_starts_with($uri, '/install') && !str_starts_with($uri, '/css') && !str_starts_with($uri, '/js')) {
        header('Location: /install.php');
        exit;
    }
} else {
    // If already installed, block direct access to installer
    if (str_starts_with($uri, '/install') || $uri === '/install.php') {
        http_response_code(403);
        header('Location: /admin/login.php');
        exit;
    }
}

// 3. Exact PHP file (e.g. /public/install.php, /public/login.php)
if (file_exists($file) && is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
    require $file;
    return;
}

// 4. Extensionless PHP route (e.g. /login -> /public/login.php, /user/dashboard -> /public/user/dashboard.php)
if (file_exists($file . '.php')) {
    require $file . '.php';
    return;
}

// 5. Directory index routing
if (is_dir($file)) {
    if (file_exists($file . '/dashboard.php')) {
        require $file . '/dashboard.php';
        return;
    }
    if (file_exists($file . '/index.php')) {
        require $file . '/index.php';
        return;
    }
}

// 6. Default fallback: Landing Page
require __DIR__ . '/public/index.php';
