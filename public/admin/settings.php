<?php
/**
 * FF Panel V2 - Admin Global Configuration Settings
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'settings';
$pageTitle = 'Website Configuration Settings';

// Handle Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $settingsToUpdate = [
        'site_name' => trim($_POST['site_name'] ?? 'FF Panel Store'),
        'site_tagline' => trim($_POST['site_tagline'] ?? 'Fast • Safe • Reliable'),
        'currency_symbol' => trim($_POST['currency_symbol'] ?? '₹'),
        'announcement' => trim($_POST['announcement'] ?? ''),
        'support_email' => trim($_POST['support_email'] ?? 'support@ffpanel.com'),
        'support_whatsapp' => trim($_POST['support_whatsapp'] ?? '+91 98765 43210'),
        'min_recharge_amount' => trim($_POST['min_recharge_amount'] ?? '50'),
        'auto_order_enabled' => !empty($_POST['auto_order_enabled']) ? '1' : '0',
        'default_provider' => trim($_POST['default_provider'] ?? 'garena_direct'),
        'referral_enabled' => !empty($_POST['referral_enabled']) ? '1' : '0',
        'referral_commission_percent' => trim($_POST['referral_commission_percent'] ?? '5.00'),
    ];

    $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    foreach ($settingsToUpdate as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    setFlash('success', 'V2 configuration settings saved successfully to MySQL database.');
    header('Location: /admin/settings');
    exit;
}

$settings = getSiteSettings();
$providers = $db->query("SELECT code, name, is_enabled FROM providers ORDER BY name ASC")->fetchAll();

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
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Website Configuration V2</h1>
                <p class="text-xs text-slate-400 mt-1">Manage global store parameters, referral rules, automated order settings, and customer support.</p>
            </div>

            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-6 sm:p-8 shadow-xl max-w-3xl">
                <form action="/admin/settings" method="POST" class="space-y-6">
                    <?= csrfField() ?>

                    <!-- Section: Branding -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>🏷️</span>
                            <span>Store Branding & Public Presentation</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Store Brand Name</label>
                                <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tagline / Subtext</label>
                                <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? 'Fast • Safe • Reliable') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Currency Symbol</label>
                            <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>" required class="w-28 bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-rose-500 font-bold">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Global Header Announcement Banner</label>
                            <textarea name="announcement" rows="2" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white focus:outline-none focus:border-rose-500"><?= htmlspecialchars($settings['announcement'] ?? '') ?></textarea>
                            <span class="text-[10px] text-slate-500 mt-1 block">Displayed on top of the public store and user dashboard.</span>
                        </div>
                    </div>

                    <!-- Section: Order & Provider Automation -->
                    <div class="space-y-4 pt-2">
                        <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>⚡</span>
                            <span>Order Fulfillment & Provider Automation</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Default API Provider</label>
                                <select name="default_provider" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                                    <?php foreach ($providers as $p): ?>
                                        <option value="<?= htmlspecialchars($p['code']) ?>" <?= ($settings['default_provider'] ?? '') === $p['code'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p['name']) ?> (<?= $p['is_enabled'] ? 'Active' : 'Disabled' ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Min Recharge / Top-up Amount (₹)</label>
                                <input type="number" name="min_recharge_amount" value="<?= htmlspecialchars($settings['min_recharge_amount'] ?? '50') ?>" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white">
                            </div>
                        </div>

                        <div class="bg-[#111728] p-4 rounded-xl border border-slate-800 flex items-center gap-3">
                            <input type="checkbox" name="auto_order_enabled" id="auto_order" value="1" <?= (!empty($settings['auto_order_enabled']) && $settings['auto_order_enabled'] === '1') ? 'checked' : '' ?> class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                            <div>
                                <label for="auto_order" class="text-xs font-bold text-white cursor-pointer">Enable Automated External Provider Dispatch</label>
                                <p class="text-[11px] text-slate-400">When enabled, placed orders mapped to an active provider will attempt real HTTP API fulfillment.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Referral Program -->
                    <div class="space-y-4 pt-2">
                        <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>🎁</span>
                            <span>Referral Program & Affiliate Commissions</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Commission Rate (% per Order)</label>
                                <input type="number" step="0.5" name="referral_commission_percent" value="<?= htmlspecialchars($settings['referral_commission_percent'] ?? '5.00') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white">
                            </div>
                            <div class="flex items-center pt-6">
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="referral_enabled" value="1" <?= (!empty($settings['referral_enabled']) && $settings['referral_enabled'] === '1') ? 'checked' : '' ?> class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                                    <span class="text-xs font-semibold text-white">Enable Referral Program</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Support Channels -->
                    <div class="space-y-4 pt-2">
                        <h3 class="text-sm font-bold text-white border-b border-slate-800 pb-2 flex items-center gap-2">
                            <span>📞</span>
                            <span>Customer Support Channels</span>
                        </h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support Email</label>
                                <input type="email" name="support_email" value="<?= htmlspecialchars($settings['support_email'] ?? 'support@ffpanel.com') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support WhatsApp Number</label>
                                <input type="text" name="support_whatsapp" value="<?= htmlspecialchars($settings['support_whatsapp'] ?? '+91 98765 43210') ?>" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                            <span>Save All Settings</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>

                </form>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
