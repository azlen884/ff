<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

$activeNav = 'services';
$activeSidebar = 'services';
$pageTitle = 'Free Fire Services Catalog';

// Filter params
$selectedCategory = trim($_GET['category'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');

$sql = "SELECT s.*, c.name as category_name, c.slug as category_slug 
        FROM services s 
        JOIN categories c ON s.category_id = c.id 
        WHERE s.is_active = 1";
$params = [];

if (!empty($selectedCategory)) {
    $sql .= " AND c.slug = ?";
    $params[] = $selectedCategory;
}

if (!empty($searchQuery)) {
    $sql .= " AND (s.title LIKE ? OR s.subtitle LIKE ? OR s.amount_description LIKE ?)";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
    $params[] = "%{$searchQuery}%";
}

$sql .= " ORDER BY s.is_popular DESC, s.display_order ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// All categories for pills
$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

// Saved UIDs for modal
$savedUids = $db->prepare("SELECT * FROM saved_uids WHERE user_id = ? ORDER BY is_default DESC");
$savedUids->execute([$currentUser['id']]);
$userSavedUids = $savedUids->fetchAll();
$defaultUid = !empty($userSavedUids) ? $userSavedUids[0]['uid_number'] : '';

require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <!-- Top Catalog Header Banner -->
    <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-6 sm:p-8 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 text-rose-500 text-xs font-bold uppercase tracking-wider mb-2">
                <span>⚡ Official Free Fire Catalog</span>
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Diamonds, Memberships & Passes</h2>
            <p class="text-xs text-slate-400 mt-1 max-w-xl">
                Choose any item below for direct in-game delivery using your numeric Free Fire Player UID.
            </p>
        </div>

        <!-- Search Bar -->
        <div class="w-full md:w-80">
            <form action="/user/services.php" method="GET" class="relative">
                <?php if ($selectedCategory): ?>
                    <input type="hidden" name="category" value="<?= htmlspecialchars($selectedCategory) ?>">
                <?php endif; ?>
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery) ?>" placeholder="Search packages..." class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Filter Tabs -->
    <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none">
        <a href="/user/services.php" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= empty($selectedCategory) ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
            All Services (<?= count($services) ?>)
        </a>
        <?php foreach ($categories as $cat): ?>
            <a href="/user/services.php?category=<?= urlencode($cat['slug']) ?>" class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all <?= $selectedCategory === $cat['slug'] ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'bg-[#0D121F] border border-slate-800 text-slate-400 hover:text-white' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Services Grid -->
    <?php if (empty($services)): ?>
        <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-12 text-center text-slate-400">
            <div class="w-12 h-12 mx-auto rounded-full bg-slate-800/80 flex items-center justify-center text-slate-500 mb-3 text-xl">🔍</div>
            <h4 class="text-base font-bold text-white mb-1">No services found</h4>
            <p class="text-xs">Try selecting another category or clear your search keyword.</p>
            <a href="/user/services.php" class="inline-block mt-4 text-xs font-bold text-rose-400 hover:text-rose-300">View All Services →</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($services as $service): ?>
                <div class="group bg-[#0D121F] border border-slate-800/90 hover:border-rose-500/50 rounded-2xl p-4 transition-all duration-200 hover:shadow-xl hover:shadow-rose-600/10 flex flex-col justify-between">
                    <div>
                        <!-- Header Graphics -->
                        <div class="w-full h-28 rounded-xl bg-gradient-to-b from-[#141A2D] to-[#0A0D16] border border-slate-800 flex items-center justify-center relative overflow-hidden mb-3">
                            <span class="absolute top-2 right-2 text-[9px] font-bold tracking-wider uppercase px-1.5 py-0.5 rounded bg-black/60 text-slate-400 border border-slate-700/60">
                                FREE FIRE
                            </span>
                            <div class="text-cyan-400 flex items-center gap-1">
                                <svg class="w-10 h-10 drop-shadow-[0_0_10px_rgba(34,211,238,0.4)]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7zm0 3.2L18.4 9 12 18.5 5.6 9 12 5.2z"/></svg>
                            </div>
                        </div>

                        <!-- Title & Description -->
                        <h4 class="text-sm font-bold text-white group-hover:text-rose-400 transition-colors leading-snug">
                            <?= htmlspecialchars($service['title']) ?>
                        </h4>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            <?= htmlspecialchars($service['amount_description']) ?>
                        </p>

                        <!-- Badges -->
                        <div class="flex items-center gap-1.5 mt-2.5">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                <?= htmlspecialchars($service['badge1'] ?? 'Instant') ?>
                            </span>
                            <?php if (!empty($service['badge2'])): ?>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                    <?= htmlspecialchars($service['badge2']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Price & Buy Button -->
                    <div class="mt-4 pt-3 border-t border-slate-800">
                        <div class="flex items-baseline justify-between mb-2">
                            <div class="text-base font-black text-white"><?= formatCurrency((float)$service['price']) ?></div>
                            <?php if ($service['original_price']): ?>
                                <div class="text-[10px] text-slate-400 line-through"><?= formatCurrency((float)$service['original_price']) ?></div>
                            <?php endif; ?>
                        </div>
                        <button type="button" onclick="openOrderModal(<?= $service['id'] ?>, '<?= addslashes(htmlspecialchars($service['title'])) ?>', '<?= formatCurrency((float)$service['price']) ?>')" class="w-full inline-flex items-center justify-center gap-1.5 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2 px-3 rounded-xl shadow-md shadow-rose-600/25 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <span>Add to Cart</span>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</main>

<!-- Modal -->
<div id="orderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-[#0F1422] border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>⚡ Instant Order Confirmation</span>
            </h3>
            <button type="button" onclick="closeOrderModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/user/order.php" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="place_order">
            <input type="hidden" name="service_id" id="modal_service_id" value="">

            <div class="bg-[#141A2D] border border-slate-800 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-white" id="modal_service_title">Item Title</div>
                    <div class="text-[10px] text-slate-400">Direct In-Game Delivery</div>
                </div>
                <div class="text-sm font-black text-rose-400" id="modal_service_price">₹ 0.00</div>
            </div>

            <div>
                <label for="modal_player_uid" class="block text-xs font-semibold text-slate-300 mb-1.5">Free Fire Player UID *</label>
                <input type="text" id="modal_player_uid" name="player_uid" value="<?= htmlspecialchars($defaultUid) ?>" required placeholder="e.g. 2849182391" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-300">
                <input type="checkbox" id="save_uid_toggle" name="save_uid" value="1" checked class="rounded border-slate-700 text-rose-600 focus:ring-0">
                <label for="save_uid_toggle" class="cursor-pointer">Save this UID to my profile</label>
            </div>

            <div class="bg-[#141A2D] rounded-xl p-3 text-xs space-y-1.5">
                <div class="flex items-center justify-between text-slate-300">
                    <span>Payment Method:</span>
                    <strong class="text-white">Wallet Balance</strong>
                </div>
                <div class="flex items-center justify-between text-slate-300">
                    <span>Your Current Balance:</span>
                    <strong class="text-emerald-400"><?= formatCurrency((float)$currentUser['wallet_balance']) ?></strong>
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeOrderModal()" class="flex-1 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs py-2.5 rounded-xl transition-colors">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    Confirm & Pay
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openOrderModal(serviceId, title, priceFormatted) {
    document.getElementById('modal_service_id').value = serviceId;
    document.getElementById('modal_service_title').innerText = title;
    document.getElementById('modal_service_price').innerText = priceFormatted;
    document.getElementById('orderModal').classList.remove('hidden');
}
function closeOrderModal() {
    document.getElementById('orderModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
