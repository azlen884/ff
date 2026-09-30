<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

$activeNav = 'profile';
$activeSidebar = 'profile';
$pageTitle = 'My Profile & Security';

// Handle Profile Update or Password Change
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name)) {
            setFlash('error', 'Name cannot be empty.');
        } else {
            $stmt = $db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $currentUser['id']]);
            setFlash('success', 'Profile details updated successfully.');
            header('Location: /profile');
            exit;
        }
    }

    if ($action === 'change_password') {
        $currentPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        // Verify current password
        $userCheck = $db->prepare("SELECT password FROM users WHERE id = ?");
        $userCheck->execute([$currentUser['id']]);
        $row = $userCheck->fetch();

        if (!password_verify($currentPass, $row['password'])) {
            setFlash('error', 'Incorrect current password. Please try again.');
        } elseif (strlen($newPass) < 6) {
            setFlash('error', 'New password must be at least 6 characters in length.');
        } elseif ($newPass !== $confirmPass) {
            setFlash('error', 'New passwords do not match.');
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $currentUser['id']]);
            setFlash('success', 'Password updated successfully!');
            header('Location: /profile');
            exit;
        }
    }
}

// User Lifetime Stats
$statsStmt = $db->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(amount), 0) as total_spent FROM orders WHERE user_id = ? AND status = 'completed'");
$statsStmt->execute([$currentUser['id']]);
$userStats = $statsStmt->fetch();

$totalUids = (int)$db->prepare("SELECT COUNT(*) FROM saved_uids WHERE user_id = ?");
$totalUids->execute([$currentUser['id']]);
$uidCount = $totalUids->fetchColumn();

require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Header -->
    <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-8 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#FF2E51] to-red-500 flex items-center justify-center text-white font-black text-2xl shadow-xl shadow-rose-600/30">
                    <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                </div>
                <div>
                    <h2 class="text-xl sm:text-2xl font-black text-white"><?= htmlspecialchars($currentUser['name']) ?></h2>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($currentUser['email']) ?> • Member since <?= date('M Y', strtotime($currentUser['created_at'])) ?></p>
                </div>
            </div>
            <div class="bg-[#13192A] border border-slate-700/80 rounded-xl px-5 py-3 text-right">
                <div class="text-lg font-black text-emerald-400"><?= formatCurrency((float)$currentUser['wallet_balance']) ?></div>
                <div class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Available Wallet Balance</div>
            </div>
        </div>

        <!-- Metrics bar -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-6 mt-6 border-t border-slate-800/80">
            <div class="bg-[#13192A] border border-slate-800 rounded-xl p-4">
                <div class="text-xs text-slate-400">Lifetime Orders</div>
                <div class="text-xl font-bold text-white mt-1"><?= (int)$userStats['total_orders'] ?> completed</div>
            </div>
            <div class="bg-[#13192A] border border-slate-800 rounded-xl p-4">
                <div class="text-xs text-slate-400">Total Spent</div>
                <div class="text-xl font-bold text-white mt-1"><?= formatCurrency((float)$userStats['total_spent']) ?></div>
            </div>
            <div class="bg-[#13192A] border border-slate-800 rounded-xl p-4">
                <div class="text-xs text-slate-400">Saved Game UIDs</div>
                <div class="text-xl font-bold text-white mt-1"><?= $uidCount ?> UIDs</div>
            </div>
        </div>
    </div>

    <!-- Edit Profile & Change Password Grids -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Personal Details -->
        <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-7 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white">Personal Information</h3>
            <form action="/profile" method="POST" class="space-y-4">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_profile">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($currentUser['name']) ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email Address</label>
                    <input type="email" value="<?= htmlspecialchars($currentUser['email']) ?>" disabled class="w-full bg-[#0a0e18] border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-500 cursor-not-allowed">
                    <span class="text-[10px] text-slate-400 mt-1 block">Email address cannot be changed directly.</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone Number</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($currentUser['phone'] ?? '') ?>" placeholder="+91 9876543210" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-5 rounded-xl shadow-md shadow-rose-600/25 transition-all">
                        Update Information
                    </button>
                </div>
            </form>
        </div>

        <!-- Security & Password -->
        <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-7 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white">Security & Password</h3>
            <form action="/profile" method="POST" class="space-y-4">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="change_password">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Current Password</label>
                    <input type="password" name="current_password" required placeholder="••••••••••••" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">New Password</label>
                    <input type="password" name="new_password" required placeholder="At least 6 characters" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm New Password</label>
                    <input type="password" name="confirm_password" required placeholder="Re-enter new password" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>

                <div class="pt-2">
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs py-2.5 px-5 rounded-xl border border-slate-700 transition-all">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
