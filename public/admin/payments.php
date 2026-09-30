<?php
/**
 * FF Panel V2 - Admin Payment & Deposit Verification
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

// Handle Approve / Reject Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $adminNote = trim($_POST['admin_note'] ?? '');

    $pStmt = $db->prepare("SELECT p.*, u.name as user_name, u.email as user_email FROM payments p JOIN users u ON p.user_id = u.id WHERE p.id = ?");
    $pStmt->execute([$paymentId]);
    $payment = $pStmt->fetch();

    if (!$payment) {
        setFlash('error', 'Deposit record not found.');
        header('Location: /admin/payments');
        exit;
    }

    if ($action === 'approve') {
        if ($payment['status'] === 'completed') {
            setFlash('error', 'This deposit has already been approved and credited.');
            header('Location: /admin/payments');
            exit;
        }

        try {
            $db->beginTransaction();

            // 1. Credit User's MySQL Wallet Balance with transaction record
            recordWalletTransaction(
                (int)$payment['user_id'],
                'credit',
                (float)$payment['net_amount'],
                "Deposit Verified via {$payment['gateway_code']} (UTR: {$payment['transaction_id']})",
                $payment['transaction_id'],
                $db
            );

            // 2. Update payment status to completed
            $upd = $db->prepare("UPDATE payments SET status = 'completed', admin_note = ?, verified_at = NOW() WHERE id = ?");
            $upd->execute([$adminNote ?: 'Verified and credited by Administrator', $paymentId]);

            // 3. Notify Customer
            createNotification(
                (int)$payment['user_id'],
                "Deposit Approved & Credited",
                "Your deposit of " . formatCurrency((float)$payment['net_amount']) . " (UTR: {$payment['transaction_id']}) has been approved and credited to your wallet balance.",
                "success",
                "/wallet",
                "user",
                $db
            );

            $db->commit();
            setFlash('success', "Deposit #{$payment['transaction_id']} approved! " . formatCurrency((float)$payment['net_amount']) . " credited to {$payment['user_name']}'s wallet.");

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            setFlash('error', 'Failed to approve deposit: ' . $e->getMessage());
        }

    } elseif ($action === 'reject') {
        if ($payment['status'] === 'completed') {
            setFlash('error', 'Cannot reject an already completed deposit.');
            header('Location: /admin/payments');
            exit;
        }

        $upd = $db->prepare("UPDATE payments SET status = 'failed', admin_note = ? WHERE id = ?");
        $upd->execute([$adminNote ?: 'Rejected by administrator (Invalid UTR or payment not received)', $paymentId]);

        createNotification(
            (int)$payment['user_id'],
            "Deposit Request Rejected",
            "Your deposit request of " . formatCurrency((float)$payment['net_amount']) . " (UTR: {$payment['transaction_id']}) was rejected: " . ($adminNote ?: 'Payment could not be verified on bank statement.'),
            "error",
            "/deposit",
            "user",
            $db
        );

        setFlash('info', "Deposit #{$payment['transaction_id']} rejected.");
    }

    header('Location: /admin/payments');
    exit;
}

// Filters & Search
$statusFilter = trim($_GET['status'] ?? '');
$search = trim($_GET['q'] ?? '');

$query = "SELECT p.*, u.name as user_name, u.email as user_email, g.name as gateway_name 
          FROM payments p 
          JOIN users u ON p.user_id = u.id 
          LEFT JOIN payment_gateways g ON p.gateway_code = g.code 
          WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $query .= " AND p.status = ?";
    $params[] = $statusFilter;
}

if (!empty($search)) {
    $query .= " AND (p.transaction_id LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$query .= " ORDER BY p.id DESC LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute($params);
$payments = $stmt->fetchAll();

// Counts
$pendingCount = (int)$db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();

$activeNav = 'payments';
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
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                        <span>Customer Deposits & Payments</span>
                        <?php if ($pendingCount > 0): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30 animate-pulse">
                                <?= $pendingCount ?> Pending Approval
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">Review bank UTR numbers, approve deposits to credit user wallets, or reject invalid attempts.</p>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl">
                <form action="/admin/payments" method="GET" class="flex flex-1 flex-col sm:flex-row items-center gap-3 w-full">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search UTR, user name or email..." class="w-full sm:w-72 bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                    
                    <select name="status" class="w-full sm:w-44 bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= $statusFilter === 'pending' ? 'selected' : '' ?>>⏳ Pending Only</option>
                        <option value="completed" <?= $statusFilter === 'completed' ? 'selected' : '' ?>>✓ Completed (Approved)</option>
                        <option value="failed" <?= $statusFilter === 'failed' ? 'selected' : '' ?>>✗ Failed / Rejected</option>
                    </select>

                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || !empty($statusFilter)): ?>
                        <a href="/admin/payments" class="text-xs text-slate-400 hover:text-white underline">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Payments Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <?php if (empty($payments)): ?>
                    <p class="text-xs text-slate-500 text-center py-10">No deposit records found matching your filters.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Gateway</th>
                                    <th class="py-3 px-4">Bank UTR / Ref</th>
                                    <th class="py-3 px-4 text-right">Net Amount</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($payments as $p): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                            <?= date('d M Y, h:i A', strtotime($p['created_at'])) ?>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="font-bold text-white"><?= htmlspecialchars($p['user_name']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($p['user_email']) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                            <?= htmlspecialchars($p['gateway_name'] ?? $p['gateway_code']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <code class="font-mono text-rose-400 font-bold bg-[#13192A] px-2 py-0.5 rounded border border-slate-800 text-[11px] select-all"><?= htmlspecialchars($p['transaction_id']) ?></code>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-bold text-white whitespace-nowrap">
                                            <?= formatCurrency((float)$p['net_amount']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                            <?php if ($p['status'] === 'completed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                    ✓ Approved
                                                </span>
                                            <?php elseif ($p['status'] === 'pending'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse">
                                                    ⏳ Pending
                                                </span>
                                            <?php elseif ($p['status'] === 'failed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                                    ✗ Rejected
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/15 text-slate-400 border border-slate-500/30">
                                                    <?= ucfirst($p['status']) ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                            <?php if ($p['status'] === 'pending'): ?>
                                                <div class="flex items-center justify-end gap-2">
                                                    <!-- Approve -->
                                                    <form action="/admin/payments" method="POST" onsubmit="return confirm('Approve deposit of <?= formatCurrency((float)$p['net_amount']) ?> and credit user wallet?');" class="inline">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                        <button type="submit" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg text-[11px] shadow-sm transition-colors">
                                                            Approve
                                                        </button>
                                                    </form>

                                                    <!-- Reject -->
                                                    <form action="/admin/payments" method="POST" onsubmit="return confirm('Reject deposit request?');" class="inline">
                                                        <?= csrfField() ?>
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                        <button type="submit" class="px-2.5 py-1 bg-rose-950/60 hover:bg-rose-900 text-rose-400 font-semibold rounded-lg text-[11px] border border-rose-800 transition-colors">
                                                            Reject
                                                        </button>
                                                    </form>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-[11px] text-slate-500 italic">
                                                    <?= $p['verified_at'] ? date('d M, h:i A', strtotime($p['verified_at'])) : 'Processed' ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

</main>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
