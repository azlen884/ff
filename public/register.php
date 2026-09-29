<?php
require_once __DIR__ . '/../config/app.php';
guestOnly();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $name = trim($_POST['name'] ?? '');
    $email = trim(strtolower($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $initialUid = trim($_POST['ff_uid'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields (Name, Email, Password).';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters in length.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match. Please re-enter.';
    } else {
        $db = getDbConnection();
        // Check if email already exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'An account with this email address already exists. Please sign in instead.';
        } else {
            try {
                $db->beginTransaction();
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                // Starting balance for new user to test orders
                $initialBalance = 500.00;

                $stmt = $db->prepare("INSERT INTO users (name, email, password, phone, role, wallet_balance, status) VALUES (?, ?, ?, ?, 'user', ?, 'active')");
                $stmt->execute([$name, $email, $hashedPassword, $phone, $initialBalance]);
                $newUserId = (int)$db->lastInsertId();

                // If user provided Free Fire UID on signup, save it immediately
                if (!empty($initialUid)) {
                    $uidStmt = $db->prepare("INSERT INTO saved_uids (user_id, uid_number, player_name, region, is_default) VALUES (?, ?, ?, 'India Server', 1)");
                    $uidStmt->execute([$newUserId, $initialUid, $name . '_FF']);
                }

                $db->commit();

                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_role'] = 'user';
                setFlash('success', 'Account created successfully! ₹500 welcome balance credited to your wallet.');
                header('Location: /dashboard');
                exit;
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Failed to register account: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/../includes/landing_header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-6">
        
        <!-- Header -->
        <div class="text-center space-y-2">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-gradient-to-br from-rose-500 to-red-700 flex items-center justify-center text-white shadow-lg shadow-rose-600/30">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <h2 class="text-2xl font-extrabold text-white tracking-tight">Create Free Account</h2>
            <p class="text-xs text-slate-400">Join FF Panel Store for instant Free Fire top-ups and exclusive discounts.</p>
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
            <form action="/register" method="POST" class="space-y-4">
                <?= csrfField() ?>

                <div>
                    <label for="name" class="block text-xs font-semibold text-slate-300 mb-1.5">Full Name *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="e.g. Rahul Sharma">
                </div>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address *</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="e.g. rahul@example.com">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="phone" class="block text-xs font-semibold text-slate-300 mb-1.5">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="+91 9876543210">
                    </div>
                    <div>
                        <label for="ff_uid" class="block text-xs font-semibold text-slate-300 mb-1.5">Free Fire UID (Optional)</label>
                        <input type="text" id="ff_uid" name="ff_uid" value="<?= htmlspecialchars($_POST['ff_uid'] ?? '') ?>" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="e.g. 2849182391">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password *</label>
                        <input type="password" id="password" name="password" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="At least 6 chars">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm Password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors" placeholder="Re-enter password">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <span>Register & Get ₹500 Bonus</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </form>

            <div class="mt-6 pt-5 border-t border-slate-800 text-center text-xs text-slate-400">
                Already registered? 
                <a href="/login" class="text-rose-400 hover:text-rose-300 font-semibold ml-1">Sign In instead</a>
            </div>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/landing_footer.php'; ?>
