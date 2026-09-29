<?php
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'services';
$pageTitle = 'Manage Services & Products';

// Handle Add / Edit / Delete / Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrfToken();
    $action = $_POST['action'];

    if ($action === 'create_service') {
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? 'Free Fire Diamonds');
        $amountDesc = trim($_POST['amount_description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $badge1 = trim($_POST['badge1'] ?? 'Instant');
        $badge2 = trim($_POST['badge2'] ?? 'Popular');
        $delivery = trim($_POST['delivery_time'] ?? 'Instant (1-2 Mins)');
        $isPopular = !empty($_POST['is_popular']) ? 1 : 0;

        if (!empty($title) && $price > 0) {
            $stmt = $db->prepare("INSERT INTO services (category_id, title, subtitle, amount_description, price, original_price, badge1, badge2, delivery_time, is_active, is_popular) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
            $stmt->execute([$categoryId, $title, $subtitle, $amountDesc, $price, $origPrice, $badge1, $badge2, $delivery, $isPopular]);
            setFlash('success', "Service '{$title}' added successfully.");
        } else {
            setFlash('error', 'Please provide a valid product title and price.');
        }
        header('Location: /admin/services.php');
        exit;
    }

    if ($action === 'update_service') {
        $id = (int)($_POST['service_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $badge2 = trim($_POST['badge2'] ?? '');

        if ($id > 0 && !empty($title) && $price > 0) {
            $stmt = $db->prepare("UPDATE services SET title = ?, price = ?, original_price = ?, badge2 = ? WHERE id = ?");
            $stmt->execute([$title, $price, $origPrice, $badge2, $id]);
            setFlash('success', "Service #{$id} updated successfully.");
        }
        header('Location: /admin/services.php');
        exit;
    }

    if ($action === 'toggle_active') {
        $id = (int)($_POST['service_id'] ?? 0);
        $stmt = $db->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('info', "Service status toggled.");
        header('Location: /admin/services.php');
        exit;
    }

    if ($action === 'delete_service') {
        $id = (int)($_POST['service_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('info', "Service #{$id} removed from catalog.");
        header('Location: /admin/services.php');
        exit;
    }
}

// Fetch all services
$services = $db->query("SELECT s.*, c.name as category_name 
                        FROM services s 
                        JOIN categories c ON s.category_id = c.id 
                        ORDER BY c.display_order ASC, s.display_order ASC")->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">
    
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xl">
        <div>
            <h2 class="text-xl sm:text-2xl font-black text-white">Manage Products & Services</h2>
            <p class="text-xs text-slate-400 mt-1">Configure Free Fire diamond packages, memberships, pricing, and active status.</p>
        </div>
        <button type="button" onclick="document.getElementById('newProductForm').classList.toggle('hidden')" class="inline-flex items-center gap-2 bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
            <span>+ Add New Product</span>
        </button>
    </div>

    <!-- Create Product Form (Collapsible) -->
    <div id="newProductForm" class="hidden bg-[#0F1422] border border-rose-500/30 rounded-2xl p-6 sm:p-7 shadow-xl space-y-4">
        <h3 class="text-sm font-bold text-white flex items-center gap-2">
            <span>Add New Free Fire Package</span>
        </h3>

        <form action="/admin/services.php" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="create_service">

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Category *</label>
                    <select name="category_id" required class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Package Title *</label>
                    <input type="text" name="title" required placeholder="e.g. 310 Diamonds" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Subtitle</label>
                    <input type="text" name="subtitle" value="Free Fire Diamonds" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Selling Price (₹) *</label>
                    <input type="number" step="0.01" name="price" required placeholder="50.00" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Original Price (₹)</label>
                    <input type="number" step="0.01" name="original_price" placeholder="65.00" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Badge 1</label>
                    <input type="text" name="badge1" value="Instant" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Badge 2 (Highlight)</label>
                    <input type="text" name="badge2" value="Popular" placeholder="Top Selling" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Amount Description</label>
                    <input type="text" name="amount_description" placeholder="e.g. 310 Diamonds Topup" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Delivery Estimate</label>
                    <input type="text" name="delivery_time" value="Instant (1-2 Mins)" class="w-full bg-[#161D32] border border-slate-700 rounded-xl px-3.5 py-2 text-xs text-white focus:outline-none focus:border-rose-500">
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1 text-xs text-slate-300">
                <input type="checkbox" id="is_popular" name="is_popular" value="1" checked class="rounded border-slate-700 text-rose-600 focus:ring-0">
                <label for="is_popular" class="cursor-pointer">Feature on Popular Services list</label>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs py-2.5 px-6 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    Create Product
                </button>
                <button type="button" onclick="document.getElementById('newProductForm').classList.add('hidden')" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs py-2.5 px-4 rounded-xl transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-[#0F1422] border border-slate-800/90 rounded-2xl shadow-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-[#141A2D] text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                    <tr>
                        <th class="py-3 px-4 font-bold">ID</th>
                        <th class="py-3 px-4 font-bold">Category</th>
                        <th class="py-3 px-4 font-bold">Product Title</th>
                        <th class="py-3 px-4 font-bold">Price</th>
                        <th class="py-3 px-4 font-bold">Original</th>
                        <th class="py-3 px-4 font-bold">Badges</th>
                        <th class="py-3 px-4 font-bold">Status</th>
                        <th class="py-3 px-4 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    <?php foreach ($services as $s): ?>
                        <tr class="hover:bg-[#141A2D]/50 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $s['id'] ?></td>
                            <td class="py-3 px-4">
                                <span class="text-xs font-semibold text-rose-400"><?= htmlspecialchars($s['category_name']) ?></span>
                            </td>
                            <td class="py-3 px-4 font-bold text-white">
                                <?= htmlspecialchars($s['title']) ?>
                                <span class="block text-[10px] font-normal text-slate-400"><?= htmlspecialchars($s['amount_description']) ?></span>
                            </td>
                            <td class="py-3 px-4 font-black text-white">
                                <?= formatCurrency((float)$s['price']) ?>
                            </td>
                            <td class="py-3 px-4 text-slate-400">
                                <?= $s['original_price'] ? formatCurrency((float)$s['original_price']) : '-' ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-1">
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                        <?= htmlspecialchars($s['badge1']) ?>
                                    </span>
                                    <?php if ($s['badge2']): ?>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                            <?= htmlspecialchars($s['badge2']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <form action="/admin/services.php" method="POST" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="toggle_active">
                                    <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="text-[10px] font-bold px-2 py-0.5 rounded-full border transition-colors <?= $s['is_active'] ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30' : 'bg-slate-800 text-slate-500 border-slate-700' ?>">
                                        <?= $s['is_active'] ? 'Active' : 'Disabled' ?>
                                    </button>
                                </form>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <form action="/admin/services.php" method="POST" onsubmit="return confirm('Delete this product permanently?');" class="inline">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="action" value="delete_service">
                                    <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs font-semibold p-1 hover:bg-rose-950/40 rounded transition-colors">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
