<?php
/**
 * FF Panel V2 - Admin Order Management
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'orders';
$pageTitle = 'Manage Top-Up Orders';

// Handle Order Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrfToken();
    $action = $_POST['action'];
    $orderId = (int)($_POST['order_id'] ?? 0);

    $oStmt = $db->prepare("SELECT o.*, s.title as service_title, u.name as user_name, u.email as user_email FROM orders o JOIN services s ON o.service_id = s.id JOIN users u ON o.user_id = u.id WHERE o.id = ?");
    $oStmt->execute([$orderId]);
    $order = $oStmt->fetch();

    if (!$order) {
        setFlash('error', 'Order not found.');
        header('Location: /admin/orders');
        exit;
    }

    if ($action === 'update_order') {
        $newStatus = trim($_POST['status'] ?? '');
        $adminNote = trim($_POST['admin_note'] ?? '');
        $allowed = ['pending', 'processing', 'completed', 'failed', 'cancelled'];

        if (in_array($newStatus, $allowed, true)) {
            $update = $db->prepare("UPDATE orders SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?");
            $update->execute([$newStatus, $adminNote, $orderId]);

            // Notify user of status update
            createNotification(
                (int)$order['user_id'],
                "Order Status: " . ucfirst($newStatus),
                "Your order #{$order['order_number']} for {$order['service_title']} is now " . ucfirst($newStatus) . ($adminNote ? " ({$adminNote})" : ""),
                $newStatus === 'completed' ? 'success' : ($newStatus === 'failed' ? 'error' : 'info'),
                "/order-detail?id={$orderId}",
                "user",
                $db
            );

            setFlash('success', "Order #{$order['order_number']} status changed to " . ucfirst($newStatus));
        }
    } elseif ($action === 'cancel_refund') {
        if ($order['status'] === 'cancelled') {
            setFlash('error', 'Order is already cancelled.');
            header('Location: /admin/orders');
            exit;
        }

        try {
            $db->beginTransaction();

            $refundAmount = (float)$order['amount'];

            // Refund to user's wallet
            recordWalletTransaction(
                (int)$order['user_id'],
                'credit',
                $refundAmount,
                "Refund for Cancelled Order #{$order['order_number']} - {$order['service_title']}",
                "REFUND-{$order['order_number']}",
                $db
            );

            $update = $db->prepare("UPDATE orders SET status = 'cancelled', admin_note = 'Cancelled and refunded by Admin', updated_at = NOW() WHERE id = ?");
            $update->execute([$orderId]);

            createNotification(
                (int)$order['user_id'],
                "Order Cancelled & Refunded",
                "Your order #{$order['order_number']} has been cancelled and " . formatCurrency($refundAmount) . " was refunded to your wallet.",
                "warning",
                "/wallet",
                "user",
                $db
            );

            $db->commit();
            setFlash('success', "Order #{$order['order_number']} cancelled and " . formatCurrency($refundAmount) . " refunded to {$order['user_name']}'s wallet.");

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            setFlash('error', 'Failed to cancel and refund: ' . $e->getMessage());
        }
    } elseif ($action === 'retry_fulfillment') {
        // Fetch Service & Provider
        $sStmt = $db->prepare("SELECT s.*, p.api_url, p.api_key, p.api_secret, p.is_enabled as prov_enabled, p.name as prov_name FROM services s LEFT JOIN providers p ON s.provider_id = p.id WHERE s.id = ?");
        $sStmt->execute([$order['service_id']]);
        $srv = $sStmt->fetch();

        if ($srv && !empty($srv['prov_enabled']) && !empty($srv['api_url']) && !empty($srv['api_key'])) {
            $provData = [
                'name' => $srv['prov_name'],
                'is_enabled' => 1,
                'api_url' => $srv['api_url'],
                'api_key' => $srv['api_key'],
                'api_secret' => $srv['api_secret']
            ];
            $res = dispatchProviderOrder($provData, $srv, $order['player_uid'], $order['order_number']);
            
            $upd = $db->prepare("UPDATE orders SET status = ?, provider_order_id = ?, provider_response = ?, admin_note = ?, updated_at = NOW() WHERE id = ?");
            $upd->execute([$res['status'], $res['provider_order_id'], $res['raw_response'], $res['message'], $orderId]);
            
            setFlash('success', "Retry dispatched: " . $res['message']);
        } else {
            setFlash('error', "Cannot auto-retry: No active provider with valid API credentials is mapped to this service.");
        }
    }

    header('Location: /admin/orders');
    exit;
}

// Search and filter parameters
$statusFilter = trim($_GET['status'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT o.*, s.title as service_title, s.amount_description, u.name as user_name, u.email as user_email, p.name as provider_name 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.user_id = u.id 
        LEFT JOIN providers p ON o.provider_id = p.id 
        WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (o.order_number LIKE ? OR o.player_uid LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $like = "%{$searchQuery}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY o.id DESC LIMIT 100";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for filters
$counts = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
$statusCounts = ['completed' => 0, 'processing' => 0, 'pending' => 0, 'failed' => 0, 'cancelled' => 0];
foreach ($counts as $r) {
    $statusCounts[$r['status']] = (int)$r['cnt'];
}

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">

    <!-- Flash Message -->
    <?php $flash = getFlash(); if ($flash): ?>
        <div role="alert" class="p-4 rounded-2xl flex items-center justify-between text-xs font-semibold shadow-lg transition-all <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/40' : ($flash['type'] === 'info' ? 'bg-cyan-950/80 text-cyan-300 border border-cyan-500/40' : 'bg-rose-950/80 text-rose-300 border border-rose-500/40') ?>">
            <div class="flex items-center gap-2.5">
                <?php if ($flash['type'] === 'success'): ?>
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?php elseif ($flash['type'] === 'info'): ?>
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?php else: ?>
                    <svg class="w-4 h-4 text-rose-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <?php endif; ?>
                <span><?= htmlspecialchars($flash['message']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Manage Top-Up Orders</h1>
            <p class="text-xs text-slate-400 mt-1">Review top-up requests, update fulfillment status, process refunds, and retry API delivery.</p>
        </div>
    </div>

            <!-- Filter Status Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
                <a href="/admin/orders" class="p-3.5 rounded-2xl bg-[#0D121F] border <?= empty($statusFilter) ? 'border-rose-500 bg-[#161D32]' : 'border-slate-800' ?> text-center transition-all">
                    <div class="text-[10px] uppercase font-bold text-slate-400">All Orders</div>
                    <div class="text-xl font-bold text-white mt-1"><?= array_sum($statusCounts) ?></div>
                </a>
                <a href="/admin/orders?status=pending" class="p-3.5 rounded-2xl bg-[#0D121F] border <?= $statusFilter === 'pending' ? 'border-amber-500 bg-[#161D32]' : 'border-slate-800' ?> text-center transition-all">
                    <div class="text-[10px] uppercase font-bold text-amber-400">Pending</div>
                    <div class="text-xl font-bold text-white mt-1"><?= $statusCounts['pending'] ?></div>
                </a>
                <a href="/admin/orders?status=processing" class="p-3.5 rounded-2xl bg-[#0D121F] border <?= $statusFilter === 'processing' ? 'border-cyan-500 bg-[#161D32]' : 'border-slate-800' ?> text-center transition-all">
                    <div class="text-[10px] uppercase font-bold text-cyan-400">Processing</div>
                    <div class="text-xl font-bold text-white mt-1"><?= $statusCounts['processing'] ?></div>
                </a>
                <a href="/admin/orders?status=completed" class="p-3.5 rounded-2xl bg-[#0D121F] border <?= $statusFilter === 'completed' ? 'border-emerald-500 bg-[#161D32]' : 'border-slate-800' ?> text-center transition-all">
                    <div class="text-[10px] uppercase font-bold text-emerald-400">Completed</div>
                    <div class="text-xl font-bold text-white mt-1"><?= $statusCounts['completed'] ?></div>
                </a>
                <a href="/admin/orders?status=failed" class="p-3.5 rounded-2xl bg-[#0D121F] border <?= $statusFilter === 'failed' ? 'border-rose-500 bg-[#161D32]' : 'border-slate-800' ?> text-center transition-all">
                    <div class="text-[10px] uppercase font-bold text-rose-400">Failed / Errors</div>
                    <div class="text-xl font-bold text-white mt-1"><?= $statusCounts['failed'] ?></div>
                </a>
            </div>

            <!-- Search Bar -->
            <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-4 shadow-xl">
                <form action="/admin/orders" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                    <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search Order #, Player UID, customer name or email..." class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                    <?php if (!empty($statusFilter)): ?>
                        <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
                    <?php endif; ?>
                    <button type="submit" class="w-full sm:w-auto px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold">Search</button>
                    <?php if (!empty($searchQuery) || !empty($statusFilter)): ?>
                        <a href="/admin/orders" class="text-xs text-slate-400 hover:text-white underline">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Orders Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <?php if (empty($orders)): ?>
                    <p class="text-xs text-slate-500 text-center py-10">No orders found matching your criteria.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Order Details</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Player UID</th>
                                    <th class="py-3 px-4">Provider / API</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($orders as $o): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="font-bold text-white flex items-center gap-1.5">
                                                <span><?= htmlspecialchars($o['order_number']) ?></span>
                                            </div>
                                            <div class="text-[11px] text-rose-400 font-semibold"><?= htmlspecialchars($o['service_title']) ?></div>
                                            <div class="text-[10px] text-slate-500"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="font-bold text-white"><?= htmlspecialchars($o['user_name']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($o['user_email']) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <code class="font-mono text-white font-bold bg-[#13192A] px-2 py-0.5 rounded border border-slate-800 select-all"><?= htmlspecialchars($o['player_uid']) ?></code>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <?php if (!empty($o['provider_name'])): ?>
                                                <div class="font-semibold text-slate-300"><?= htmlspecialchars($o['provider_name']) ?></div>
                                                <div class="text-[10px] text-slate-500 font-mono"><?= htmlspecialchars($o['provider_order_id'] ?: 'Pending Sync') ?></div>
                                            <?php else: ?>
                                                <span class="text-slate-500 text-[11px] italic">Direct Fulfillment</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="font-bold text-white"><?= formatCurrency((float)$o['amount']) ?></div>
                                            <?php if ((float)$o['discount_amount'] > 0): ?>
                                                <div class="text-[10px] text-emerald-400 font-semibold">-<?= formatCurrency((float)$o['discount_amount']) ?> coupon</div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <?php if ($o['status'] === 'completed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                    ✓ Completed
                                                </span>
                                            <?php elseif ($o['status'] === 'processing'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-500/15 text-cyan-400 border border-cyan-500/30">
                                                    ⚡ Processing
                                                </span>
                                            <?php elseif ($o['status'] === 'pending'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse">
                                                    ⏳ Pending
                                                </span>
                                            <?php elseif ($o['status'] === 'failed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                                    ✗ Failed
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/15 text-slate-400 border border-slate-500/30">
                                                    Cancelled
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <div class="flex items-center justify-end gap-1.5">
                                                <button type="button" onclick='openOrderModal(<?= json_encode($o) ?>)' class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-[11px] font-semibold transition-colors">
                                                    Manage
                                                </button>
                                                <?php if ($o['status'] === 'failed' || $o['status'] === 'pending'): ?>
                                                    <form action="/admin/orders" method="POST" class="inline">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="retry_fulfillment">
                                                        <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                                        <button type="submit" title="Retry Provider API" class="px-2 py-1 bg-rose-950/60 hover:bg-rose-900 text-rose-400 rounded-lg text-[11px] border border-rose-800 transition-colors">
                                                            Retry
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

</main>

<!-- Modal: Admin Manage Order -->
<div id="adminOrderModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-5 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white" id="modalOrderNumber">Order Details</h3>
            <button type="button" onclick="closeOrderModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <div class="space-y-3 text-xs">
            <div class="bg-[#111728] p-3.5 rounded-2xl border border-slate-800 space-y-1.5">
                <div class="flex justify-between">
                    <span class="text-slate-400">Customer:</span>
                    <span class="font-bold text-white" id="m_cust"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Service:</span>
                    <span class="font-bold text-rose-400" id="m_service"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Player UID:</span>
                    <code class="font-mono text-white font-bold" id="m_uid"></code>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-400">Total Paid:</span>
                    <span class="font-bold text-white" id="m_amount"></span>
                </div>
            </div>

            <!-- Status Update Form -->
            <form action="/admin/orders" method="POST" class="space-y-3 pt-2">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_order">
                <input type="hidden" name="order_id" id="m_order_id" value="0">

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Update Status</label>
                    <select name="status" id="m_status_select" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Admin Note / Reason</label>
                    <textarea name="admin_note" id="m_note" rows="2" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500" placeholder="e.g. Delivered diamonds via player UID direct recharge."></textarea>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#FF2E51] hover:bg-rose-600 shadow-md shadow-rose-600/30">
                        Update Status
                    </button>
                </div>
            </form>

            <!-- Dangerous Cancel & Refund Action -->
            <div class="pt-3 border-t border-slate-800">
                <form action="/admin/orders" method="POST" onsubmit="return confirm('WARNING: This will cancel the order and IMMEDIATELY REFUND the full amount back to the customer\'s wallet balance. Proceed?');">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="cancel_refund">
                    <input type="hidden" name="order_id" id="m_refund_order_id" value="0">
                    <button type="submit" class="w-full py-2 bg-rose-950/40 hover:bg-rose-900/60 border border-rose-800 text-rose-300 rounded-xl text-xs font-bold transition-colors">
                        Cancel Order & Refund Amount to Wallet
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openOrderModal(o) {
    document.getElementById('modalOrderNumber').innerText = 'Manage Order ' + o.order_number;
    document.getElementById('m_order_id').value = o.id;
    document.getElementById('m_refund_order_id').value = o.id;
    document.getElementById('m_cust').innerText = o.user_name + ' (' + o.user_email + ')';
    document.getElementById('m_service').innerText = o.service_title;
    document.getElementById('m_uid').innerText = o.player_uid;
    document.getElementById('m_amount').innerText = '₹' + parseFloat(o.amount).toFixed(2);
    document.getElementById('m_status_select').value = o.status;
    document.getElementById('m_note').value = o.admin_note || '';
    document.getElementById('adminOrderModal').classList.remove('hidden');
}

function closeOrderModal() {
    document.getElementById('adminOrderModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
