<?php
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'orders';
$pageTitle = 'Manage Top-Up Orders';

// Handle Order Status & Note Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    verifyCsrfToken();
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $adminNote = trim($_POST['admin_note'] ?? '');
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];

    if ($orderId > 0 && in_array($newStatus, $allowed, true)) {
        $update = $db->prepare("UPDATE orders SET status = ?, admin_note = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$newStatus, $adminNote, $orderId]);
        setFlash('success', "Order #{$orderId} status updated to {$newStatus}.");
    }
    header('Location: /admin/orders.php');
    exit;
}

// Search and filter parameters
$statusFilter = trim($_GET['status'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT o.*, s.title as service_title, s.amount_description, u.name as user_name, u.email as user_email 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        JOIN users u ON o.user_id = u.id 
        WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (o.order_number LIKE ? OR o.player_uid LIKE ? OR u.name LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for filters
$counts = $db->query("SELECT status, COUNT(*) as cnt FROM orders GROUP BY status")->fetchAll();
$statusCounts = ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0];
foreach ($counts as $r) {
    $statusCounts[$r['status']] = (int)$r['cnt'];
}
$totalCount = array_sum($statusCounts);

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Header & Search -->
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-xl">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Manage Orders</h2>
            <p class="text-xs text-slate-400 mt-1">Review Free Fire player UIDs, update fulfillment status, and add delivery notes.</p>
        </div>

        <form action="/admin/orders.php" method="GET" class="w-full md:w-80 relative">
            <?php if ($statusFilter): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($statusFilter) ?>">
            <?php endif; ?>
            <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search Order #, UID, Customer..." class="w-full bg-[#141A2D] border border-slate-700/80 rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </form>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="/admin/orders.php" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= empty($statusFilter) ? 'bg-rose-600 text-white' : 'bg-[#0F1422] border border-slate-800 text-slate-400 hover:text-white' ?>">
            All (<?= $totalCount ?>)
        </a>
        <a href="/admin/orders.php?status=pending" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'pending' ? 'bg-amber-600 text-white' : 'bg-[#0F1422] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Pending Review (<?= $statusCounts['pending'] ?>)
        </a>
        <a href="/admin/orders.php?status=processing" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'processing' ? 'bg-cyan-600 text-white' : 'bg-[#0F1422] border border-slate-800 text-slate-400 hover:text-white' ?>">
            In Processing (<?= $statusCounts['processing'] ?>)
        </a>
        <a href="/admin/orders.php?status=completed" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'completed' ? 'bg-emerald-600 text-white' : 'bg-[#0F1422] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Completed (<?= $statusCounts['completed'] ?>)
        </a>
        <a href="/admin/orders.php?status=cancelled" class="px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'cancelled' ? 'bg-rose-900 text-white' : 'bg-[#0F1422] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Cancelled (<?= $statusCounts['cancelled'] ?>)
        </a>
    </div>

    <!-- Orders Table -->
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#141A2D] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Order ID</th>
                        <th class="py-3.5 px-4 font-bold">Customer Info</th>
                        <th class="py-3.5 px-4 font-bold">Service Ordered</th>
                        <th class="py-3.5 px-4 font-bold">Free Fire UID</th>
                        <th class="py-3.5 px-4 font-bold">Amount</th>
                        <th class="py-3.5 px-4 font-bold">Placed At</th>
                        <th class="py-3.5 px-4 font-bold">Status</th>
                        <th class="py-3.5 px-4 font-bold text-right">Update Order</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    <?php foreach ($orders as $order): 
                        $statusBadge = match($order['status']) {
                            'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                            'processing' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                            'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                            'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                            default => 'bg-slate-700 text-slate-300'
                        };
                    ?>
                        <tr class="hover:bg-[#141A2D]/50 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-white">
                                <?= htmlspecialchars($order['order_number']) ?>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-white"><?= htmlspecialchars($order['user_name']) ?></div>
                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($order['user_email']) ?></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="text-white font-semibold"><?= htmlspecialchars($order['service_title']) ?></div>
                                <div class="text-[10px] text-slate-400"><?= htmlspecialchars($order['amount_description']) ?></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-mono font-bold text-rose-400 flex items-center gap-1.5">
                                    <span><?= htmlspecialchars($order['player_uid']) ?></span>
                                    <button type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($order['player_uid']) ?>'); alert('UID Copied!');" class="text-[9px] bg-slate-800 hover:bg-slate-700 text-white px-1.5 py-0.5 rounded">Copy</button>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-white">
                                <?= formatCurrency((float)$order['amount']) ?>
                            </td>
                            <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                <?= date('d M, h:i A', strtotime($order['created_at'])) ?>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $statusBadge ?> uppercase">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" onclick="openAdminOrderModal(<?= $order['id'] ?>, '<?= htmlspecialchars($order['order_number']) ?>', '<?= htmlspecialchars($order['status']) ?>', '<?= addslashes(htmlspecialchars($order['admin_note'] ?? '')) ?>')" class="bg-rose-600/20 hover:bg-rose-600 border border-rose-500/30 text-rose-300 hover:text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                    Manage
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<!-- Admin Order Edit Modal -->
<div id="adminOrderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-[#111728] border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>Update Order Status: <span id="modal_order_title" class="font-mono text-rose-400"></span></span>
            </h3>
            <button type="button" onclick="closeAdminOrderModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/orders.php" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="update_order">
            <input type="hidden" name="order_id" id="modal_order_id" value="">

            <div>
                <label for="modal_status" class="block text-xs font-semibold text-slate-300 mb-1.5">Fulfillment Status</label>
                <select id="modal_status" name="status" class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                    <option value="pending">Pending (Awaiting Verification)</option>
                    <option value="processing">Processing (In Dispatch)</option>
                    <option value="completed">Completed (Delivered In-Game)</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div>
                <label for="modal_admin_note" class="block text-xs font-semibold text-slate-300 mb-1.5">Admin Delivery Note (Visible to Customer)</label>
                <textarea id="modal_admin_note" name="admin_note" rows="3" placeholder="e.g. Diamonds injected via official Garena server at 14:32 UTC." class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeAdminOrderModal()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-2.5 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAdminOrderModal(id, orderNumber, status, note) {
    document.getElementById('modal_order_id').value = id;
    document.getElementById('modal_order_title').innerText = orderNumber;
    document.getElementById('modal_status').value = status;
    document.getElementById('modal_admin_note').value = note;
    document.getElementById('adminOrderModal').classList.remove('hidden');
}

function closeAdminOrderModal() {
    document.getElementById('adminOrderModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
