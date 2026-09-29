<?php
/**
 * FF Panel V2 - Admin Notification Manager
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

// Handle Actions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();
    $action = $_POST['action'] ?? '';

    if ($action === 'send_notification') {
        $targetUserId = !empty($_POST['target_user_id']) ? (int)$_POST['target_user_id'] : null;
        $roleTarget = trim($_POST['role_target'] ?? 'user');
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = trim($_POST['type'] ?? 'info');
        $link = trim($_POST['link'] ?? '');

        if (empty($title) || empty($message)) {
            setFlash('error', 'Title and Message are required.');
            header('Location: /admin/notifications');
            exit;
        }

        createNotification($targetUserId, $title, $message, $type, $link ?: null, $roleTarget, $db);
        setFlash('success', 'Notification dispatched successfully.');
        header('Location: /admin/notifications');
        exit;
    }

    if ($action === 'mark_all_read') {
        $db->query("UPDATE notifications SET is_read = 1 WHERE role_target = 'admin' AND is_read = 0");
        setFlash('success', 'Admin notifications marked as read.');
        header('Location: /admin/notifications');
        exit;
    }
}

// Fetch Admin Notifications
$notifications = $db->query("SELECT * FROM notifications WHERE role_target = 'admin' ORDER BY id DESC LIMIT 50")->fetchAll();
$unreadCount = getUnreadNotificationCount(null, 'admin');

// Fetch Users for selector
$users = $db->query("SELECT id, name, email FROM users WHERE role = 'user' ORDER BY name ASC LIMIT 100")->fetchAll();

$activeNav = 'notifications';
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
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                        <span>Admin Alerts & Dispatch</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF2E51] text-white">
                                <?= $unreadCount ?> new
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">Review operational alerts for deposits and orders, or send notifications to users.</p>
                </div>

                <div class="flex items-center gap-3">
                    <?php if ($unreadCount > 0): ?>
                        <form action="/admin/notifications" method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold text-slate-300 rounded-xl transition-colors">
                                Mark all as read
                            </button>
                        </form>
                    <?php endif; ?>
                    <button type="button" onclick="openSendModal()" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        <span>Send Notification</span>
                    </button>
                </div>
            </div>

            <!-- Notifications List -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-3">
                <?php if (empty($notifications)): ?>
                    <p class="text-xs text-slate-500 text-center py-10">No admin alerts recorded.</p>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="bg-[#111728] border <?= empty($n['is_read']) ? 'border-rose-500/40 bg-[#141B30]' : 'border-slate-800' ?> rounded-2xl p-4 flex items-start gap-4">
                            <div class="w-8 h-8 shrink-0 rounded-xl flex items-center justify-center text-xs font-bold <?= $n['type'] === 'warning' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : ($n['type'] === 'success' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/30') ?>">
                                <?= $n['type'] === 'warning' ? '⚡' : ($n['type'] === 'success' ? '✓' : 'ℹ') ?>
                            </div>
                            <div class="space-y-1 flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs sm:text-sm font-bold text-white"><?= htmlspecialchars($n['title']) ?></h4>
                                    <span class="text-[10px] text-slate-500"><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                                </div>
                                <p class="text-xs text-slate-300 leading-relaxed"><?= htmlspecialchars($n['message']) ?></p>
                                <?php if (!empty($n['link'])): ?>
                                    <a href="<?= htmlspecialchars($n['link']) ?>" class="inline-block pt-1 text-[11px] text-rose-400 hover:text-rose-300 font-semibold underline">Take Action &rarr;</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<!-- Modal: Send Notification -->
<div id="sendModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl max-w-md w-full p-6 sm:p-8 space-y-5 shadow-2xl">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white">Send Notification</h3>
            <button type="button" onclick="closeSendModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/notifications" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="send_notification">

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Target Recipient</label>
                <select name="target_user_id" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                    <option value="">All Registered Users (Broadcast)</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['email']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Notification Title *</label>
                <input type="text" name="title" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500" placeholder="e.g. Special Weekend Diamonds Offer!">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Message Content *</label>
                <textarea name="message" rows="3" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Type / Style</label>
                    <select name="type" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="info">Information (Blue)</option>
                        <option value="success">Success / Reward (Green)</option>
                        <option value="warning">Warning / Alert (Yellow)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Action Link (Optional)</label>
                    <input type="text" name="link" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500 font-mono text-[11px]" placeholder="/dashboard">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeSendModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#FF2E51] hover:bg-rose-600 shadow-md shadow-rose-600/30">Send Now</button>
            </div>
        </form>
    </div>
</div>

<script>
function openSendModal() {
    document.getElementById('sendModal').classList.remove('hidden');
}
function closeSendModal() {
    document.getElementById('sendModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
