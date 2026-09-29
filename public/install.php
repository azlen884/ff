<?php
/**
 * FF Panel Store - Web Installation & Setup Wizard
 * Self-contained, secure browser installer
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    session_start();
}

$lockFile = __DIR__ . '/../config/installed.lock';
$isInstalled = file_exists($lockFile);

// Handle AJAX Test Database Connection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'test_db') {
    header('Content-Type: application/json; charset=UTF-8');
    if ($isInstalled) {
        echo json_encode(['success' => false, 'error' => 'Installation is locked.']);
        exit;
    }

    $host = trim($_POST['db_host'] ?? '127.0.0.1');
    $port = trim($_POST['db_port'] ?? '3306');
    $dbname = trim($_POST['db_name'] ?? 'ffpanel');
    $username = trim($_POST['db_user'] ?? 'root');
    $password = $_POST['db_pass'] ?? '';

    try {
        $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
        $testPdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 4
        ]);
        echo json_encode(['success' => true, 'message' => "Connected to MySQL server successfully!"]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

$error = '';
$success = false;
$installedDetails = [];

// Handle Full Installation Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    if ($isInstalled) {
        $error = 'Application is already installed and protected by security lock.';
    } else {
        $dbHost = trim($_POST['db_host'] ?? '127.0.0.1');
        $dbPort = trim($_POST['db_port'] ?? '3306');
        $dbName = trim($_POST['db_name'] ?? 'ffpanel');
        $dbUser = trim($_POST['db_user'] ?? 'root');
        $dbPass = $_POST['db_pass'] ?? '';

        $adminName = trim($_POST['admin_name'] ?? '');
        $adminEmail = trim(strtolower($_POST['admin_email'] ?? ''));
        $adminPass = $_POST['admin_pass'] ?? '';
        $adminPassConfirm = $_POST['admin_pass_confirm'] ?? '';

        // Validation
        if (empty($dbHost) || empty($dbName) || empty($dbUser)) {
            $error = 'Please provide complete database connection details.';
        } elseif (empty($adminName) || empty($adminEmail) || empty($adminPass)) {
            $error = 'Please fill in all administrator account fields.';
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid administrator email address.';
        } elseif (strlen($adminPass) < 6) {
            $error = 'Admin password must be at least 6 characters in length.';
        } elseif ($adminPass !== $adminPassConfirm) {
            $error = 'Administrator passwords do not match. Please re-enter.';
        } else {
            try {
                // 1. Connect to MySQL server
                $serverDsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
                $pdo = new PDO($serverDsn, $dbUser, $dbPass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);

                // 2. Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$dbName}`");

                // Helper to execute SQL scripts safely statement-by-statement
                $runSqlScript = function(PDO $db, string $filePath, string $targetDb) {
                    if (!file_exists($filePath)) {
                        return;
                    }
                    $db->exec("USE `{$targetDb}`");
                    $content = file_get_contents($filePath);
                    $lines = explode("\n", $content);
                    $query = '';
                    foreach ($lines as $line) {
                        $trimmed = trim($line);
                        if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                            continue;
                        }
                        if (stripos($trimmed, 'CREATE DATABASE') === 0 || stripos($trimmed, 'USE ') === 0) {
                            continue;
                        }
                        $query .= $line . "\n";
                        if (str_ends_with($trimmed, ';')) {
                            $stmtText = trim($query);
                            if (!empty($stmtText)) {
                                $db->exec($stmtText);
                            }
                            $query = '';
                        }
                    }
                };

                // 3. Import Schema
                $schemaFile = __DIR__ . '/../database/schema.sql';
                $runSqlScript($pdo, $schemaFile, $dbName);

                // 4. Import Seed data (categories, initial products, etc.)
                $seedFile = __DIR__ . '/../database/seed.sql';
                $runSqlScript($pdo, $seedFile, $dbName);

                // 5. Create / Update Master Administrator Account
                $adminHash = password_hash($adminPass, PASSWORD_BCRYPT);
                $adminStmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role, wallet_balance, status) 
                                            VALUES (1, ?, ?, ?, 'admin', 50000.00, 'active') 
                                            ON DUPLICATE KEY UPDATE name = VALUES(name), email = VALUES(email), password = VALUES(password), status = 'active'");
                $adminStmt->execute([$adminName, $adminEmail, $adminHash]);

                // 6. Save database configuration file
                $configContent = "<?php\n"
                    . "// FF Panel Database Configuration\n"
                    . "// Generated automatically by Web Installer on " . date('Y-m-d H:i:s') . "\n\n"
                    . "return [\n"
                    . "    'host'     => " . var_export($dbHost, true) . ",\n"
                    . "    'port'     => " . var_export($dbPort, true) . ",\n"
                    . "    'dbname'   => " . var_export($dbName, true) . ",\n"
                    . "    'username' => " . var_export($dbUser, true) . ",\n"
                    . "    'password' => " . var_export($dbPass, true) . ",\n"
                    . "];\n";

                file_put_contents(__DIR__ . '/../config/database_config.php', $configContent);

                // 7. Write Installation Lock
                $lockContent = "INSTALLED=true\n"
                    . "TIMESTAMP=" . date('c') . "\n"
                    . "DB_NAME={$dbName}\n"
                    . "ADMIN_EMAIL={$adminEmail}\n";
                file_put_contents($lockFile, $lockContent);

                $success = true;
                $isInstalled = true;
                $installedDetails = [
                    'admin_email' => $adminEmail,
                    'db_name' => $dbName,
                    'db_host' => $dbHost,
                ];

            } catch (Exception $e) {
                $error = 'Installation Error: ' . $e->getMessage();
            }
        }
    }
}

// System Requirements Check
$reqs = [
    'PHP Version (>= 8.1)' => [
        'status' => version_compare(PHP_VERSION, '8.1.0', '>='),
        'detail' => PHP_VERSION
    ],
    'PDO Extension' => [
        'status' => extension_loaded('pdo'),
        'detail' => extension_loaded('pdo') ? 'Installed' : 'Missing'
    ],
    'PDO MySQL Driver' => [
        'status' => extension_loaded('pdo_mysql'),
        'detail' => extension_loaded('pdo_mysql') ? 'Installed' : 'Missing'
    ],
    'OpenSSL / BCrypt' => [
        'status' => extension_loaded('openssl') && defined('PASSWORD_BCRYPT'),
        'detail' => 'Enabled'
    ],
    'Config Directory Writable' => [
        'status' => is_writable(__DIR__ . '/../config'),
        'detail' => is_writable(__DIR__ . '/../config') ? 'Writable' : 'Read-only'
    ],
];
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#080B11] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Web Installer & Setup — FF Panel Store</title>
    <link rel="stylesheet" href="/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-gaming { font-family: 'Rajdhani', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-[#080B11] text-slate-200 antialiased selection:bg-rose-600 selection:text-white flex flex-col items-center justify-center p-4 sm:p-6 lg:p-10">

    <div class="max-w-2xl w-full space-y-6">
        
        <!-- Brand Header -->
        <div class="text-center space-y-2">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-[#FF2E51] to-red-700 shadow-xl shadow-rose-600/30 mb-2">
                <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                </svg>
            </div>
            <div class="font-gaming text-2xl font-bold tracking-wider text-white uppercase">
                FF PANEL <span class="text-[#FF2E51]">STORE</span>
            </div>
            <h1 class="text-lg font-bold text-white tracking-wide">Web Installation & Setup Wizard</h1>
            <p class="text-xs text-slate-400">Automated database schema migration & administrator account provisioning.</p>
        </div>

        <?php if ($success): ?>
            <!-- Success Screen -->
            <div class="bg-[#0D121F] border border-emerald-500/40 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 animate-in fade-in zoom-in duration-300">
                <div class="text-center space-y-3">
                    <div class="w-16 h-16 mx-auto rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-3xl font-black">
                        ✓
                    </div>
                    <h2 class="text-2xl font-extrabold text-white">Installation Completed!</h2>
                    <p class="text-xs text-slate-300 max-w-md mx-auto">
                        Your MySQL database tables have been created and your administrator credentials have been configured.
                    </p>
                </div>

                <div class="bg-[#13192A] border border-slate-800 rounded-2xl p-4 text-xs space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Database Name:</span>
                        <strong class="text-white font-mono"><?= htmlspecialchars($installedDetails['db_name']) ?></strong>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Database Host:</span>
                        <span class="text-slate-300 font-mono"><?= htmlspecialchars($installedDetails['db_host']) ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Admin Account:</span>
                        <strong class="text-rose-400"><?= htmlspecialchars($installedDetails['admin_email']) ?></strong>
                    </div>
                    <div class="flex items-center justify-between pt-1 border-t border-slate-800">
                        <span class="text-slate-400">Security Lock:</span>
                        <span class="text-emerald-400 font-semibold">Enabled (config/installed.lock created)</span>
                    </div>
                </div>

                <div class="pt-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <a href="/admin/login.php" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <span>Go to Admin Login</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="/" class="w-full inline-flex items-center justify-center gap-2 bg-[#13192A] hover:bg-slate-800 text-slate-200 hover:text-white font-semibold text-xs py-3.5 px-4 rounded-xl border border-slate-700/80 transition-all">
                        <span>Visit Website</span>
                    </a>
                </div>
            </div>

        <?php elseif ($isInstalled): ?>
            <!-- Already Installed Screen (Lock Guard) -->
            <div class="bg-[#0D121F] border border-slate-800/90 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-amber-500/15 border border-amber-500/30 text-amber-400 flex items-center justify-center text-2xl font-bold">
                    🔒
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white">Application Already Installed</h2>
                    <p class="text-xs text-slate-400 mt-2 max-w-md mx-auto leading-relaxed">
                        FF Panel is already configured and active. For security reasons, the web installer is locked. If you need to re-run the setup, please remove <code class="text-rose-400 font-mono text-[11px] bg-black/40 px-1 py-0.5 rounded">config/installed.lock</code> from your server.
                    </p>
                </div>

                <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="/admin/login.php" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <span>Admin Login</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="/" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#13192A] hover:bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs py-3 px-6 rounded-xl border border-slate-700 transition-all">
                        <span>Customer Store</span>
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Active Setup Form -->
            
            <?php if ($error): ?>
                <div class="bg-rose-950/90 border border-rose-500/50 rounded-2xl p-4 text-xs text-rose-300 flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Step 1: Environment Check Card -->
            <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-5 shadow-xl space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                        <span class="text-rose-500">1.</span> System Compatibility
                    </span>
                    <span class="text-[11px] text-emerald-400 font-semibold">Ready for Installation</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-xs">
                    <?php foreach ($reqs as $label => $data): ?>
                        <div class="bg-[#13192A] border border-slate-800/80 rounded-xl p-2.5 flex items-center justify-between gap-2">
                            <span class="text-[11px] text-slate-400 truncate"><?= $label ?></span>
                            <?php if ($data['status']): ?>
                                <span class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded shrink-0">✓ OK</span>
                            <?php else: ?>
                                <span class="text-[10px] font-bold text-rose-400 bg-rose-500/10 px-1.5 py-0.5 rounded shrink-0">✕ Failed</span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Installation Form -->
            <form action="/install.php" method="POST" class="space-y-6" id="installForm">
                <input type="hidden" name="action" value="install">

                <!-- Step 2: Database Configuration -->
                <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                            <span class="text-rose-500">2.</span> MySQL Database Credentials
                        </span>
                        <button type="button" onclick="testDbConnection()" id="testDbBtn" class="text-[11px] font-semibold text-rose-400 hover:text-white bg-rose-500/10 hover:bg-rose-500/25 border border-rose-500/30 px-2.5 py-1 rounded-lg transition-colors">
                            Test Connection
                        </button>
                    </div>

                    <div id="testDbAlert" class="hidden text-xs p-3 rounded-xl"></div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Database Host *</label>
                            <input type="text" id="db_host" name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? '127.0.0.1') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Port</label>
                            <input type="text" id="db_port" name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? '3306') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500 font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Database Name *</label>
                        <input type="text" id="db_name" name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? 'ffpanel') ?>" required placeholder="e.g. ffpanel" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        <span class="text-[10px] text-slate-500 mt-1 block">The installer will create this database automatically if it does not already exist.</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Database Username *</label>
                            <input type="text" id="db_user" name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? 'root') ?>" required placeholder="e.g. root" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Database Password</label>
                            <input type="password" id="db_pass" name="db_pass" placeholder="••••••••••••" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Administrator Account Setup -->
                <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 shadow-xl space-y-4">
                    <div class="pb-2 border-b border-slate-800">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-2">
                            <span class="text-rose-500">3.</span> Super Administrator Account
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Full Name *</label>
                            <input type="text" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? 'Store Administrator') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Email Address *</label>
                            <input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@ffpanel.com') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Password *</label>
                            <input type="password" name="admin_pass" required placeholder="At least 6 characters" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm Admin Password *</label>
                            <input type="password" name="admin_pass_confirm" required placeholder="Re-enter password" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        </div>
                    </div>
                </div>

                <!-- Submit Action -->
                <div class="pt-2">
                    <button type="submit" id="submitBtn" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3.5 px-6 rounded-2xl shadow-xl shadow-rose-600/30 hover:shadow-rose-600/50 transition-all cursor-pointer">
                        <span>Execute Installation & Build Tables</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <!-- Footer -->
        <div class="text-center text-[11px] text-slate-500">
            FF Panel Web Setup Engine • Secure Database Migration
        </div>

    </div>

    <!-- Plain JavaScript for interactive connection check -->
    <script>
    function testDbConnection() {
        const btn = document.getElementById('testDbBtn');
        const alertBox = document.getElementById('testDbAlert');
        const host = document.getElementById('db_host').value;
        const port = document.getElementById('db_port').value;
        const name = document.getElementById('db_name').value;
        const user = document.getElementById('db_user').value;
        const pass = document.getElementById('db_pass').value;

        btn.innerText = 'Connecting...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'test_db');
        formData.append('db_host', host);
        formData.append('db_port', port);
        formData.append('db_name', name);
        formData.append('db_user', user);
        formData.append('db_pass', pass);

        fetch('/install.php', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.innerText = 'Test Connection';
            btn.disabled = false;
            alertBox.classList.remove('hidden');
            if (data.success) {
                alertBox.className = 'text-xs p-3 rounded-xl bg-emerald-950/80 border border-emerald-500/40 text-emerald-300 flex items-center gap-2';
                alertBox.innerHTML = '<span>✓</span> <span>' + data.message + '</span>';
            } else {
                alertBox.className = 'text-xs p-3 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300 flex items-center gap-2';
                alertBox.innerHTML = '<span>✕</span> <span>' + (data.error || 'Connection failed') + '</span>';
            }
        })
        .catch(function(err) {
            btn.innerText = 'Test Connection';
            btn.disabled = false;
            alertBox.classList.remove('hidden');
            alertBox.className = 'text-xs p-3 rounded-xl bg-rose-950/80 border border-rose-500/40 text-rose-300';
            alertBox.innerText = 'Network error or server unreachable.';
        });
    }
    </script>
</body>
</html>
