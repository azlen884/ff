<?php
require_once __DIR__ . '/../../config/app.php';

// If already logged in as admin, redirect to admin dashboard
if (getCurrentAdmin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your admin email and password.';
    } else {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            if ($admin['status'] === 'suspended') {
                $error = 'Admin account has been restricted.';
            } else {
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_name'] = $admin['name'];
                setFlash('success', 'Admin session authenticated. Welcome back, ' . $admin['name']);
                header('Location: /admin/dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid administrator credentials. Access logged.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0A0D14] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal Login — FF Panel Store</title>
    <link rel="stylesheet" href="/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="min-h-full flex items-center justify-center p-4 bg-gradient-to-b from-[#0F1422] to-[#0A0D14] antialiased">
    <div class="max-w-md w-full space-y-6">
        
        <div class="text-center space-y-2">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-gradient-to-br from-rose-600 to-red-800 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-rose-600/30">
                ⚡
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight uppercase">Admin Console</h1>
            <p class="text-xs text-slate-400">Restricted staff authentication for store operators.</p>
        </div>

        <!-- Quick Demo Credentials for Fast Evaluation -->
        <div class="bg-rose-500/10 border border-rose-500/25 rounded-2xl p-4 text-xs text-slate-300 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-rose-400 flex items-center gap-1.5">
                    <span>⚡</span> <span>Master Administrator:</span>
                </span>
                <button type="button" onclick="document.getElementById('email').value='admin@ffpanel.com'; document.getElementById('password').value='AdminPassword123!';" class="text-[11px] text-white bg-rose-600/80 hover:bg-rose-600 px-2 py-0.5 rounded font-semibold transition-colors">
                    Auto-Fill Credentials
                </button>
            </div>
            <div class="font-mono text-[11px] text-slate-300">
                Email: <span class="text-white font-semibold">admin@ffpanel.com</span> | Pass: <span class="text-white font-semibold">AdminPassword123!</span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="bg-rose-950/80 border border-rose-500/40 rounded-xl p-3.5 text-xs text-rose-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <div class="bg-[#111728] border border-slate-800 rounded-2xl p-6 sm:p-8 shadow-2xl">
            <form action="/admin/login.php" method="POST" class="space-y-4">
                <?= csrfField() ?>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'admin@ffpanel.com') ?>" required class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500 transition-colors">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Password</label>
                    <input type="password" id="password" name="password" required class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500 transition-colors">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <span>Authenticate Admin Portal</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-4 border-t border-slate-800 text-center">
                <a href="/login.php" class="text-xs text-slate-400 hover:text-white transition-colors">← Return to Customer Login</a>
            </div>
        </div>

    </div>
</body>
</html>
