<?php
/**
 * FF Panel V2 - Customer Wallet Dashboard & Transaction Ledger
 */
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

// Fetch filter
$typeFilter = trim($_GET['type'] ?? '');

$query = "SELECT * FROM wallet_transactions WHERE user_id = ?";
$params = [$currentUser['id']];

if ($typeFilter === 'credit' || $typeFilter === 'debit') {
    $query .= " AND type = ?";
    $params[] = $typeFilter;
}

$query .= " ORDER BY id DESC LIMIT 50";
$stmt = $db->prepare($query);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Calculate total credited & total spent
$statStmt = $db->prepare("SELECT 
    SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credited,
    SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_spent,
    COUNT(*) as total_txns
    FROM wallet_transactions WHERE user_id = ?");
$statStmt->execute([$currentUser['id']]);
$stats = $statStmt->fetch();

$activeNav = 'wallet';
$activeSidebar = 'wallet';
require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">

    <!-- Flash Message -->
    <?php $flash = getFlash(); if ($flash): ?>
        <div role="alert" class="p-4 rounded-2xl flex items-center justify-between text-xs font-semibold shadow-lg transition-all <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/40' : ($flash['type'] === 'info' ? 'bg-cyan-950/80 text-cyan-300 border border-cyan-500/40' : 'bg-rose-950/80 text-rose-300 border border-rose-500/40') ?>">
            <div class="flex items-center gap-2.5">
                <?php if ($flash['type'] === 'success'): ?>
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
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

    <!-- Wallet Overview Hero Card -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#161D32] via-[#101526] to-[#0A0E18] border border-rose-500/25 p-6 sm:p-8 shadow-2xl shadow-rose-950/40">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-rose-600/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-bold uppercase tracking-wider">
                    <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                    <span>Secure Wallet Balance</span>
                </div>
                <h1 class="text-xs text-slate-400 font-semibold uppercase tracking-wider">Available Balance</h1>
                <div class="font-gaming text-4xl sm:text-5xl font-black text-white tracking-tight flex items-baseline gap-2">
                    <span><?= formatCurrency((float)$currentUser['wallet_balance']) ?></span>
                    <span class="text-xs font-normal text-slate-400 uppercase tracking-normal">INR Live</span>
                </div>
                <p class="text-xs text-slate-400">Use this balance for instant diamond top-ups without payment gateway friction.</p>
            </div>

                    <div class="flex flex-wrap items-center gap-3">
                        <a href="/deposit" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs px-6 py-3.5 rounded-xl shadow-xl shadow-rose-600/30 hover:shadow-rose-600/50 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            <span>Add Funds to Wallet</span>
                        </a>
                        <a href="/services" class="inline-flex items-center gap-2 bg-[#141A2D] hover:bg-slate-800 text-slate-300 hover:text-white font-semibold text-xs px-5 py-3.5 rounded-xl border border-slate-700/80 transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>Browse Services</span>
                        </a>
                    </div>
                </div>

                <!-- Wallet Metrics Grid -->
                <div class="mt-8 pt-6 border-t border-slate-800/80 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-[#0D1220] border border-slate-800 rounded-2xl p-4">
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Lifetime Credited</div>
                        <div class="text-xl font-bold text-emerald-400 mt-1"><?= formatCurrency((float)($stats['total_credited'] ?? 0)) ?></div>
                    </div>
                    <div class="bg-[#0D1220] border border-slate-800 rounded-2xl p-4">
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Orders Spent</div>
                        <div class="text-xl font-bold text-rose-400 mt-1"><?= formatCurrency((float)($stats['total_spent'] ?? 0)) ?></div>
                    </div>
                    <div class="bg-[#0D1220] border border-slate-800 rounded-2xl p-4">
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Ledger Transactions</div>
                        <div class="text-xl font-bold text-white mt-1"><?= (int)($stats['total_txns'] ?? 0) ?> Records</div>
                    </div>
                </div>
            </div>

            <!-- Transactions Ledger Section -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-800/80">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-wide">Wallet Transaction History</h2>
                        <p class="text-xs text-slate-400">All credit and debit records are securely logged and verified.</p>
                    </div>

                    <!-- Filter Tabs -->
                    <div class="inline-flex items-center gap-1 bg-[#13192A] p-1 rounded-xl border border-slate-800">
                        <a href="/wallet" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?= empty($typeFilter) ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white' ?>">All</a>
                        <a href="/wallet?type=credit" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $typeFilter === 'credit' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-white' ?>">Credits (+)</a>
                        <a href="/wallet?type=debit" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all <?= $typeFilter === 'debit' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-white' ?>">Debits (-)</a>
                    </div>
                </div>

                <?php if (empty($transactions)): ?>
                    <div class="text-center py-12 space-y-3">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800/80 flex items-center justify-center text-slate-400">
                            <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-bold text-slate-300">No Transactions Found</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">You have no wallet transactions matching this filter yet.</p>
                        <a href="/deposit" class="inline-flex items-center gap-2 text-xs font-bold text-rose-400 hover:text-rose-300">Add funds to get started &rarr;</a>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800/80 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Date & Time</th>
                                    <th class="py-3 px-4">Type</th>
                                    <th class="py-3 px-4">Description</th>
                                    <th class="py-3 px-4">Reference ID</th>
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
                                            <?php if ($txn['type'] === 'credit'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 uppercase">
                                                    <span>+</span> Credit
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30 uppercase">
                                                    <span>-</span> Debit
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
                                            <span class="text-slate-500"><?= formatCurrency((float)$txn['balance_before']) ?></span>
                                            <span class="text-slate-400 mx-1">&rarr;</span>
                                            <span class="text-slate-200 font-semibold"><?= formatCurrency((float)$txn['balance_after']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

            </div>

</main>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
