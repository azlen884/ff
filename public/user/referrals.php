<?php
/**
 * FF Panel V2 - Customer Referral & Affiliate Program
 */
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();
$settings = getSiteSettings();

$referralEnabled = !empty($settings['referral_enabled']) && $settings['referral_enabled'] === '1';
$commissionPercent = (float)($settings['referral_commission_percent'] ?? 5.00);

// Referral Code
$refCode = $currentUser['referral_code'] ?? 'FF' . $currentUser['id'] . 'REF';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:3000';
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$referralLink = "{$protocol}://{$host}/register?ref=" . urlencode($refCode);

// Query referral statistics
$statsStmt = $db->prepare("SELECT 
    COUNT(DISTINCT u.id) as total_referrals,
    COALESCE(SUM(rc.commission_amount), 0) as total_earned
    FROM users u
    LEFT JOIN referral_commissions rc ON u.id = rc.referred_user_id AND rc.referrer_id = ?
    WHERE u.referred_by = ?");
$statsStmt->execute([$currentUser['id'], $currentUser['id']]);
$stats = $statsStmt->fetch();

// Query referral commission ledger
$commStmt = $db->prepare("SELECT rc.*, u.name as referred_name, o.order_number, o.amount as order_amount
    FROM referral_commissions rc
    JOIN users u ON rc.referred_user_id = u.id
    LEFT JOIN orders o ON rc.order_id = o.id
    WHERE rc.referrer_id = ?
    ORDER BY rc.id DESC LIMIT 30");
$commStmt->execute([$currentUser['id']]);
$commissions = $commStmt->fetchAll();

$activeNav = 'referrals';
$activeSidebar = 'referrals';
require_once __DIR__ . '/../../includes/user_header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
        
        <!-- Sidebar Navigation -->
        <div class="w-full lg:w-64 shrink-0">
            <?php require_once __DIR__ . '/../../includes/user_sidebar.php'; ?>
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

            <!-- Hero Referral Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#161D32] via-[#101526] to-[#0A0E18] border border-rose-500/25 p-6 sm:p-8 shadow-2xl space-y-6">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2 max-w-xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-bold uppercase tracking-wider">
                            <span>🎁</span>
                            <span>Earn <?= $commissionPercent ?>% Lifetime Cash Commission</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Invite Friends & Earn Real Wallet Cash</h1>
                        <p class="text-xs text-slate-300 leading-relaxed">
                            Share your referral code with fellow Free Fire players. Whenever they purchase diamonds or memberships, you automatically get <?= $commissionPercent ?>% of the order value credited straight to your wallet.
                        </p>
                    </div>

                    <div class="bg-[#0C101B] border border-slate-800 rounded-2xl p-5 text-center shrink-0">
                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Your Referral Code</div>
                        <div class="font-gaming text-3xl font-black text-rose-400 my-1 tracking-widest"><?= htmlspecialchars($refCode) ?></div>
                        <button type="button" onclick="copyToClipboard('<?= htmlspecialchars($refCode) ?>', 'Referral code copied!')" class="text-[11px] font-semibold text-slate-300 hover:text-white bg-slate-800 px-3 py-1 rounded-lg">
                            Copy Code
                        </button>
                    </div>
                </div>

                <!-- Shareable Link Input Box -->
                <div class="bg-[#0B0F1A] border border-slate-700/80 rounded-2xl p-4 space-y-2">
                    <label class="block text-xs font-semibold text-slate-300">Your Shareable Referral Link</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="<?= htmlspecialchars($referralLink) ?>" id="ref_link_input" class="w-full bg-[#13192A] border border-slate-800 rounded-xl px-4 py-2.5 text-xs text-slate-200 font-mono focus:outline-none">
                        <button type="button" onclick="copyToClipboard('<?= htmlspecialchars($referralLink) ?>', 'Referral link copied to clipboard!')" class="inline-flex items-center gap-1.5 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-lg shadow-rose-600/30 transition-all shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                            <span>Copy Link</span>
                        </button>
                    </div>
                </div>

                <!-- Stats Overview -->
                <div class="pt-4 border-t border-slate-800/80 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-[#0D1220] border border-slate-800 rounded-2xl p-4">
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Friends Registered</div>
                        <div class="text-2xl font-bold text-white mt-1"><?= (int)($stats['total_referrals'] ?? 0) ?> Users</div>
                    </div>
                    <div class="bg-[#0D1220] border border-slate-800 rounded-2xl p-4">
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Total Commission Earned</div>
                        <div class="text-2xl font-bold text-emerald-400 mt-1"><?= formatCurrency((float)($stats['total_earned'] ?? 0)) ?></div>
                    </div>
                    <div class="bg-[#0D1220] border border-slate-800 rounded-2xl p-4">
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Commission Rate</div>
                        <div class="text-2xl font-bold text-rose-400 mt-1"><?= $commissionPercent ?>% per Order</div>
                    </div>
                </div>

            </div>

            <!-- Referral Commission History -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                    <div>
                        <h2 class="text-base font-bold text-white tracking-wide">Referral Commission Ledger</h2>
                        <p class="text-xs text-slate-400">Detailed breakdown of every referral reward credited to your wallet balance.</p>
                    </div>
                </div>

                <?php if (empty($commissions)): ?>
                    <div class="text-center py-10 space-y-2">
                        <p class="text-xs text-slate-500">No referral commission records yet.</p>
                        <p class="text-[11px] text-slate-400">Share your link with your gaming squad to begin earning automatic commissions!</p>
                    </div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Referred User</th>
                                    <th class="py-3 px-4">Order Ref</th>
                                    <th class="py-3 px-4 text-right">Order Value</th>
                                    <th class="py-3 px-4 text-right">Rate</th>
                                    <th class="py-3 px-4 text-right">Credited Reward</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($commissions as $comm): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3 px-4 text-slate-300 whitespace-nowrap"><?= date('d M Y, h:i A', strtotime($comm['created_at'])) ?></td>
                                        <td class="py-3 px-4 text-white font-medium whitespace-nowrap"><?= htmlspecialchars($comm['referred_name']) ?></td>
                                        <td class="py-3 px-4 text-slate-400 font-mono text-[11px] whitespace-nowrap"><?= htmlspecialchars($comm['order_number'] ?? ('#ORD-' . $comm['order_id'])) ?></td>
                                        <td class="py-3 px-4 text-right text-slate-300 whitespace-nowrap"><?= formatCurrency((float)$comm['order_amount']) ?></td>
                                        <td class="py-3 px-4 text-right text-slate-400 whitespace-nowrap"><?= (float)$comm['commission_percentage'] ?>%</td>
                                        <td class="py-3 px-4 text-right font-bold text-emerald-400 whitespace-nowrap">+<?= formatCurrency((float)$comm['commission_amount']) ?></td>
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

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
