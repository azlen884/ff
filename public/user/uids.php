<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

$activeNav = 'uids';
$activeSidebar = 'uids';
$pageTitle = 'Free Fire UID Management';

// Handle Add UID
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrfToken();
    $action = $_POST['action'];

    if ($action === 'add_uid') {
        $uidNumber = trim($_POST['uid_number'] ?? '');
        $playerName = trim($_POST['player_name'] ?? '');
        $region = trim($_POST['region'] ?? 'India Server');
        $isDefault = !empty($_POST['is_default']) ? 1 : 0;

        if (empty($uidNumber) || strlen($uidNumber) < 6) {
            setFlash('error', 'Free Fire UID must be a valid numeric ID at least 6 digits long.');
        } else {
            // Check if already exists for this user
            $check = $db->prepare("SELECT id FROM saved_uids WHERE user_id = ? AND uid_number = ?");
            $check->execute([$currentUser['id'], $uidNumber]);
            if ($check->fetch()) {
                setFlash('error', 'This UID is already saved in your account.');
            } else {
                if ($isDefault) {
                    $reset = $db->prepare("UPDATE saved_uids SET is_default = 0 WHERE user_id = ?");
                    $reset->execute([$currentUser['id']]);
                }
                $insert = $db->prepare("INSERT INTO saved_uids (user_id, uid_number, player_name, region, is_default) VALUES (?, ?, ?, ?, ?)");
                $insert->execute([$currentUser['id'], $uidNumber, $playerName, $region, $isDefault]);
                setFlash('success', "UID {$uidNumber} added successfully to your account!");
            }
        }
        header('Location: /uids');
        exit;
    }

    if ($action === 'set_default') {
        $uidId = (int)($_POST['uid_id'] ?? 0);
        $reset = $db->prepare("UPDATE saved_uids SET is_default = 0 WHERE user_id = ?");
        $reset->execute([$currentUser['id']]);

        $setDefault = $db->prepare("UPDATE saved_uids SET is_default = 1 WHERE id = ? AND user_id = ?");
        $setDefault->execute([$uidId, $currentUser['id']]);

        setFlash('success', 'Default Free Fire UID updated.');
        header('Location: /uids');
        exit;
    }

    if ($action === 'delete_uid') {
        $uidId = (int)($_POST['uid_id'] ?? 0);
        $del = $db->prepare("DELETE FROM saved_uids WHERE id = ? AND user_id = ?");
        $del->execute([$uidId, $currentUser['id']]);

        setFlash('info', 'Free Fire UID removed from saved list.');
        header('Location: /uids');
        exit;
    }
}

// Fetch all saved UIDs
$stmt = $db->prepare("SELECT * FROM saved_uids WHERE user_id = ? ORDER BY is_default DESC, created_at DESC");
$stmt->execute([$currentUser['id']]);
$uids = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Header -->
    <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div>
            <h2 class="text-2xl font-extrabold text-white">Free Fire Player UIDs</h2>
            <p class="text-xs text-slate-400 mt-1">
                Manage your saved Free Fire game IDs for quick 1-click checkout and auto-recharge.
            </p>
        </div>
        <button type="button" onclick="document.getElementById('addUidCard').scrollIntoView({behavior: 'smooth'})" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
            <span>+ Add New UID</span>
        </button>
    </div>

    <!-- Grid of Saved UIDs -->
    <?php if (empty($uids)): ?>
        <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-10 text-center text-slate-400">
            <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500 mb-3 text-xl">🎮</div>
            <h4 class="text-base font-bold text-white mb-1">No Saved Free Fire UIDs</h4>
            <p class="text-xs">Add your first Free Fire UID below to enable instant top-up with zero typing.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <?php foreach ($uids as $item): ?>
                <div class="bg-[#0D121F] border <?= $item['is_default'] ? 'border-rose-500/60 shadow-lg shadow-rose-600/10' : 'border-slate-800/80' ?> rounded-2xl p-5 flex flex-col justify-between space-y-4">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="text-xs font-mono font-black text-white text-base tracking-wider">
                                <?= htmlspecialchars($item['uid_number']) ?>
                            </span>
                            <?php if ($item['is_default']): ?>
                                <span class="bg-rose-500/15 text-rose-400 border border-rose-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">
                                    Primary UID
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs font-semibold text-slate-300">
                            <?= htmlspecialchars($item['player_name'] ?: 'Free Fire Player') ?>
                        </div>
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                            <span>Region:</span>
                            <span class="text-slate-200"><?= htmlspecialchars($item['region'] ?? 'India Server') ?></span>
                        </div>
                        <div class="text-[10px] text-slate-500 mt-2">
                            Added: <?= date('d M Y', strtotime($item['created_at'])) ?>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between gap-2">
                        <?php if (!$item['is_default']): ?>
                            <form action="/uids" method="POST" class="inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="action" value="set_default">
                                <input type="hidden" name="uid_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="text-[11px] font-semibold text-slate-300 hover:text-white bg-slate-800/80 hover:bg-slate-700 px-3 py-1.5 rounded-lg border border-slate-700 transition-colors">
                                    Set as Primary
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                                <span>✓</span> Default
                            </span>
                        <?php endif; ?>

                        <form action="/uids" method="POST" onsubmit="return confirm('Are you sure you want to remove this UID?');" class="inline">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete_uid">
                            <input type="hidden" name="uid_id" value="<?= $item['id'] ?>">
                            <button type="submit" class="text-[11px] font-semibold text-rose-400 hover:text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 px-3 py-1.5 rounded-lg border border-rose-500/20 transition-colors">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Add New UID Form Card -->
    <div id="addUidCard" class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-8 shadow-xl max-w-xl">
        <h3 class="text-base font-bold text-white mb-1">Add Free Fire Player UID</h3>
        <p class="text-xs text-slate-400 mb-5">Save your game ID so you can top-up instantly anytime.</p>

        <form action="/uids" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="add_uid">

            <div>
                <label for="uid_number" class="block text-xs font-semibold text-slate-300 mb-1.5">Player Free Fire UID (Numeric) *</label>
                <input type="text" id="uid_number" name="uid_number" required placeholder="e.g. 2849182391" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                <span class="text-[10px] text-slate-400 block mt-1">Can be found by opening Free Fire &gt; Profile banner &gt; UID copy icon.</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="player_name" class="block text-xs font-semibold text-slate-300 mb-1.5">In-Game Name / Nickname</label>
                    <input type="text" id="player_name" name="player_name" placeholder="e.g. ShadowHunter_YT" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label for="region" class="block text-xs font-semibold text-slate-300 mb-1.5">Game Server Region</label>
                    <select id="region" name="region" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500">
                        <option value="India Server">India Server</option>
                        <option value="Bangladesh Server">Bangladesh Server</option>
                        <option value="Nepal Server">Nepal Server</option>
                        <option value="Global / Singapore">Global / Singapore</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1 text-xs text-slate-300">
                <input type="checkbox" id="is_default" name="is_default" value="1" class="rounded border-slate-700 text-rose-600 focus:ring-0">
                <label for="is_default" class="cursor-pointer">Set as primary default UID for fast 1-click recharges</label>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-6 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    <span>Save Free Fire UID</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        </form>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
