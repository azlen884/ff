<?php
require_once __DIR__ . '/../config/app.php';
guestOnly();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both your email address and password.';
    } else {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND role = 'user'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'suspended') {
                $error = 'Your account has been suspended. Please contact support.';
            } else {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = 'user';
                setFlash('success', 'Welcome back, ' . $user['name'] . '! Access your Free Fire dashboard below.');
                header('Location: /user/dashboard.php');
                exit;
            }
        } else {
            $error = 'Invalid email address or password. Please try again.';
        }
    }
}

require_once __DIR__ . '/../includes/landing_header.php';
?>

<div class="min-h-[75vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 flex items-center justify-center text-white shadow-lg shadow-rose-600/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Customer Sign In</h2>
            <p class="text-xs text-slate-400">Access your saved Free Fire UIDs, order logs, and wallet balance.</p>
        </div>

        <!-- Quick Demo Credentials Box for Testing -->
        <div class="bg-rose-500/10 border border-rose-500/25 rounded-2xl p-4 text-xs text-slate-300 space-y-2">
            <div class="flex items-center justify-between">
                <span class="font-bold text-rose-400 flex items-center gap-1.5">
                    <span>⚡</span> <span>Demo Customer Account:</span>
                </span>
                <button type="button" onclick="document.getElementById('email').value='aaris@ffpanel.com'; document.getElementById('password').value='UserPassword123!';" class="text-[11px] text-white bg-rose-600/80 hover:bg-rose-600 px-2 py-0.5 rounded font-semibold transition-colors">
                    Auto-Fill Credentials
                </button>
            </div>
            <div class="font-mono text-[11px] text-slate-300">
                Email: <span class="text-white font-semibold">aaris@ffpanel.com</span> | Pass: <span class="text-white font-semibold">UserPassword123!</span>
            </div>
        </div>

        <!-- Error Alert -->
        <?php if ($error): ?>
            <div class="bg-rose-950/80 border border-rose-500/40 rounded-xl p-3.5 text-xs text-rose-300 flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-2xl">
            <form action="/login.php" method="POST" class="space-y-4">
                <?= csrfField() ?>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? 'aaris@ffpanel.com') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="e.g. customer@example.com">
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <input type="password" id="password" name="password" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="••••••••••••">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <span>Sign In to Customer Panel</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Don't have an account yet? 
                <a href="/register.php" class="text-rose-400 hover:text-rose-300 font-semibold ml-1">Create an account</a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/landing_footer.php'; ?>
