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

            <!-- Hero Referral Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#161D32] via-[#101526] to-[#0A0E18] border border-rose-500/25 p-6 sm:p-8 shadow-2xl space-y-6">
                
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-2 max-w-xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-rose-500/15 border border-rose-500/30 text-rose-400 text-xs font-bold uppercase tracking-wider">
                            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                            </svg>
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
