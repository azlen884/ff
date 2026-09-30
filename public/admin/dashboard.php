<?php
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'dashboard';
$pageTitle = 'Admin Operations Dashboard';

// Handle quick order status update from dashboard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'quick_status') {
    verifyCsrfToken();
    $orderId = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');
    $allowed = ['pending', 'processing', 'completed', 'cancelled'];

    if ($orderId > 0 && in_array($newStatus, $allowed, true)) {
        $update = $db->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
        $update->execute([$newStatus, $orderId]);
        setFlash('success', "Order #{$orderId} status changed to {$newStatus}.");
    }
    header('Location: /admin');
    exit;
}

// Calculate real metrics from MySQL
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$pendingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$processingOrders = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'processing'")->fetchColumn();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalServices = (int)$db->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();

// Fetch recent 8 orders
$recentOrders = $db->query("SELECT o.*, s.title as service_title, u.name as user_name 
                            FROM orders o 
                            JOIN services s ON o.service_id = s.id 
                            JOIN users u ON o.user_id = u.id 
                            ORDER BY o.created_at DESC 
                            LIMIT 8")->fetchAll();

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Top Stats Row (Real MySQL Data) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Revenue -->
        <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-5 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Total Revenue</span>
                <span class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400 text-xs font-bold font-mono">₹ INR</span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-white"><?= formatCurrency($totalRevenue) ?></div>
                <div class="text-[11px] text-emerald-400 mt-1 flex items-center gap-1">
                    <span>✓</span> Verified from completed orders
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-5 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Total Orders</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-white"><?= $totalOrders ?></div>
                <div class="text-[11px] text-slate-400 mt-1">
                    <?= $processingOrders ?> in processing queue
                </div>
            </div>
        </div>

        <!-- Pending Orders (Action Needed) -->
        <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-5 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Pending Review</span>
                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black <?= $pendingOrders > 0 ? 'text-amber-400' : 'text-white' ?>"><?= $pendingOrders ?></div>
                <div class="text-[11px] text-slate-400 mt-1">
                    Requires admin dispatch verification
                </div>
            </div>
        </div>

        <!-- Registered Users -->
        <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-5 shadow-xl">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-400">Registered Users</span>
                <div class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-black text-white"><?= $totalUsers ?></div>
                <div class="text-[11px] text-slate-400 mt-1">
                    <?= $totalServices ?> active products available
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Orders & Quick Status Management -->
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-white">Recent Top-Up Orders</h3>
                <p class="text-xs text-slate-400">Manage real customer Free Fire UID orders and update fulfillment status.</p>
            </div>
            <a href="/admin/orders" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-400 hover:text-rose-300">
                <span>View Full Order Management →</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#141A2D] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">Order #</th>
                        <th class="py-3 px-4 font-bold">Customer</th>
                        <th class="py-3 px-4 font-bold">Package</th>
                        <th class="py-3 px-4 font-bold">Free Fire UID</th>
                        <th class="py-3 px-4 font-bold">Amount</th>
                        <th class="py-3 px-4 font-bold">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Quick Status Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    <?php foreach ($recentOrders as $order): 
                        $statusBadge = match($order['status']) {
                            'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                            'processing' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                            'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                            'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                            default => 'bg-slate-700 text-slate-300'
                        };
                    ?>
                        <tr class="hover:bg-[#141A2D]/50 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-white">
                                <?= htmlspecialchars($order['order_number']) ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-bold text-white"><?= htmlspecialchars($order['user_name'] ?: 'Customer') ?></div>
                                <div class="text-[10px] text-slate-400"><?= date('M d, H:i', strtotime($order['created_at'])) ?></div>
                            </td>
                            <td class="py-3 px-4 text-slate-200">
                                <?= htmlspecialchars($order['service_title']) ?>
                            </td>
                            <td class="py-3 px-4 font-mono font-bold text-rose-400">
                                <?= htmlspecialchars($order['player_uid']) ?>
                            </td>
                            <td class="py-3 px-4 font-bold text-white">
                                <?= formatCurrency((float)$order['amount']) ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border <?= $statusBadge ?> uppercase">
                                    <?= htmlspecialchars($order['status']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="/admin" method="POST" class="inline-flex items-center gap-1.5">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="quick_status">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">

                                    <?php if ($order['status'] !== 'completed'): ?>
                                        <button type="submit" name="status" value="completed" class="bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-bold px-2 py-1 rounded transition-colors" title="Mark Completed">
                                            ✓ Done
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] !== 'processing'): ?>
                                        <button type="submit" name="status" value="processing" class="bg-cyan-600 hover:bg-cyan-500 text-white text-[10px] font-bold px-2 py-1 rounded transition-colors" title="Mark Processing">
                                            ⚙ Process
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($order['status'] !== 'cancelled'): ?>
                                        <button type="submit" name="status" value="cancelled" class="bg-rose-900/60 hover:bg-rose-700 text-rose-300 text-[10px] font-bold px-2 py-1 rounded transition-colors" title="Mark Cancelled">
                                            ✕
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
