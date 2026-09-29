<?php
/**
 * FF Panel Built-in PHP Server Router
 * Fully self-contained router & asset server
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . '/public' . $uri;

// 1. Static file with MIME header
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

    if ($ext === 'php') {
        require $file;
        return;
    }
}

// 2. Installation Check: Auto-redirect to installer if not installed
$isInstalled = file_exists(__DIR__ . '/config/installed.lock');
if (!$isInstalled && !str_starts_with($uri, '/install') && !str_starts_with($uri, '/css') && !str_starts_with($uri, '/js')) {
    header('Location: /install.php');
    exit;
}

// 2. Extensionless PHP route (e.g. /login -> /public/login.php, /user/dashboard -> /public/user/dashboard.php)
if (file_exists($file . '.php')) {
    require $file . '.php';
    return;
}

// 3. Directory index routing
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

// 4. Default fallback: Landing Page
require __DIR__ . '/public/index.php';
