<?php
/**
 * FF Panel V2 - Admin Payment Gateways Configuration
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

// Handle Gateway Updates (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_status') {
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['is_enabled'] ?? 0);
        $stmt = $db->prepare("UPDATE payment_gateways SET is_enabled = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        setFlash('success', 'Gateway status updated successfully.');
        header('Location: /admin/gateways');
        exit;
    }

    if ($action === 'save_gateway') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim(strtolower($_POST['code'] ?? ''));
        $isEnabled = !empty($_POST['is_enabled']) ? 1 : 0;
        $upiId = trim($_POST['upi_id'] ?? '');
        $merchantId = trim($_POST['merchant_id'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $apiSecret = trim($_POST['api_secret'] ?? '');
        $qrImageUrl = trim($_POST['qr_image_url'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');
        $minDeposit = (float)($_POST['min_deposit'] ?? 50.00);
        $maxDeposit = (float)($_POST['max_deposit'] ?? 50000.00);
        $feePercentage = (float)($_POST['fee_percentage'] ?? 0.00);

        if (empty($name) || empty($code)) {
            setFlash('error', 'Gateway Name and Code are required.');
            header('Location: /admin/gateways');
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE payment_gateways SET name = ?, code = ?, is_enabled = ?, upi_id = ?, merchant_id = ?, api_key = ?, api_secret = ?, qr_image_url = ?, instructions = ?, min_deposit = ?, max_deposit = ?, fee_percentage = ? WHERE id = ?");
            $stmt->execute([$name, $code, $isEnabled, $upiId, $merchantId, $apiKey, $apiSecret, $qrImageUrl, $instructions, $minDeposit, $maxDeposit, $feePercentage, $id]);
            setFlash('success', "Gateway '{$name}' configuration updated.");
        } else {
            $stmt = $db->prepare("INSERT INTO payment_gateways (name, code, is_enabled, upi_id, merchant_id, api_key, api_secret, qr_image_url, instructions, min_deposit, max_deposit, fee_percentage) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $code, $isEnabled, $upiId, $merchantId, $apiKey, $apiSecret, $qrImageUrl, $instructions, $minDeposit, $maxDeposit, $feePercentage]);
            setFlash('success', "New gateway '{$name}' created.");
        }
        header('Location: /admin/gateways');
        exit;
    }
}

// Fetch all gateways
$gateways = $db->query("SELECT * FROM payment_gateways ORDER BY id ASC")->fetchAll();

$activeNav = 'gateways';
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Payment Gateways & Methods</h1>
                    <p class="text-xs text-slate-400 mt-1">Configure active customer payment options, UPI IDs, API keys, and deposit limits.</p>
                </div>
                <button type="button" onclick="openGatewayModal()" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Add New Gateway</span>
                </button>
            </div>

            <!-- Gateways List Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($gateways as $gw): ?>
                    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                            <div>
                                <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($gw['name']) ?></h3>
                                <code class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($gw['code']) ?></code>
                            </div>

                            <!-- Toggle Status Form -->
                            <form action="/admin/gateways" method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?= $gw['id'] ?>">
                                <input type="hidden" name="is_enabled" value="<?= $gw['is_enabled'] ? 0 : 1 ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all <?= $gw['is_enabled'] ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:text-white' ?>">
                                    <span class="w-2 h-2 rounded-full <?= $gw['is_enabled'] ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' ?>"></span>
                                    <span><?= $gw['is_enabled'] ? 'Enabled' : 'Disabled' ?></span>
                                </button>
                            </form>
                        </div>

                        <div class="space-y-2 text-xs text-slate-300">
                            <?php if (!empty($gw['upi_id'])): ?>
                                <div class="flex justify-between py-1 border-b border-slate-800/40">
                                    <span class="text-slate-500">UPI ID:</span>
                                    <span class="font-mono text-rose-400 font-bold"><?= htmlspecialchars($gw['upi_id']) ?></span>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($gw['merchant_id'])): ?>
                                <div class="flex justify-between py-1 border-b border-slate-800/40">
                                    <span class="text-slate-500">Merchant ID:</span>
                                    <span class="font-mono text-slate-300"><?= htmlspecialchars($gw['merchant_id']) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="flex justify-between py-1 border-b border-slate-800/40">
                                <span class="text-slate-500">Deposit Limits:</span>
                                <span><?= formatCurrency((float)$gw['min_deposit']) ?> - <?= formatCurrency((float)$gw['max_deposit']) ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-800/40">
                                <span class="text-slate-500">Gateway Fee:</span>
                                <span><?= (float)$gw['fee_percentage'] ?>%</span>
                            </div>
                        </div>

                        <?php if (!empty($gw['instructions'])): ?>
                            <div class="bg-[#111728] p-3 rounded-xl border border-slate-800/80 text-[11px] text-slate-400 line-clamp-2">
                                <?= htmlspecialchars($gw['instructions']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-end">
                            <button type="button" onclick='editGateway(<?= json_encode($gw) ?>)' class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-400 hover:text-rose-300 bg-[#13192A] hover:bg-slate-800 px-3.5 py-1.5 rounded-xl border border-slate-700/80 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Configure</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

    </div>
</div>

<!-- Modal: Gateway Edit / Create -->
<div id="gatewayModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-5 shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white" id="modalTitle">Configure Payment Gateway</h3>
            <button type="button" onclick="closeGatewayModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/gateways" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_gateway">
            <input type="hidden" name="id" id="gw_id" value="0">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Gateway Name *</label>
                    <input type="text" name="name" id="gw_name" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Gateway Code *</label>
                    <input type="text" name="code" id="gw_code" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">UPI ID (If UPI method)</label>
                    <input type="text" name="upi_id" id="gw_upi" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono" placeholder="merchant@upi">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Merchant ID / Account</label>
                    <input type="text" name="merchant_id" id="gw_merchant" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">API Key (Optional)</label>
                    <input type="text" name="api_key" id="gw_key" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">API Secret (Optional)</label>
                    <input type="password" name="api_secret" id="gw_secret" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Min Deposit (₹)</label>
                    <input type="number" name="min_deposit" id="gw_min" step="1" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Max Deposit (₹)</label>
                    <input type="number" name="max_deposit" id="gw_max" step="1" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Fee %</label>
                    <input type="number" name="fee_percentage" id="gw_fee" step="0.1" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Customer Instructions</label>
                <textarea name="instructions" id="gw_instr" rows="3" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_enabled" id="gw_enabled" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                <label for="gw_enabled" class="text-xs font-semibold text-slate-300">Enable this gateway for customer deposits</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeGatewayModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#FF2E51] hover:bg-rose-600 shadow-md shadow-rose-600/30">Save Gateway</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGatewayModal() {
    document.getElementById('modalTitle').innerText = 'Add New Payment Gateway';
    document.getElementById('gw_id').value = '0';
    document.getElementById('gw_name').value = '';
    document.getElementById('gw_code').value = '';
    document.getElementById('gw_upi').value = '';
    document.getElementById('gw_merchant').value = '';
    document.getElementById('gw_key').value = '';
    document.getElementById('gw_secret').value = '';
    document.getElementById('gw_min').value = '50';
    document.getElementById('gw_max').value = '50000';
    document.getElementById('gw_fee').value = '0';
    document.getElementById('gw_instr').value = '';
    document.getElementById('gw_enabled').checked = true;
    document.getElementById('gatewayModal').classList.remove('hidden');
}

function editGateway(gw) {
    document.getElementById('modalTitle').innerText = 'Configure ' + gw.name;
    document.getElementById('gw_id').value = gw.id;
    document.getElementById('gw_name').value = gw.name;
    document.getElementById('gw_code').value = gw.code;
    document.getElementById('gw_upi').value = gw.upi_id || '';
    document.getElementById('gw_merchant').value = gw.merchant_id || '';
    document.getElementById('gw_key').value = gw.api_key || '';
    document.getElementById('gw_secret').value = gw.api_secret || '';
    document.getElementById('gw_min').value = gw.min_deposit || '50';
    document.getElementById('gw_max').value = gw.max_deposit || '50000';
    document.getElementById('gw_fee').value = gw.fee_percentage || '0';
    document.getElementById('gw_instr').value = gw.instructions || '';
    document.getElementById('gw_enabled').checked = gw.is_enabled == 1;
    document.getElementById('gatewayModal').classList.remove('hidden');
}

function closeGatewayModal() {
    document.getElementById('gatewayModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
