<?php
/**
 * FF Panel Database Connection
 * Real MySQL Connection via PDO
 */

function getDbConnection(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    // Load from installer configuration if present
    $configFile = __DIR__ . '/database_config.php';
    if (file_exists($configFile)) {
        $dbConfig = require $configFile;
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $dbname = $dbConfig['dbname'] ?? 'ffpanel';
        $username = $dbConfig['username'] ?? 'root';
        $password = $dbConfig['password'] ?? '';
    } else {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $dbname = getenv('DB_NAME') ?: 'ffpanel';
        $username = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASS') ?: '';
    }

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, $username, $password, $options);
        return $pdo;
    } catch (PDOException $e) {
        // Fallback with ffpanel user if root requires specific credentials
        try {
            $pdo = new PDO($dsn, 'ffpanel', 'ffpanel_password', $options);
            return $pdo;
        } catch (PDOException $ex) {
            die("<div style='background:#111827;color:#f87171;padding:24px;font-family:sans-serif;border-radius:8px;max-width:600px;margin:40px auto;border:1px solid #dc2626;'>
                <h3 style='margin-top:0;'>Database Connection Error</h3>
                <p>Could not connect to MySQL database.</p>
                <p style='color:#9ca3af;font-size:12px;'>Error: " . htmlspecialchars($ex->getMessage()) . "</p>
                <p style='margin-top:16px;'><a href='/install.php' style='color:#60a5fa;'>Run Web Installer &rarr;</a></p>
            </div>");
        }
    }
}
