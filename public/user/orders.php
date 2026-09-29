<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

$activeNav = 'orders';
$activeSidebar = 'orders';
$pageTitle = 'My Order History';

$statusFilter = trim($_GET['status'] ?? '');

$sql = "SELECT o.*, s.title as service_title, s.amount_description 
        FROM orders o 
        JOIN services s ON o.service_id = s.id 
        WHERE o.user_id = ?";
$params = [$currentUser['id']];

if (!empty($statusFilter)) {
    $sql .= " AND o.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Counts for filter pills
$countsStmt = $db->prepare("SELECT status, COUNT(*) as cnt FROM orders WHERE user_id = ? GROUP BY status");
$countsStmt->execute([$currentUser['id']]);
$statusCounts = ['completed' => 0, 'processing' => 0, 'pending' => 0, 'cancelled' => 0];
foreach ($countsStmt->fetchAll() as $row) {
    $statusCounts[$row['status']] = (int)$row['cnt'];
}
$totalUserOrders = array_sum($statusCounts);

require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Header -->
    <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div>
            <h2 class="text-2xl font-extrabold text-white">Order History & Logs</h2>
            <p class="text-xs text-slate-400 mt-1">
                Real-time tracking of all Free Fire diamond top-ups and passes placed on your account.
            </p>
        </div>
        <a href="/user/services.php" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
            <span>+ Place New Order</span>
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="/user/orders.php" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= empty($statusFilter) ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
            All (<?= $totalUserOrders ?>)
        </a>
        <a href="/user/orders.php?status=completed" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'completed' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Completed (<?= $statusCounts['completed'] ?>)
        </a>
        <a href="/user/orders.php?status=processing" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'processing' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Processing (<?= $statusCounts['processing'] ?>)
        </a>
        <a href="/user/orders.php?status=pending" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'pending' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Pending (<?= $statusCounts['pending'] ?>)
        </a>
        <a href="/user/orders.php?status=cancelled" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $statusFilter === 'cancelled' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
            Cancelled (<?= $statusCounts['cancelled'] ?>)
        </a>
    </div>

    <!-- Orders Content -->
    <?php if (empty($orders)): ?>
        <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
            <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500 mb-3 text-xl">📦</div>
            <h4 class="text-base font-bold text-white mb-1">No orders found</h4>
            <p class="text-xs">You have not placed any orders under this filter.</p>
            <a href="/user/services.php" class="inline-block mt-4 text-xs font-bold text-rose-400 hover:text-rose-300">Browse Free Fire Services →</a>
        </div>
    <?php else: ?>
        <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-[#111728] border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 font-bold">Order ID</th>
                            <th class="py-3.5 px-4 font-bold">Service Item</th>
                            <th class="py-3.5 px-4 font-bold">Player UID</th>
                            <th class="py-3.5 px-4 font-bold">Amount</th>
                            <th class="py-3.5 px-4 font-bold">Date & Time</th>
                            <th class="py-3.5 px-4 font-bold">Status</th>
                            <th class="py-3.5 px-4 font-bold text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        <?php foreach ($orders as $o): 
                            $statusBadge = match($o['status']) {
                                'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                'processing' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                default => 'bg-slate-700 text-slate-300'
                            };
                        ?>
                            <tr class="hover:bg-[#13192A]/50 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-white">
                                    <?= htmlspecialchars($o['order_number']) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-white"><?= htmlspecialchars($o['service_title']) ?></div>
                                    <div class="text-[10px] text-slate-400"><?= htmlspecialchars($o['amount_description']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-200">
                                    <?= htmlspecialchars($o['player_uid']) ?>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-white">
                                    <?= formatCurrency((float)$o['amount']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-slate-400 text-[11px]">
                                    <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $statusBadge ?> capitalize">
                                        <?= htmlspecialchars($o['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="/user/order-detail.php?id=<?= $o['id'] ?>" class="inline-flex items-center gap-1 bg-[#141A2D] hover:bg-slate-700 text-slate-200 hover:text-white px-2.5 py-1 rounded-lg border border-slate-700 text-[11px] font-semibold transition-colors">
                                        <span>Details</span>
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
