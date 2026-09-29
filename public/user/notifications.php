<?php
/**
 * FF Panel V2 - Customer Notification Center
 */
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

// Handle Mark All as Read (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrfToken();

    if ($_POST['action'] === 'mark_all_read') {
        $upd = $db->prepare("UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR (user_id IS NULL AND role_target = 'user')) AND is_read = 0");
        $upd->execute([$currentUser['id']]);
        setFlash('success', 'All notifications marked as read.');
    } elseif ($_POST['action'] === 'mark_single_read') {
        $notifId = (int)($_POST['notification_id'] ?? 0);
        $upd = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR user_id IS NULL)");
        $upd->execute([$notifId, $currentUser['id']]);
    }
    header('Location: /notifications');
    exit;
}

// Fetch notifications
$stmt = $db->prepare("SELECT * FROM notifications WHERE (user_id = ? OR (user_id IS NULL AND role_target = 'user')) ORDER BY id DESC LIMIT 50");
$stmt->execute([$currentUser['id']]);
$notifications = $stmt->fetchAll();

$unreadCount = getUnreadNotificationCount($currentUser['id'], 'user');

$activeNav = 'notifications';
$activeSidebar = 'notifications';
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

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2.5">
                        <span>Notifications</span>
                        <?php if ($unreadCount > 0): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#FF2E51] text-white">
                                <?= $unreadCount ?> new
                            </span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-xs text-slate-400 mt-1">Real-time alerts for your orders, deposits, wallet balance, and bonuses.</p>
                </div>

                <?php if ($unreadCount > 0): ?>
                    <form action="/notifications" method="POST">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-400 hover:text-rose-300 bg-[#13192A] hover:bg-slate-800 border border-slate-700/80 px-4 py-2 rounded-xl transition-colors">
                            <span>Mark all as read</span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Notifications List -->
            <div class="space-y-3">
                <?php if (empty($notifications)): ?>
                    <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-12 text-center space-y-3 shadow-xl">
                        <div class="w-12 h-12 mx-auto rounded-2xl bg-slate-800 flex items-center justify-center text-slate-500 text-xl">
                            🔔
                        </div>
                        <h3 class="text-sm font-bold text-white">No Notifications Yet</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">When your orders are processed or deposits approved, updates will appear right here.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <div class="bg-[#0D121F] border <?= empty($n['is_read']) ? 'border-rose-500/40 bg-[#101628]' : 'border-slate-800/80' ?> rounded-2xl p-4 sm:p-5 shadow-lg flex items-start justify-between gap-4 transition-all">
                            
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 shrink-0 rounded-xl flex items-center justify-center text-sm font-bold <?= $n['type'] === 'success' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($n['type'] === 'warning' ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : ($n['type'] === 'error' ? 'bg-rose-500/15 text-rose-400 border border-rose-500/30' : 'bg-cyan-500/15 text-cyan-400 border border-cyan-500/30')) ?>">
                                    <?= $n['type'] === 'success' ? '✓' : ($n['type'] === 'warning' ? '⏳' : ($n['type'] === 'error' ? '!' : 'ℹ')) ?>
                                </div>

                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-xs sm:text-sm font-bold text-white"><?= htmlspecialchars($n['title']) ?></h4>
                                        <?php if (empty($n['is_read'])): ?>
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="text-xs text-slate-300 leading-relaxed"><?= htmlspecialchars($n['message']) ?></p>
                                    <div class="flex items-center gap-4 pt-1 text-[11px] text-slate-500">
                                        <span><?= date('d M Y, h:i A', strtotime($n['created_at'])) ?></span>
                                        <?php if (!empty($n['link'])): ?>
                                            <a href="<?= htmlspecialchars($n['link']) ?>" class="text-rose-400 hover:text-rose-300 font-semibold underline">
                                                View Details &rarr;
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if (empty($n['is_read'])): ?>
                                <form action="/notifications" method="POST" class="shrink-0">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="mark_single_read">
                                    <input type="hidden" name="notification_id" value="<?= $n['id'] ?>">
                                    <button type="submit" title="Mark as read" class="text-slate-400 hover:text-white p-1 rounded hover:bg-slate-800 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </form>
                            <?php endif; ?>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
