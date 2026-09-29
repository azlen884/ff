<?php
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
        'support_whatsapp' => trim($_POST['support_whatsapp'] ?? '+91 98765 43210')
    ];

    $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
    foreach ($settingsToUpdate as $k => $v) {
        $stmt->execute([$k, $v]);
    }

    setFlash('success', 'Website configuration settings saved successfully to MySQL database.');
    header('Location: /admin/settings.php');
    exit;
}

$settings = getSiteSettings();

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Basic Website Settings</h2>
            <p class="text-xs text-slate-400 mt-1">Configure portal branding, customer support channels, currency and announcement banners.</p>
        </div>
    </div>

    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-6 sm:p-8 shadow-xl max-w-2xl">
        <form action="/admin/settings.php" method="POST" class="space-y-5">
            <?= csrfField() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Store Brand Name</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?>" required class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Tagline / Subtext</label>
                    <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? 'Fast • Safe • Reliable') ?>" required class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Currency Symbol</label>
                <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>" required class="w-28 bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500 font-bold">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1.5">Global Header Announcement Banner</label>
                <textarea name="announcement" rows="2" class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500"><?= htmlspecialchars($settings['announcement'] ?? '') ?></textarea>
                <span class="text-[10px] text-slate-500 mt-1 block">Displayed on top of the public landing page.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support Email Address</label>
                    <input type="email" name="support_email" value="<?= htmlspecialchars($settings['support_email'] ?? 'support@ffpanel.com') ?>" required class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Support WhatsApp Number</label>
                    <input type="text" name="support_whatsapp" value="<?= htmlspecialchars($settings['support_whatsapp'] ?? '+91 98765 43210') ?>" required class="w-full bg-[#161D32] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <div class="pt-3">
                <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    Save Website Settings
                </button>
            </div>
        </form>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
