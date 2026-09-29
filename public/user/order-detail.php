<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

$orderId = (int)($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT o.*, s.title as service_title, s.amount_description, s.delivery_time, c.name as category_name 
                      FROM orders o 
                      JOIN services s ON o.service_id = s.id 
                      JOIN categories c ON s.category_id = c.id 
                      WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$orderId, $currentUser['id']]);
$order = $stmt->fetch();

if (!$order) {
    setFlash('error', 'Order not found or you do not have permission to view it.');
    header('Location: /user/orders.php');
    exit;
}

$activeNav = 'orders';
$activeSidebar = 'orders';
$pageTitle = 'Order #' . $order['order_number'];

require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Header with Back Button -->
    <div class="flex items-center justify-between gap-4">
        <a href="/user/orders.php" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to My Orders</span>
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 bg-[#13192A] hover:bg-slate-800 border border-slate-700/80 text-slate-300 hover:text-white text-xs px-3.5 py-1.5 rounded-xl font-semibold transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Print Receipt</span>
        </button>
    </div>

    <!-- Order Card Overview -->
    <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-8 shadow-2xl space-y-8">
        
        <!-- Top Info Row -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-rose-500 mb-1">Free Fire Direct Top-Up</div>
                <h2 class="text-xl sm:text-2xl font-black text-white font-mono flex items-center gap-3">
                    <span><?= htmlspecialchars($order['order_number']) ?></span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">Placed on <?= date('d F Y \a\t h:i A', strtotime($order['created_at'])) ?></p>
            </div>
            <div>
                <?php
                $statusBadge = match($order['status']) {
                    'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                    'processing' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
                    'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                    'cancelled' => 'bg-rose-500/10 text-rose-400 border-rose-500/30',
                    default => 'bg-slate-700 text-slate-300'
                };
                ?>
                <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold border <?= $statusBadge ?> uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                    <?= htmlspecialchars($order['status']) ?>
                </span>
            </div>
        </div>

        <!-- 4-Step Order Progress Timeline -->
        <div class="bg-[#111728] border border-slate-800/80 rounded-2xl p-6">
            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-6">Fulfillment Timeline</h4>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 relative">
                <!-- Step 1 -->
                <div class="flex sm:flex-col items-center sm:text-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                    <div>
                        <div class="text-xs font-bold text-white">Order Created</div>
                        <div class="text-[10px] text-slate-400">Payment Verified</div>
                    </div>
                </div>
                <!-- Step 2 -->
                <div class="flex sm:flex-col items-center sm:text-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                    <div>
                        <div class="text-xs font-bold text-white">UID Validated</div>
                        <div class="text-[10px] text-slate-400">Garena Indian Server</div>
                    </div>
                </div>
                <!-- Step 3 -->
                <div class="flex sm:flex-col items-center sm:text-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center font-bold text-xs shrink-0">✓</div>
                    <div>
                        <div class="text-xs font-bold text-white">Direct Dispatch</div>
                        <div class="text-[10px] text-slate-400">Diamonds Injected</div>
                    </div>
                </div>
                <!-- Step 4 -->
                <div class="flex sm:flex-col items-center sm:text-center gap-3">
                    <div class="w-9 h-9 rounded-full <?= $order['status'] === 'completed' ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-slate-500' ?> flex items-center justify-center font-bold text-xs shrink-0">
                        <?= $order['status'] === 'completed' ? '✓' : '4' ?>
                    </div>
                    <div>
                        <div class="text-xs font-bold text-white"><?= ucfirst($order['status']) ?></div>
                        <div class="text-[10px] text-slate-400">In-Game Ready</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Breakdown Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- Service & Player Information -->
            <div class="bg-[#111728] border border-slate-800/80 rounded-2xl p-5 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Delivery Details</h4>
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Item Purchased:</span>
                        <strong class="text-white"><?= htmlspecialchars($order['service_title']) ?></strong>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Package Spec:</span>
                        <span class="text-slate-200"><?= htmlspecialchars($order['amount_description']) ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Player Free Fire UID:</span>
                        <span class="font-mono font-bold text-rose-400 flex items-center gap-2">
                            <span><?= htmlspecialchars($order['player_uid']) ?></span>
                            <button type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($order['player_uid']) ?>'); alert('UID copied!');" class="text-[10px] bg-slate-800 hover:bg-slate-700 px-1.5 py-0.5 rounded text-white transition-colors">Copy</button>
                        </span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Player Nickname:</span>
                        <span class="text-slate-200"><?= htmlspecialchars($order['player_name'] ?: 'Verified User') ?></span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400">Delivery SLA:</span>
                        <span class="text-emerald-400 font-semibold"><?= htmlspecialchars($order['delivery_time'] ?? 'Instant (< 2 Mins)') ?></span>
                    </div>
                </div>
            </div>

            <!-- Billing & Payment Information -->
            <div class="bg-[#111728] border border-slate-800/80 rounded-2xl p-5 space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Payment Breakdown</h4>
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Item Subtotal:</span>
                        <span class="text-white"><?= formatCurrency((float)$order['amount']) ?></span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Taxes & Server Gateway Fees:</span>
                        <span class="text-emerald-400 font-semibold">₹ 0.00 (Free)</span>
                    </div>
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <span class="text-slate-400">Payment Channel:</span>
                        <span class="text-white"><?= htmlspecialchars($order['payment_method']) ?></span>
                    </div>
                    <div class="flex items-center justify-between pt-2">
                        <span class="text-sm font-bold text-white">Total Amount Paid:</span>
                        <span class="text-lg font-black text-rose-400"><?= formatCurrency((float)$order['amount']) ?></span>
                    </div>
                </div>
            </div>

        </div>

        <?php if (!empty($order['admin_note'])): ?>
            <div class="bg-slate-900/60 border border-slate-800 rounded-xl p-4 text-xs">
                <span class="font-bold text-slate-300">Admin Note:</span>
                <p class="text-slate-400 mt-1"><?= htmlspecialchars($order['admin_note']) ?></p>
            </div>
        <?php endif; ?>

    </div>

</main>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
