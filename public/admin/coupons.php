<?php
/**
 * FF Panel V2 - Admin Discount Coupons Management
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_coupon') {
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['is_active'] ?? 0);
        $stmt = $db->prepare("UPDATE coupons SET is_active = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        setFlash('success', 'Coupon status updated.');
        header('Location: /admin/coupons');
        exit;
    }

    if ($action === 'delete_coupon') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM coupons WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('info', 'Coupon removed.');
        header('Location: /admin/coupons');
        exit;
    }

    if ($action === 'save_coupon') {
        $id = (int)($_POST['id'] ?? 0);
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = $_POST['discount_type'] === 'percentage' ? 'percentage' : 'flat';
        $val = (float)($_POST['discount_value'] ?? 0);
        $minOrder = (float)($_POST['min_order_amount'] ?? 0);
        $maxCap = !empty($_POST['max_discount_amount']) ? (float)$_POST['max_discount_amount'] : null;
        $usageLimit = (int)($_POST['usage_limit'] ?? 100);
        $expiresAt = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $isActive = !empty($_POST['is_active']) ? 1 : 0;

        if (empty($code) || $val <= 0) {
            setFlash('error', 'Coupon Code and Discount Value (> 0) are required.');
            header('Location: /admin/coupons');
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE coupons SET code = ?, discount_type = ?, discount_value = ?, min_order_amount = ?, max_discount_amount = ?, usage_limit = ?, expires_at = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$code, $type, $val, $minOrder, $maxCap, $usageLimit, $expiresAt, $isActive, $id]);
            setFlash('success', "Coupon '{$code}' updated.");
        } else {
            // Check if code exists
            $chk = $db->prepare("SELECT id FROM coupons WHERE code = ?");
            $chk->execute([$code]);
            if ($chk->fetch()) {
                setFlash('error', "Coupon code '{$code}' already exists.");
                header('Location: /admin/coupons');
                exit;
            }

            $stmt = $db->prepare("INSERT INTO coupons (code, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, expires_at, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$code, $type, $val, $minOrder, $maxCap, $usageLimit, $expiresAt, $isActive]);
            setFlash('success', "New coupon '{$code}' created successfully!");
        }
        header('Location: /admin/coupons');
        exit;
    }
}

// Fetch all coupons
$coupons = $db->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();

$activeNav = 'coupons';
require_once __DIR__ . '/../../includes/admin_header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-64 shrink-0">
            <?php require_once __DIR__ . '/../../includes/admin_sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 space-y-6">

            <!-- Flash Message -->
            <?php $flash = getFlash(); if ($flash): ?>
                <div role="alert" class="p-4 rounded-2xl flex items-center justify-between text-xs font-semibold shadow-lg transition-all <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/40' : ($flash['type'] === 'info' ? 'bg-cyan-950/80 text-cyan-300 border border-cyan-500/40' : 'bg-rose-950/80 text-rose-300 border border-rose-500/40') ?>">
                    <div class="flex items-center gap-2.5">
                        <span><?= $flash['type'] === 'success' ? '✅' : ($flash['type'] === 'info' ? 'ℹ️' : '⚠️') ?></span>
                        <span><?= htmlspecialchars($flash['message']) ?></span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
                </div>
            <?php endif; ?>

            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Discount Coupons</h1>
                    <p class="text-xs text-slate-400 mt-1">Create and manage promo codes for percentage or flat order discounts.</p>
                </div>
                <button type="button" onclick="openCouponModal()" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Create Coupon</span>
                </button>
            </div>

            <!-- Coupons Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <?php if (empty($coupons)): ?>
                    <div class="text-center py-10 space-y-2">
                        <p class="text-xs text-slate-500">No discount coupons configured yet.</p>
                        <button type="button" onclick="openCouponModal()" class="text-xs font-bold text-rose-400 hover:text-rose-300">Create your first coupon &rarr;</button>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Coupon Code</th>
                                    <th class="py-3 px-4">Discount</th>
                                    <th class="py-3 px-4">Min Order</th>
                                    <th class="py-3 px-4">Usage</th>
                                    <th class="py-3 px-4">Expires</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($coupons as $c): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3.5 px-4 font-mono font-bold text-rose-400 text-sm whitespace-nowrap">
                                            <?= htmlspecialchars($c['code']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-white font-semibold whitespace-nowrap">
                                            <?= $c['discount_type'] === 'percentage' ? ((float)$c['discount_value'] . '% OFF') : ('₹' . number_format((float)$c['discount_value'], 2) . ' FLAT') ?>
                                            <?php if (!empty($c['max_discount_amount'])): ?>
                                                <span class="text-[10px] text-slate-400 block font-normal">(Max ₹<?= (float)$c['max_discount_amount'] ?>)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                            <?= formatCurrency((float)$c['min_order_amount']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                            <span class="font-bold text-white"><?= (int)$c['used_count'] ?></span> / <?= (int)$c['usage_limit'] ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                            <?= !empty($c['expires_at']) ? date('d M Y', strtotime($c['expires_at'])) : '<span class="text-emerald-400">Never</span>' ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <form action="/admin/coupons" method="POST" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="toggle_coupon">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="hidden" name="is_active" value="<?= $c['is_active'] ? 0 : 1 ?>">
                                                <button type="submit" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $c['is_active'] ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' ?>">
                                                    <?= $c['is_active'] ? 'Active' : 'Disabled' ?>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-2">
                                                <button type="button" onclick='editCoupon(<?= json_encode($c) ?>)' class="p-1 text-slate-400 hover:text-white rounded hover:bg-slate-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                </button>
                                                <form action="/admin/coupons" method="POST" onsubmit="return confirm('Delete coupon <?= $c['code'] ?>?');" class="inline">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="action" value="delete_coupon">
                                                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                    <button type="submit" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-rose-950/40">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<!-- Modal: Coupon Create / Edit -->
<div id="couponModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white" id="cModalTitle">Create Coupon Code</h3>
            <button type="button" onclick="closeCouponModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/coupons" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_coupon">
            <input type="hidden" name="id" id="c_id" value="0">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Coupon Code *</label>
                <input type="text" name="code" id="c_code" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono uppercase tracking-wider" placeholder="e.g. FLASH50">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Discount Type *</label>
                    <select name="discount_type" id="c_type" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="flat">Flat Amount (₹)</option>
                        <option value="percentage">Percentage (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Discount Value *</label>
                    <input type="number" name="discount_value" id="c_val" step="0.5" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white" placeholder="e.g. 50">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Min Order Amount (₹)</label>
                    <input type="number" name="min_order_amount" id="c_min" step="1" value="0" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Max Cap (For % only)</label>
                    <input type="number" name="max_discount_amount" id="c_max" step="1" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white" placeholder="Optional">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Usage Limit</label>
                    <input type="number" name="usage_limit" id="c_limit" value="100" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Expiry Date</label>
                    <input type="date" name="expires_at" id="c_exp" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_active" id="c_active" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                <label for="c_active" class="text-xs font-semibold text-slate-300">Active and redeemable by customers</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeCouponModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#FF2E51] hover:bg-rose-600 shadow-md shadow-rose-600/30">Save Coupon</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCouponModal() {
    document.getElementById('cModalTitle').innerText = 'Create New Coupon';
    document.getElementById('c_id').value = '0';
    document.getElementById('c_code').value = '';
    document.getElementById('c_type').value = 'flat';
    document.getElementById('c_val').value = '';
    document.getElementById('c_min').value = '0';
    document.getElementById('c_max').value = '';
    document.getElementById('c_limit').value = '100';
    document.getElementById('c_exp').value = '';
    document.getElementById('c_active').checked = true;
    document.getElementById('couponModal').classList.remove('hidden');
}

function editCoupon(c) {
    document.getElementById('cModalTitle').innerText = 'Edit Coupon ' + c.code;
    document.getElementById('c_id').value = c.id;
    document.getElementById('c_code').value = c.code;
    document.getElementById('c_type').value = c.discount_type;
    document.getElementById('c_val').value = c.discount_value;
    document.getElementById('c_min').value = c.min_order_amount || '0';
    document.getElementById('c_max').value = c.max_discount_amount || '';
    document.getElementById('c_limit').value = c.usage_limit || '100';
    document.getElementById('c_exp').value = c.expires_at || '';
    document.getElementById('c_active').checked = c.is_active == 1;
    document.getElementById('couponModal').classList.remove('hidden');
}

function closeCouponModal() {
    document.getElementById('couponModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
