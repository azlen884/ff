<?php
/**
 * FF Panel V2 - Customer Add Funds & Gateway Deposit
 */
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

// Fetch only ENABLED payment gateways
$gStmt = $db->query("SELECT * FROM payment_gateways WHERE is_enabled = 1 ORDER BY id ASC");
$gateways = $gStmt->fetchAll();

// Handle Deposit Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $gatewayCode = trim($_POST['gateway_code'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $transactionId = trim($_POST['transaction_id'] ?? ''); // UTR or Ref number
    $note = trim($_POST['user_note'] ?? '');

    // Validate gateway
    $gwStmt = $db->prepare("SELECT * FROM payment_gateways WHERE code = ? AND is_enabled = 1");
    $gwStmt->execute([$gatewayCode]);
    $selectedGateway = $gwStmt->fetch();

    if (!$selectedGateway) {
        setFlash('error', 'Selected payment method is currently disabled or invalid.');
        header('Location: /deposit');
        exit;
    }

    $minDeposit = (float)$selectedGateway['min_deposit'];
    $maxDeposit = (float)$selectedGateway['max_deposit'];

    if ($amount < $minDeposit) {
        setFlash('error', "Minimum deposit amount for {$selectedGateway['name']} is " . formatCurrency($minDeposit));
        header('Location: /deposit');
        exit;
    }

    if ($amount > $maxDeposit) {
        setFlash('error', "Maximum deposit amount for {$selectedGateway['name']} is " . formatCurrency($maxDeposit));
        header('Location: /deposit');
        exit;
    }

    if (empty($transactionId) || strlen($transactionId) < 6) {
        setFlash('error', 'Please provide a valid UTR, Transaction ID, or Reference Number (at least 6 characters).');
        header('Location: /deposit');
        exit;
    }

    // Check duplicate UTR / Reference ID in payments table
    $chkStmt = $db->prepare("SELECT id, status FROM payments WHERE transaction_id = ?");
    $chkStmt->execute([$transactionId]);
    $existing = $chkStmt->fetch();

    if ($existing) {
        setFlash('error', "This Transaction ID / UTR ({$transactionId}) has already been submitted. Status: " . ucfirst($existing['status']));
        header('Location: /deposit');
        exit;
    }

    // Calculate fee
    $feePercent = (float)($selectedGateway['fee_percentage'] ?? 0.00);
    $feeAmount = round($amount * ($feePercent / 100), 2);
    $netAmount = max(0, $amount - $feeAmount);

    try {
        $ins = $db->prepare("INSERT INTO payments (user_id, gateway_code, transaction_id, amount, fee, net_amount, status, payment_details, created_at) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, NOW())");
        $detailsJson = json_encode([
            'gateway_name' => $selectedGateway['name'],
            'upi_id' => $selectedGateway['upi_id'] ?? null,
            'user_note' => $note,
            'user_email' => $currentUser['email']
        ]);
        $ins->execute([
            $currentUser['id'],
            $gatewayCode,
            $transactionId,
            $amount,
            $feeAmount,
            $netAmount,
            $detailsJson
        ]);
        $depositId = (int)$db->lastInsertId();

        // Notification for user
        createNotification(
            $currentUser['id'],
            "Deposit Request Submitted",
            "Your deposit request of " . formatCurrency($netAmount) . " (UTR: {$transactionId}) has been received and is pending admin verification.",
            "info",
            "/deposit",
            "user",
            $db
        );

        // Notification for admin
        createNotification(
            null,
            "New Deposit Pending Verification",
            "User {$currentUser['name']} submitted a deposit of " . formatCurrency($netAmount) . " via {$selectedGateway['name']} (UTR: {$transactionId}).",
            "warning",
            "/admin/payments",
            "admin",
            $db
        );

        setFlash('success', "Deposit request for " . formatCurrency($netAmount) . " submitted successfully! Your wallet balance will update as soon as the UTR is verified.");
        header('Location: /deposit');
        exit;

    } catch (Exception $e) {
        setFlash('error', 'Failed to submit deposit request: ' . $e->getMessage());
        header('Location: /deposit');
        exit;
    }
}

// Fetch user's recent deposits
$depStmt = $db->prepare("SELECT p.*, g.name as gateway_name FROM payments p LEFT JOIN payment_gateways g ON p.gateway_code = g.code WHERE p.user_id = ? ORDER BY p.id DESC LIMIT 20");
$depStmt->execute([$currentUser['id']]);
$userDeposits = $depStmt->fetchAll();

$activeNav = 'wallet';
$activeSidebar = 'deposit';
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

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Add Funds to Wallet</h1>
            <p class="text-xs text-slate-400 mt-1">Select an active payment method below and enter your payment transaction reference.</p>
        </div>
        <div class="text-right">
            <div class="text-[11px] text-slate-400 font-semibold uppercase">Current Balance</div>
            <div class="font-gaming text-xl sm:text-2xl font-bold text-white"><?= formatCurrency((float)$currentUser['wallet_balance']) ?></div>
        </div>
    </div>

            <?php if (empty($gateways)): ?>
                <div class="bg-[#0D121F] border border-amber-500/30 rounded-2xl p-8 text-center space-y-3">
                    <div class="w-12 h-12 mx-auto rounded-2xl bg-amber-500/15 text-amber-400 flex items-center justify-center text-xl">⚠️</div>
                    <h3 class="text-base font-bold text-white">No Active Payment Gateways</h3>
                    <p class="text-xs text-slate-400 max-w-md mx-auto">Payment gateways are currently being configured by administrators. Please contact support or check back shortly.</p>
                </div>
            <?php else: ?>
                <!-- Grid of Enabled Gateways -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <?php foreach ($gateways as $idx => $gw): ?>
                        <div class="bg-[#0D121F] border border-slate-800 rounded-3xl p-6 shadow-xl space-y-5 flex flex-col justify-between">
                            
                            <div class="space-y-4">
                                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500/20 to-red-600/30 border border-rose-500/30 flex items-center justify-center text-rose-400 font-bold">
                                            ₹
                                        </div>
                                        <div>
                                            <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($gw['name']) ?></h3>
                                            <span class="text-[10px] text-emerald-400 font-semibold bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20">Active Gateway</span>
                                        </div>
                                    </div>
                                    <div class="text-right text-[11px] text-slate-400">
                                        <div>Min: <?= formatCurrency((float)$gw['min_deposit']) ?></div>
                                        <div>Max: <?= formatCurrency((float)$gw['max_deposit']) ?></div>
                                    </div>
                                </div>

                                <!-- Gateway Specific Instructions / Details -->
                                <?php if (!empty($gw['upi_id'])): ?>
                                    <div class="bg-[#111728] border border-slate-700/80 rounded-2xl p-4 space-y-2">
                                        <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Official UPI ID:</div>
                                        <div class="flex items-center justify-between bg-[#0A0D16] px-3.5 py-2 rounded-xl border border-slate-800">
                                            <code class="text-xs font-mono font-bold text-rose-400" id="upi_val_<?= $gw['id'] ?>"><?= htmlspecialchars($gw['upi_id']) ?></code>
                                            <button type="button" onclick="copyToClipboard('<?= htmlspecialchars($gw['upi_id']) ?>', 'UPI ID copied to clipboard!')" class="text-slate-400 hover:text-white text-xs font-semibold px-2 py-1 rounded bg-slate-800 hover:bg-slate-700 transition-colors">
                                                Copy
                                            </button>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($gw['instructions'])): ?>
                                    <div class="text-xs text-slate-300 bg-[#121829] p-3.5 rounded-xl border border-slate-800 leading-relaxed">
                                        <?= nl2br(htmlspecialchars($gw['instructions'])) ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Deposit Form -->
                            <form action="/deposit" method="POST" class="pt-4 border-t border-slate-800/80 space-y-3.5">
                                <?= csrfField() ?>
                                <input type="hidden" name="gateway_code" value="<?= htmlspecialchars($gw['code']) ?>">

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Deposit Amount (₹) *</label>
                                    <input type="number" name="amount" min="<?= (float)$gw['min_deposit'] ?>" max="<?= (float)$gw['max_deposit'] ?>" step="1" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors font-bold text-base" placeholder="e.g. 500">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-300 mb-1">Bank UTR / Transaction ID *</label>
                                    <input type="text" name="transaction_id" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors font-mono uppercase" placeholder="e.g. 428919284712">
                                    <p class="text-[10px] text-slate-500 mt-1">Found on your GPay / PhonePe / Paytm payment receipt.</p>
                                </div>

                                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                                    <span>Submit Deposit for Verification</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </button>
                            </form>

                        </div>
                    <?php endforeach; ?>

                </div>
            <?php endif; ?>

            <!-- User Recent Deposits Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                    <div>
                        <h2 class="text-sm font-bold text-white tracking-wide">My Deposit Submissions</h2>
                        <p class="text-xs text-slate-400">Track the verification status of your recharge requests.</p>
                    </div>
                    <a href="/wallet" class="text-xs font-bold text-rose-400 hover:text-rose-300">View Full Wallet Ledger &rarr;</a>
                </div>

                <?php if (empty($userDeposits)): ?>
                    <p class="text-xs text-slate-500 text-center py-6">You have not submitted any deposit requests yet.</p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                    <th class="py-3 px-4">Date</th>
                                    <th class="py-3 px-4">Gateway</th>
                                    <th class="py-3 px-4">UTR / Reference</th>
                                    <th class="py-3 px-4 text-right">Amount</th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/50">
                                <?php foreach ($userDeposits as $dep): ?>
                                    <tr class="hover:bg-slate-800/20 transition-colors">
                                        <td class="py-3 px-4 text-slate-300 whitespace-nowrap"><?= date('d M Y, h:i A', strtotime($dep['created_at'])) ?></td>
                                        <td class="py-3 px-4 text-white font-medium whitespace-nowrap"><?= htmlspecialchars($dep['gateway_name'] ?? $dep['gateway_code']) ?></td>
                                        <td class="py-3 px-4 text-slate-300 font-mono text-[11px] whitespace-nowrap"><?= htmlspecialchars($dep['transaction_id']) ?></td>
                                        <td class="py-3 px-4 text-right font-bold text-white whitespace-nowrap"><?= formatCurrency((float)$dep['net_amount']) ?></td>
                                        <td class="py-3 px-4 text-center whitespace-nowrap">
                                            <?php if ($dep['status'] === 'completed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                                    <span>✓</span> Approved
                                                </span>
                                            <?php elseif ($dep['status'] === 'pending'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30 animate-pulse">
                                                    <span>⏳</span> Pending Verification
                                                </span>
                                            <?php elseif ($dep['status'] === 'failed'): ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                                    <span>✗</span> Rejected
                                                </span>
                                            <?php else: ?>
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-500/15 text-slate-400 border border-slate-500/30">
                                                    <?= ucfirst($dep['status']) ?>
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

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
