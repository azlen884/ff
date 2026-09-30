<?php
/**
 * FF Panel V2 - Admin Services & Product Management
 */
require_once __DIR__ . '/../../config/app.php';
$currentAdmin = requireAdmin();
$db = getDbConnection();

$activeNav = 'services';
$pageTitle = 'Manage Services & Products';

// Handle Add / Edit / Delete / Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verifyCsrfToken();
    $action = $_POST['action'];

    if ($action === 'create_service' || $action === 'update_service') {
        $id = (int)($_POST['service_id'] ?? 0);
        $categoryId = (int)($_POST['category_id'] ?? 1);
        $providerId = !empty($_POST['provider_id']) ? (int)$_POST['provider_id'] : null;
        $providerServiceId = trim($_POST['provider_service_id'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $subtitle = trim($_POST['subtitle'] ?? 'Free Fire Diamonds');
        $description = trim($_POST['description'] ?? '');
        $amountDesc = trim($_POST['amount_description'] ?? '');
        $providerCost = (float)($_POST['provider_cost'] ?? 0);
        $adminMargin = (float)($_POST['admin_margin'] ?? 0);
        $price = (float)($_POST['price'] ?? 0);
        $origPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $badge1 = trim($_POST['badge1'] ?? 'Instant');
        $badge2 = trim($_POST['badge2'] ?? '');
        $delivery = trim($_POST['delivery_time'] ?? 'Instant (1-5 Mins)');
        $minQty = (int)($_POST['min_qty'] ?? 1);
        $maxQty = (int)($_POST['max_qty'] ?? 1);
        $isPopular = !empty($_POST['is_popular']) ? 1 : 0;
        $isActive = !empty($_POST['is_active']) ? 1 : 0;
        $imageUrl = trim($_POST['existing_image_url'] ?? '');

        // Handle image upload if a file was provided
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['product_image'];
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (!in_array($mimeType, $allowedMimes) || !in_array($ext, $allowedExts)) {
                setFlash('error', 'Invalid image format. Allowed formats: PNG, JPG, JPEG, WEBP, GIF.');
                header('Location: /admin/services');
                exit;
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                setFlash('error', 'Image size exceeds maximum limit of 5MB.');
                header('Location: /admin/services');
                exit;
            }

            $uploadDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $imageUrl = '/uploads/products/' . $fileName;
            } else {
                setFlash('error', 'Failed to save uploaded image.');
                header('Location: /admin/services');
                exit;
            }
        } elseif (!empty($_POST['image_url'])) {
            $imageUrl = trim($_POST['image_url']);
        }

        if (empty($title) || $price <= 0) {
            setFlash('error', 'Please provide a valid package title and selling price.');
            header('Location: /admin/services');
            exit;
        }

        if ($action === 'update_service' && $id > 0) {
            $stmt = $db->prepare("UPDATE services SET category_id = ?, provider_id = ?, provider_service_id = ?, title = ?, subtitle = ?, description = ?, amount_description = ?, provider_cost = ?, admin_margin = ?, price = ?, original_price = ?, badge1 = ?, badge2 = ?, delivery_time = ?, min_qty = ?, max_qty = ?, is_popular = ?, is_active = ?, image_url = ? WHERE id = ?");
            $stmt->execute([$categoryId, $providerId, $providerServiceId, $title, $subtitle, $description, $amountDesc, $providerCost, $adminMargin, $price, $origPrice, $badge1, $badge2, $delivery, $minQty, $maxQty, $isPopular, $isActive, $imageUrl, $id]);
            setFlash('success', "Service '{$title}' updated successfully.");
        } else {
            $stmt = $db->prepare("INSERT INTO services (category_id, provider_id, provider_service_id, title, subtitle, description, amount_description, provider_cost, admin_margin, price, original_price, badge1, badge2, delivery_time, min_qty, max_qty, is_popular, is_active, image_url, display_order, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, 0, NOW())");
            $stmt->execute([$categoryId, $providerId, $providerServiceId, $title, $subtitle, $description, $amountDesc, $providerCost, $adminMargin, $price, $origPrice, $badge1, $badge2, $delivery, $minQty, $maxQty, $isPopular, $imageUrl]);
            setFlash('success', "Service '{$title}' created successfully.");
        }
        header('Location: /admin/services');
        exit;
    }

    if ($action === 'toggle_active') {
        $id = (int)($_POST['service_id'] ?? 0);
        $stmt = $db->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('info', "Service status toggled.");
        header('Location: /admin/services');
        exit;
    }

    if ($action === 'delete_service') {
        $id = (int)($_POST['service_id'] ?? 0);
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('info', "Service #{$id} removed from catalog.");
        header('Location: /admin/services');
        exit;
    }
}

// Fetch all services with category and provider details
$services = $db->query("SELECT s.*, c.name as category_name, p.name as provider_name 
                        FROM services s 
                        JOIN categories c ON s.category_id = c.id 
                        LEFT JOIN providers p ON s.provider_id = p.id 
                        ORDER BY c.display_order ASC, s.display_order ASC")->fetchAll();

$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();
$providers = $db->query("SELECT id, name, code, is_enabled FROM providers ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/../../includes/admin_header.php';
require_once __DIR__ . '/../../includes/admin_sidebar.php';
?>

<main class="flex-1 min-w-0 space-y-6">

    <!-- Flash Message -->
    <?php $flash = getFlash(); if ($flash): ?>
        <div role="alert" class="p-4 rounded-2xl flex items-center justify-between text-xs font-semibold shadow-lg transition-all <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-500/40' : ($flash['type'] === 'info' ? 'bg-cyan-950/80 text-cyan-300 border border-cyan-500/40' : 'bg-rose-950/80 text-rose-300 border border-rose-500/40') ?>">
            <div class="flex items-center gap-2.5">
                <?php if ($flash['type'] === 'success'): ?>
                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
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

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Services & Product Catalog</h1>
            <p class="text-xs text-slate-400 mt-1">Configure Free Fire diamond packages, provider cost, admin profit margin, and automated mappings.</p>
        </div>
                <button type="button" onclick="openServiceModal()" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>+ Add New Package</span>
                </button>
            </div>

            <!-- Services Table -->
            <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-5 sm:p-6 shadow-xl space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-800 text-slate-400 uppercase text-[10px] tracking-wider">
                                <th class="py-3 px-4">Package</th>
                                <th class="py-3 px-4">Category</th>
                                <th class="py-3 px-4">Provider Mapping</th>
                                <th class="py-3 px-4 text-right">Cost Price</th>
                                <th class="py-3 px-4 text-right">Margin</th>
                                <th class="py-3 px-4 text-right">Selling Price</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50">
                            <?php foreach ($services as $s): ?>
                                <tr class="hover:bg-slate-800/20 transition-colors">
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <div class="flex items-center gap-3">
                                            <?php if (!empty($s['image_url'])): ?>
                                                <img src="<?= htmlspecialchars($s['image_url']) ?>" alt="<?= htmlspecialchars($s['title']) ?>" class="w-9 h-9 rounded-lg object-cover border border-slate-700/80 shrink-0">
                                            <?php else: ?>
                                                <div class="w-9 h-9 rounded-lg bg-[#13192A] border border-slate-700/60 flex items-center justify-center text-rose-500 font-bold text-xs shrink-0">
                                                    <svg class="w-5 h-5 text-rose-400" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M12 2L2 9.5 12 22 22 9.5 12 2zM12 4.4L18.4 9.2 12 18.2 5.6 9.2 12 4.4z"/>
                                                    </svg>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-bold text-white"><?= htmlspecialchars($s['title']) ?></div>
                                                <div class="text-[11px] text-slate-400"><?= htmlspecialchars($s['amount_description']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-300 whitespace-nowrap">
                                        <span class="bg-[#13192A] px-2 py-0.5 rounded text-[11px] border border-slate-800"><?= htmlspecialchars($s['category_name']) ?></span>
                                    </td>
                                    <td class="py-3.5 px-4 whitespace-nowrap">
                                        <?php if (!empty($s['provider_name'])): ?>
                                            <div class="text-xs font-semibold text-rose-400"><?= htmlspecialchars($s['provider_name']) ?></div>
                                            <div class="text-[10px] text-slate-500 font-mono">PID: <?= htmlspecialchars($s['provider_service_id'] ?: 'Direct') ?></div>
                                        <?php else: ?>
                                            <span class="text-slate-500 italic text-[11px]">Manual / In-House</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-slate-400 whitespace-nowrap">
                                        <?= formatCurrency((float)$s['provider_cost']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right text-emerald-400 font-semibold whitespace-nowrap">
                                        +<?= formatCurrency((float)$s['admin_margin']) ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-right font-bold text-white whitespace-nowrap">
                                        <div class="text-sm"><?= formatCurrency((float)$s['price']) ?></div>
                                        <?php if ($s['original_price']): ?>
                                            <div class="text-[10px] text-slate-500 line-through"><?= formatCurrency((float)$s['original_price']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                        <form action="/admin/services" method="POST" class="inline">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="toggle_active">
                                            <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                            <button type="submit" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold <?= $s['is_active'] ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' ?>">
                                                <?= $s['is_active'] ? 'Active' : 'Disabled' ?>
                                            </button>
                                        </form>
                                    </td>
                                    <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" onclick='editService(<?= json_encode($s) ?>)' class="p-1 text-slate-400 hover:text-white rounded hover:bg-slate-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </button>
                                            <form action="/admin/services" method="POST" onsubmit="return confirm('Delete service <?= $s['title'] ?>?');" class="inline">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="action" value="delete_service">
                                                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="p-1 text-rose-400 hover:text-rose-300 rounded hover:bg-rose-950/40">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

</main>

<!-- Modal: Service Edit / Create -->
<div id="serviceModal" class="hidden fixed inset-0 z-50 bg-black/80 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-[#0D121F] border border-slate-800 rounded-3xl max-w-xl w-full p-6 sm:p-8 space-y-5 shadow-2xl overflow-y-auto max-h-[90vh]">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-base font-bold text-white" id="sModalTitle">Add Product Package</h3>
            <button type="button" onclick="closeServiceModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/admin/services" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="s_action" value="create_service">
            <input type="hidden" name="service_id" id="s_id" value="0">
            <input type="hidden" name="existing_image_url" id="s_existing_image" value="">

            <!-- Product Image Upload & Live Preview -->
            <div class="bg-[#13192A] border border-slate-700/80 rounded-2xl p-3.5 space-y-2">
                <label class="block text-xs font-semibold text-slate-300">Product / Package Image</label>
                <div class="flex items-center gap-3.5">
                    <div id="imagePreviewContainer" class="w-14 h-14 rounded-xl bg-[#0D121F] border border-slate-700/80 flex items-center justify-center overflow-hidden shrink-0 shadow-inner">
                        <img id="imagePreview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                        <span id="imagePreviewPlaceholder" class="text-[10px] text-slate-500 font-semibold">No Image</span>
                    </div>
                    <div class="flex-1 space-y-1">
                        <input type="file" name="product_image" id="s_image_file" accept="image/png,image/jpeg,image/webp,image/gif" onchange="previewSelectedImage(this)" class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-white hover:file:bg-slate-700 cursor-pointer">
                        <p class="text-[10px] text-slate-400">Supported: PNG, JPG, JPEG, WEBP (Max 5MB). Leave empty to retain current image.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Category *</label>
                    <select name="category_id" id="s_cat" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                        <?php foreach ($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Package Title *</label>
                    <input type="text" name="title" id="s_title" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white placeholder-slate-500" placeholder="e.g. 520 Diamonds">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">API Provider (Fulfillment)</label>
                    <select name="provider_id" id="s_prov" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                        <option value="">None (Manual Processing)</option>
                        <?php foreach ($providers as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= $p['is_enabled'] ? 'Active' : 'Disabled' ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Provider Service ID / Code</label>
                    <input type="text" name="provider_service_id" id="s_psid" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white font-mono" placeholder="e.g. 520_diamonds">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Provider Cost (₹)</label>
                    <input type="number" step="0.01" name="provider_cost" id="s_cost" value="0.00" oninput="calcPrice()" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Admin Margin (₹)</label>
                    <input type="number" step="0.01" name="admin_margin" id="s_margin" value="0.00" oninput="calcPrice()" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Selling Price (₹) *</label>
                    <input type="number" step="0.01" name="price" id="s_price" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Original Price (Strikeout)</label>
                    <input type="number" step="0.01" name="original_price" id="s_orig" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white" placeholder="Optional">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Amount Description</label>
                    <input type="text" name="amount_description" id="s_amt_desc" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white" placeholder="e.g. 520 Diamonds Top-Up">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Badge 1</label>
                    <input type="text" name="badge1" id="s_badge1" value="Instant" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Badge 2 (Highlight)</label>
                    <input type="text" name="badge2" id="s_badge2" value="Popular" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Min Qty</label>
                    <input type="number" name="min_qty" id="s_min_qty" value="1" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Max Qty</label>
                    <input type="number" name="max_qty" id="s_max_qty" value="1" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white">
                </div>
            </div>

            <div class="flex items-center gap-4">
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_popular" id="s_popular" value="1" class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                    <span class="text-xs font-semibold text-slate-300">Featured in Popular</span>
                </label>
                <label class="inline-flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="s_active" value="1" checked class="rounded bg-slate-800 border-slate-700 text-rose-600 focus:ring-rose-500">
                    <span class="text-xs font-semibold text-slate-300">Active in Store</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-800">
                <button type="button" onclick="closeServiceModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700">Cancel</button>
                <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#FF2E51] hover:bg-rose-600 shadow-md shadow-rose-600/30">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
function calcPrice() {
    const cost = parseFloat(document.getElementById('s_cost').value) || 0;
    const margin = parseFloat(document.getElementById('s_margin').value) || 0;
    if (cost > 0 || margin > 0) {
        document.getElementById('s_price').value = (cost + margin).toFixed(2);
    }
}

function previewSelectedImage(input) {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openServiceModal() {
    document.getElementById('sModalTitle').innerText = 'Add New Package';
    document.getElementById('s_action').value = 'create_service';
    document.getElementById('s_id').value = '0';
    document.getElementById('s_existing_image').value = '';
    document.getElementById('s_image_file').value = '';
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    preview.src = '';
    preview.classList.add('hidden');
    placeholder.classList.remove('hidden');

    document.getElementById('s_title').value = '';
    document.getElementById('s_prov').value = '';
    document.getElementById('s_psid').value = '';
    document.getElementById('s_cost').value = '0.00';
    document.getElementById('s_margin').value = '0.00';
    document.getElementById('s_price').value = '';
    document.getElementById('s_orig').value = '';
    document.getElementById('s_amt_desc').value = '';
    document.getElementById('s_badge1').value = 'Instant';
    document.getElementById('s_badge2').value = 'Popular';
    document.getElementById('s_min_qty').value = '1';
    document.getElementById('s_max_qty').value = '1';
    document.getElementById('s_popular').checked = true;
    document.getElementById('s_active').checked = true;
    document.getElementById('serviceModal').classList.remove('hidden');
}

function editService(s) {
    document.getElementById('sModalTitle').innerText = 'Edit ' + s.title;
    document.getElementById('s_action').value = 'update_service';
    document.getElementById('s_id').value = s.id;
    document.getElementById('s_existing_image').value = s.image_url || '';
    document.getElementById('s_image_file').value = '';
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('imagePreviewPlaceholder');
    if (s.image_url) {
        preview.src = s.image_url;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
    } else {
        preview.src = '';
        preview.classList.add('hidden');
        placeholder.classList.remove('hidden');
    }

    document.getElementById('s_cat').value = s.category_id;
    document.getElementById('s_title').value = s.title;
    document.getElementById('s_prov').value = s.provider_id || '';
    document.getElementById('s_psid').value = s.provider_service_id || '';
    document.getElementById('s_cost').value = s.provider_cost || '0.00';
    document.getElementById('s_margin').value = s.admin_margin || '0.00';
    document.getElementById('s_price').value = s.price;
    document.getElementById('s_orig').value = s.original_price || '';
    document.getElementById('s_amt_desc').value = s.amount_description || '';
    document.getElementById('s_badge1').value = s.badge1 || 'Instant';
    document.getElementById('s_badge2').value = s.badge2 || '';
    document.getElementById('s_min_qty').value = s.min_qty || '1';
    document.getElementById('s_max_qty').value = s.max_qty || '1';
    document.getElementById('s_popular').checked = s.is_popular == 1;
    document.getElementById('s_active').checked = s.is_active == 1;
    document.getElementById('serviceModal').classList.remove('hidden');
}

function closeServiceModal() {
    document.getElementById('serviceModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../../includes/admin_footer.php'; ?>
