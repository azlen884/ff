<?php
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'users';
$pageTitle = 'Manage Customers';

// Handle Balance Adjustment & Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrfToken();
    $action = $_POST['action'];

    if ($action === 'adjust_wallet') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        $type = $_POST['type'] ?? 'add'; // 'add' or 'deduct'

        if ($userId > 0 && $amount > 0) {
            if ($type === 'add') {
                $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
                $stmt->execute([$amount, $userId]);
                setFlash('success', "Credited " . formatCurrency($amount) . " to user wallet.");
            } else {
                $stmt = $db->prepare("UPDATE users SET wallet_balance = GREATEST(0, wallet_balance - ?) WHERE id = ?");
                $stmt->execute([$amount, $userId]);
                setFlash('info', "Deducted " . formatCurrency($amount) . " from user wallet.");
            }
        }
        header('Location: /admin/users.php');
        exit;
    }

    if ($action === 'toggle_status') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $stmt = $db->prepare("UPDATE users SET status = IF(status = 'active', 'suspended', 'active') WHERE id = ? AND role != 'admin'");
        $stmt->execute([$userId]);
        setFlash('info', "Customer account status updated.");
        header('Location: /admin/users.php');
        exit;
    }
}

// Fetch all registered customers with their order count & total spent
$users = $db->query("SELECT u.*, 
                     COUNT(o.id) as order_count, 
                     COALESCE(SUM(o.amount), 0) as total_spent,
                     (SELECT COUNT(*) FROM saved_uids WHERE user_id = u.id) as uid_count
                     FROM users u 
                     LEFT JOIN orders o ON u.id = o.user_id 
                     WHERE u.role = 'user' 
                     GROUP BY u.id 
                     ORDER BY u.created_at DESC")->fetchAll();

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Registered Customers</h2>
            <p class="text-xs text-slate-400 mt-1">Manage user accounts, wallet balance balances, and account permissions.</p>
        </div>
        <div class="text-xs text-slate-400">
            Total Customers: <strong class="text-white"><?= count($users) ?></strong>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#141A2D] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Customer</th>
                        <th class="py-3.5 px-4 font-bold">Contact</th>
                        <th class="py-3.5 px-4 font-bold">Wallet Balance</th>
                        <th class="py-3.5 px-4 font-bold">Orders / Spent</th>
                        <th class="py-3.5 px-4 font-bold">Saved UIDs</th>
                        <th class="py-3.5 px-4 font-bold">Status</th>
                        <th class="py-3.5 px-4 font-bold text-right">Wallet Adjustment</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    <?php foreach ($users as $u): ?>
                        <tr class="hover:bg-[#141A2D]/50 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="text-[10px] text-slate-400">Joined <?= date('d M Y', strtotime($u['created_at'])) ?></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-white"><?= htmlspecialchars($u['email']) ?></div>
                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($u['phone'] ?: 'No phone provided') ?></div>
                            </td>
                            <td class="py-3.5 px-4 font-black text-emerald-400 text-sm">
                                <?= formatCurrency((float)$u['wallet_balance']) ?>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-white"><?= (int)$u['order_count'] ?> orders</div>
                                <div class="text-[10px] text-slate-400">Spent <?= formatCurrency((float)$u['total_spent']) ?></div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-300 font-semibold">
                                <?= (int)$u['uid_count'] ?> UIDs
                            </td>
                            <td class="py-3.5 px-4">
                                <form action="/admin/users.php" method="POST" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                    <button type="submit" class="text-[10px] font-bold px-2.5 py-0.5 rounded-full border transition-colors <?= $u['status'] === 'active' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-rose-500/10 text-rose-400 border-rose-500/30' ?>">
                                        <?= ucfirst($u['status']) ?>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" onclick="openWalletModal(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>', '<?= formatCurrency((float)$u['wallet_balance']) ?>')" class="bg-[#141A2D] hover:bg-slate-700 text-slate-200 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700 font-semibold transition-colors">
                                    Adjust Funds
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Adjust Wallet Modal -->
<div id="walletModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-[#111728] border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>Wallet Adjustment: <span id="wallet_user_name" class="text-rose-400"></span></span>
            </h3>
            <button type="button" onclick="closeWalletModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/users.php" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="adjust_wallet">
            <input type="hidden" name="user_id" id="modal_user_id" value="">

            <div class="bg-[#141A2D] p-3 rounded-xl text-xs flex justify-between">
                <span class="text-slate-400">Current Balance:</span>
                <strong class="text-emerald-400" id="wallet_user_bal">₹ 0.00</strong>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Adjustment Type</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center gap-2 bg-[#161D32] border border-slate-700 p-2.5 rounded-xl cursor-pointer">
                        <input type="radio" name="type" value="add" checked class="text-rose-600 focus:ring-0">
                        <span class="text-xs font-bold text-white">+ Add Funds</span>
                    </label>
                    <label class="flex items-center gap-2 bg-[#161D32] border border-slate-700 p-2.5 rounded-xl cursor-pointer">
                        <input type="radio" name="type" value="deduct" class="text-rose-600 focus:ring-0">
                        <span class="text-xs font-bold text-rose-400">- Deduct Funds</span>
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Amount (₹) *</label>
                <input type="number" step="0.01" name="amount" required placeholder="e.g. 500.00" class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeWalletModal()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-2.5 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    Execute Balance Change
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openWalletModal(id, name, bal) {
    document.getElementById('modal_user_id').value = id;
    document.getElementById('wallet_user_name').innerText = name;
    document.getElementById('wallet_user_bal').innerText = bal;
    document.getElementById('walletModal').classList.remove('hidden');
}
function closeWalletModal() {
    document.getElementById('walletModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
