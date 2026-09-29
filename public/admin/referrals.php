<?php
/**
 * FF Panel V2 - Admin Referral Program Manager
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

// Summary stats
$totalReferredUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE referred_by IS NOT NULL")->fetchColumn();
$totalCommissionsPaid = (float)$db->query("SELECT COALESCE(SUM(commission_amount), 0) FROM referral_commissions WHERE status = 'credited'")->fetchColumn();
$settings = getSiteSettings();

// Top Referrers
$topReferrers = $db->query("SELECT u.id, u.name, u.email, u.referral_code, 
    COUNT(r.id) as referred_count,
    COALESCE(SUM(rc.commission_amount), 0) as total_earned
    FROM users u
    JOIN users r ON u.id = r.referred_by
    LEFT JOIN referral_commissions rc ON u.id = rc.referrer_id
    GROUP BY u.id
    ORDER BY total_earned DESC, referred_count DESC
    LIMIT 20")->fetchAll();

// Recent Commissions
$commissions = $db->query("SELECT rc.*, ref.name as referrer_name, ref.email as referrer_email, 
    buyer.name as buyer_name, o.order_number, o.amount as order_amount
    FROM referral_commissions rc
    JOIN users ref ON rc.referrer_id = ref.id
    JOIN users buyer ON rc.referred_user_id = buyer.id
    LEFT JOIN orders o ON rc.order_id = o.id
    ORDER BY rc.id DESC LIMIT 50")->fetchAll();

$activeNav = 'referrals';
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
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Referral & Affiliate Analytics</h1>
                    <p class="text-xs text-slate-400 mt-1">Monitor partner referrals, commission payouts, and viral user growth.</p>
                </div>
                <a href="/admin/settings" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-200 rounded-xl transition-colors">
                    <span>Configure Referral Rate</span> &rarr;
                </a>
            </div>

            <!-- Stats Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Referred Customers</div>
                    <div class="text-2xl font-black text-white mt-1"><?= $totalReferredUsers ?> Users</div>
                </div>
                <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Commission Credited</div>
                    <div class="text-2xl font-black text-emerald-400 mt-1"><?= formatCurrency($totalCommissionsPaid) ?></div>
                </div>
                <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-5 shadow-xl">
                    <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Current Program Rate</div>
                    <div class="text-2xl font-black text-rose-400 mt-1"><?= (float)($settings['referral_commission_percent'] ?? 5) ?>%</div>
                </div>
            </div>

            <!-- Top Referrers Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <h2 class="text-sm font-bold text-white tracking-wide">Top Affiliates & Referrers</h2>

                <?php if (empty($topReferrers)): ?>
                    <p class="text-xs text-slate-500 text-center py-6">No referral data recorded yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Affiliate Name</th>
                                    <th class="py-3 px-4">Referral Code</th>
                                    <th class="py-3 px-4 text-center">Invited Users</th>
                                    <th class="py-3 px-4 text-right">Commissions Paid</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($topReferrers as $top): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3 px-4 whitespace-nowrap">
                                            <div class="font-bold text-white"><?= htmlspecialchars($top['name']) ?></div>
                                            <div class="text-[11px] text-slate-400"><?= htmlspecialchars($top['email']) ?></div>
                                        </td>
                                        <td class="py-3 px-4 font-mono font-bold text-rose-400 whitespace-nowrap">
                                            <?= htmlspecialchars($top['referral_code']) ?>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-white whitespace-nowrap">
                                            <?= (int)$top['referred_count'] ?>
                                        </td>
                                        <td class="py-3 px-4 text-right font-bold text-emerald-400 whitespace-nowrap">
                                            <?= formatCurrency((float)$top['total_earned']) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Recent Commissions Ledger -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <h2 class="text-sm font-bold text-white tracking-wide">Recent Commission Transactions</h2>

                <?php if (empty($commissions)): ?>
                    <p class="text-xs text-slate-500 text-center py-6">No commission transactions yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Beneficiary (Referrer)</th>
                                    <th class="py-3 px-4">Referred Buyer</th>
                                    <th class="py-3 px-4">Order Ref</th>
                                    <th class="py-3 px-4 text-right">Order Amount</th>
                                    <th class="py-3 px-4 text-right">Credited Reward</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($commissions as $c): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3 px-4 text-slate-300 whitespace-nowrap"><?= date('d M Y, h:i A', strtotime($c['created_at'])) ?></td>
                                        <td class="py-3 px-4 font-bold text-white whitespace-nowrap"><?= htmlspecialchars($c['referrer_name']) ?></td>
                                        <td class="py-3 px-4 text-slate-300 whitespace-nowrap"><?= htmlspecialchars($c['buyer_name']) ?></td>
                                        <td class="py-3 px-4 font-mono text-[11px] text-slate-400 whitespace-nowrap"><?= htmlspecialchars($c['order_number'] ?? ('#ORD-' . $c['order_id'])) ?></td>
                                        <td class="py-3 px-4 text-right text-slate-300 whitespace-nowrap"><?= formatCurrency((float)$c['order_amount']) ?></td>
                                        <td class="py-3 px-4 text-right font-bold text-emerald-400 whitespace-nowrap">+<?= formatCurrency((float)$c['commission_amount']) ?></td>
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

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
