<?php
/**
 * FF Panel V2 - Admin Global Wallet Transactions Ledger
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$search = trim($_GET['q'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');

$query = "SELECT wt.*, u.name as user_name, u.email as user_email 
          FROM wallet_transactions wt 
          JOIN users u ON wt.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($typeFilter === 'credit' || $typeFilter === 'debit') {
    $query .= " AND wt.type = ?";
    $params[] = $typeFilter;
}

if (!empty($search)) {
    $query .= " AND (u.name LIKE ? OR u.email LIKE ? OR wt.reference_id LIKE ? OR wt.description LIKE ?)";
    $like = "%{$search}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$query .= " ORDER BY wt.id DESC LIMIT 100";
$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Global stats
$totalCredits = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE type = 'credit'")->fetchColumn();
$totalDebits = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM wallet_transactions WHERE type = 'debit'")->fetchColumn();

$activeNav = 'wallet-transactions';
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
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Global Wallet Transactions Ledger</h1>
                    <p class="text-xs text-slate-400 mt-1">Audit trail of all customer wallet deposits, order debits, refunds, and referral commissions.</p>
                </div>
            </div>

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Lifetime Wallet Inflows (Credits)</div>
                    <div class="text-2xl font-black text-emerald-400 mt-1">+<?= formatCurrency($totalCredits) ?></div>
                </div>
                <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Lifetime Wallet Outflows (Debits)</div>
                    <div class="text-2xl font-black text-rose-400 mt-1">-<?= formatCurrency($totalDebits) ?></div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row items-center justify-between gap-4 shadow-xl">
                <form action="/admin/wallet-transactions" method="GET" class="flex flex-1 flex-col sm:flex-row items-center gap-3 w-full">
                    <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search user name, email, or reference..." class="w-full sm:w-72 bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                    
                    <select name="type" class="w-full sm:w-44 bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                        <option value="">All Types</option>
                        <option value="credit" <?= $typeFilter === 'credit' ? 'selected' : '' ?>>Credits (+)</option>
                        <option value="debit" <?= $typeFilter === 'debit' ? 'selected' : '' ?>>Debits (-)</option>
                    </select>

                    <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold">Filter</button>
                    <?php if (!empty($search) || !empty($typeFilter)): ?>
                        <a href="/admin/wallet-transactions" class="text-xs text-slate-400 hover:text-white underline">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Transactions Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <?php if (empty($transactions)): ?>
                    <p class="text-xs text-slate-500 text-center py-10">No wallet transactions found.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Customer</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Description</th>
                                    <th class="py-3 px-4">Reference</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-right">Balance Change</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($transactions as $txn): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                            <?= date('d M Y, h:i A', strtotime($txn['created_at'])) ?>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <div class="font-bold text-white"><?= htmlspecialchars($txn['user_name']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($txn['user_email']) ?></div>
                                        </td>
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <?php if ($txn['type'] === 'credit'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                    + Credit
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                                    - Debit
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-white font-medium max-w-xs truncate" title="<?= htmlspecialchars($txn['description']) ?>">
                                            <?= htmlspecialchars($txn['description']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                                            <?= htmlspecialchars($txn['reference_id'] ?? '—') ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right font-bold whitespace-nowrap <?= $txn['type'] === 'credit' ? 'text-emerald-400' : 'text-rose-400' ?>">
                                            <?= ($txn['type'] === 'credit' ? '+' : '-') . formatCurrency((float)$txn['amount']) ?>
                                        </td>
                                        <td class="py-3.5 px-4 text-right text-slate-400 text-[11px] whitespace-nowrap">
                                            <span><?= formatCurrency((float)$txn['balance_before']) ?></span>
                                            <span class="mx-1">&rarr;</span>
                                            <span class="text-white font-semibold"><?= formatCurrency((float)$txn['balance_after']) ?></span>
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
