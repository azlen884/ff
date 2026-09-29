<?php
/**
 * FF Panel V2 - Admin Game API Providers Management
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'toggle_provider') {
        $id = (int)($_POST['id'] ?? 0);
        $status = (int)($_POST['is_enabled'] ?? 0);
        $stmt = $db->prepare("UPDATE providers SET is_enabled = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        setFlash('success', 'Provider status updated.');
        header('Location: /admin/providers');
        exit;
    }

    if ($action === 'save_provider') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim(strtolower($_POST['code'] ?? ''));
        $apiUrl = trim($_POST['api_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');
        $apiSecret = trim($_POST['api_secret'] ?? '');
        $currency = trim($_POST['currency'] ?? 'INR');
        $isEnabled = !empty($_POST['is_enabled']) ? 1 : 0;

        if (empty($name) || empty($code)) {
            setFlash('error', 'Provider Name and Code are required.');
            header('Location: /admin/providers');
            exit;
        }

        if ($id > 0) {
            $stmt = $db->prepare("UPDATE providers SET name = ?, code = ?, api_url = ?, api_key = ?, api_secret = ?, currency = ?, is_enabled = ? WHERE id = ?");
            $stmt->execute([$name, $code, $apiUrl, $apiKey, $apiSecret, $currency, $isEnabled, $id]);
            setFlash('success', "Provider '{$name}' updated.");
        } else {
            $stmt = $db->prepare("INSERT INTO providers (name, code, api_url, api_key, api_secret, currency, is_enabled) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $code, $apiUrl, $apiKey, $apiSecret, $currency, $isEnabled]);
            setFlash('success', "New provider '{$name}' registered.");
        }
        header('Location: /admin/providers');
        exit;
    }
}

// Fetch all providers
$providers = $db->query("SELECT * FROM providers ORDER BY id ASC")->fetchAll();

$activeNav = 'providers';
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
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Free Fire API Providers</h1>
                    <p class="text-xs text-slate-400 mt-1">Configure automated diamond top-up suppliers, API endpoints, and credentials.</p>
                </div>
                <button type="button" onclick="openProviderModal()" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Add New Provider</span>
                </button>
            </div>

            <!-- Notice Box -->
            <div class="bg-[#111728] border border-slate-700/80 rounded-2xl p-4 text-xs text-slate-300 flex items-start gap-3">
                <span class="text-lg">🛡️</span>
                <div>
                    <span class="font-bold text-white block mb-0.5">Real Provider Integration Architecture:</span>
                    <span>When a provider is enabled with real credentials, orders are dispatched over HTTP with server-to-server signatures. When credentials are not configured, orders remain safely queued with 'Pending' status without fake auto-completion.</span>
                </div>
            </div>

            <!-- Providers Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php foreach ($providers as $prov): ?>
                    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-800/80">
                            <div>
                                <h3 class="text-sm font-bold text-white"><?= htmlspecialchars($prov['name']) ?></h3>
                                <code class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($prov['code']) ?></code>
                            </div>

                            <!-- Toggle Status Form -->
                            <form action="/admin/providers" method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="toggle_provider">
                                <input type="hidden" name="id" value="<?= $prov['id'] ?>">
                                <input type="hidden" name="is_enabled" value="<?= $prov['is_enabled'] ? 0 : 1 ?>">
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all <?= $prov['is_enabled'] ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' ?>">
                                    <span class="w-2 h-2 rounded-full <?= $prov['is_enabled'] ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' ?>"></span>
                                    <span><?= $prov['is_enabled'] ? 'Active' : 'Disabled' ?></span>
                                </button>
                            </form>
                        </div>

                        <div class="space-y-2 text-xs text-slate-300">
                            <div class="flex justify-between py-1 border-b border-slate-800/40">
                                <span class="text-slate-500">API Endpoint:</span>
                                <span class="font-mono text-slate-300 max-w-[200px] truncate" title="<?= htmlspecialchars($prov['api_url'] ?? '') ?>"><?= htmlspecialchars($prov['api_url'] ?: 'Not Configured') ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-800/40">
                                <span class="text-slate-500">API Key:</span>
                                <span class="font-mono text-slate-400"><?= !empty($prov['api_key']) ? (substr($prov['api_key'], 0, 4) . '••••••••' . substr($prov['api_key'], -4)) : '<span class="text-amber-400">Missing Key</span>' ?></span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-slate-800/40">
                                <span class="text-slate-500">Currency:</span>
                                <span><?= htmlspecialchars($prov['currency'] ?? 'INR') ?></span>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-slate-800/80 flex items-center justify-end">
                            <button type="button" onclick='editProvider(<?= json_encode($prov) ?>)' class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-400 hover:text-rose-300 bg-[#13192A] hover:bg-slate-800 px-3.5 py-1.5 rounded-xl border border-slate-700/80 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <span>Edit Credentials</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

    </div>
</div>

<!-- Modal: Provider Edit / Create -->
<div id="providerModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl max-w-lg w-full p-6 sm:p-8 space-y-5 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white" id="pModalTitle">Configure API Provider</h3>
            <button type="button" onclick="closeProviderModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/providers" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_provider">
            <input type="hidden" name="id" id="prov_id" value="0">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Provider Name *</label>
                    <input type="text" name="name" id="prov_name" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Identifier Code *</label>
                    <input type="text" name="code" id="prov_code" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">API Endpoint URL</label>
                <input type="url" name="api_url" id="prov_url" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono" placeholder="https://api.provider.com/v1">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">API Key / Token</label>
                    <input type="text" name="api_key" id="prov_key" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">API Secret / Sign Key</label>
                    <input type="password" name="api_secret" id="prov_secret" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 font-mono">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Currency</label>
                <input type="text" name="currency" id="prov_curr" value="INR" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white font-mono">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_enabled" id="prov_enabled" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                <label for="prov_enabled" class="text-xs font-semibold text-slate-300">Enable this provider for automated order dispatch</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeProviderModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#FF2E51] hover:bg-rose-600 shadow-md shadow-rose-600/30">Save Provider</button>
            </div>
        </form>
    </div>
</div>

<script>
function openProviderModal() {
    document.getElementById('pModalTitle').innerText = 'Add New Provider';
    document.getElementById('prov_id').value = '0';
    document.getElementById('prov_name').value = '';
    document.getElementById('prov_code').value = '';
    document.getElementById('prov_url').value = '';
    document.getElementById('prov_key').value = '';
    document.getElementById('prov_secret').value = '';
    document.getElementById('prov_curr').value = 'INR';
    document.getElementById('prov_enabled').checked = true;
    document.getElementById('providerModal').classList.remove('hidden');
}

function editProvider(p) {
    document.getElementById('pModalTitle').innerText = 'Configure ' + p.name;
    document.getElementById('prov_id').value = p.id;
    document.getElementById('prov_name').value = p.name;
    document.getElementById('prov_code').value = p.code;
    document.getElementById('prov_url').value = p.api_url || '';
    document.getElementById('prov_key').value = p.api_key || '';
    document.getElementById('prov_secret').value = p.api_secret || '';
    document.getElementById('prov_curr').value = p.currency || 'INR';
    document.getElementById('prov_enabled').checked = p.is_enabled == 1;
    document.getElementById('providerModal').classList.remove('hidden');
}

function closeProviderModal() {
    document.getElementById('providerModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
