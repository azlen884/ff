<?php
require_once __DIR__ . '/../../config/app.php';
$currentUser = requireUser();
$db = getDbConnection();

$activeNav = 'home';
$activeSidebar = 'home';
$pageTitle = 'FF Panel Store - Home';

// Fetch Categories
$categories = $db->query("SELECT * FROM categories ORDER BY display_order ASC")->fetchAll();

// Fetch Popular Services matching reference image
$stmt = $db->query("SELECT s.*, c.slug as category_slug, c.name as category_name 
                    FROM services s 
                    JOIN categories c ON s.category_id = c.id 
                    WHERE s.is_active = 1 
                    ORDER BY s.is_popular DESC, s.display_order ASC 
                    LIMIT 8");
$popularServices = $stmt->fetchAll();

// Fetch all diamond packages for Quick Recharge widget dropdown
$rechargePackages = $db->query("SELECT id, title, price, amount_description FROM services WHERE is_active = 1 ORDER BY price ASC")->fetchAll();

// Fetch Saved UIDs of current user
$savedUidsStmt = $db->prepare("SELECT * FROM saved_uids WHERE user_id = ? ORDER BY is_default DESC, id DESC");
$savedUidsStmt->execute([$currentUser['id']]);
$userSavedUids = $savedUidsStmt->fetchAll();
$defaultUid = !empty($userSavedUids) ? $userSavedUids[0]['uid_number'] : '';

// Fetch Live Order Activity from MySQL (Real records)
$liveOrders = $db->query("SELECT o.*, s.title as service_title, s.category_id, 
                          TIMESTAMPDIFF(MINUTE, o.created_at, NOW()) as minutes_ago 
                          FROM orders o 
                          JOIN services s ON o.service_id = s.id 
                          WHERE o.status = 'completed' 
                          ORDER BY o.created_at DESC 
                          LIMIT 5")->fetchAll();

// Database-driven / Admin-manageable 2-3 Banners
$dashboardBanners = [
    [
        'image' => !empty($settings['banner_1_image']) ? $settings['banner_1_image'] : '/images/banners/banner_diamonds.svg',
        'badge' => 'Instant Free Fire Diamonds',
        'title' => 'Get Your Free Fire Diamonds Instantly',
        'subtitle' => 'UID top-up, double bonus diamonds, and fast automated delivery.',
        'link' => '/services?category=diamonds',
        'link_text' => 'Top Up Diamonds'
    ],
    [
        'image' => !empty($settings['banner_2_image']) ? $settings['banner_2_image'] : '/images/banners/banner_membership.svg',
        'badge' => 'Weekly & Monthly Pass',
        'title' => 'Save Up to 50% on Memberships',
        'subtitle' => 'Claim daily diamonds, special badges, and exclusive weekly VIP rewards.',
        'link' => '/services?category=membership',
        'link_text' => 'Get Membership'
    ],
    [
        'image' => !empty($settings['banner_3_image']) ? $settings['banner_3_image'] : '/images/banners/banner_evogun.svg',
        'badge' => 'Evo Gun Tokens & Crates',
        'title' => 'Unlock Legendary Evo Gun Tokens',
        'subtitle' => 'Max out your MP40, AK47, and SCAR with fast server token crates.',
        'link' => '/services?category=weapon-skin',
        'link_text' => 'Explore Evo Guns'
    ]
];

require_once __DIR__ . '/../../includes/user_header.php';
require_once __DIR__ . '/../../includes/user_sidebar.php';
?>

<!-- Main Center & Right Content Grid -->
<main class="flex-1 min-w-0 flex flex-col xl:flex-row gap-6">
    
    <!-- Center Column (Hero Banner + Categories + Popular Services) -->
    <div class="flex-1 min-w-0 space-y-6">
        
        <!-- Hero Multi-Banner Responsive Carousel (2-3 Banners) -->
        <div id="dashboardBannerCarousel" class="relative overflow-hidden rounded-2xl sm:rounded-3xl border border-rose-500/30 shadow-2xl group min-h-[200px] sm:min-h-[250px] lg:min-h-[280px]">
            <!-- Slides Track -->
            <div id="bannerSlidesTrack" class="flex transition-transform duration-500 ease-out w-full h-full">
                <?php foreach ($dashboardBanners as $idx => $b): ?>
                    <div class="banner-slide min-w-full shrink-0 relative overflow-hidden flex items-center p-5 sm:p-8 lg:p-10 min-h-[200px] sm:min-h-[250px] lg:min-h-[280px]">
                        <!-- Actual Banner Graphic as Background Image -->
                        <img src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['title']) ?>" class="absolute inset-0 w-full h-full object-cover object-center pointer-events-none select-none" loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>">
                        <!-- Ambient Scrim Overlay for WCAG Contrast -->
                        <div class="absolute inset-0 bg-gradient-to-r from-[#080B11]/90 via-[#080B11]/70 to-[#080B11]/25 pointer-events-none"></div>

                        <!-- Content Grid -->
                        <div class="relative z-10 max-w-xl space-y-2 sm:space-y-3.5">
                            <div class="inline-flex items-center gap-1.5 bg-[#FF2E51]/20 border border-[#FF2E51]/40 px-2.5 py-0.5 sm:py-1 rounded-full text-[#FF2E51] text-[10px] sm:text-[11px] font-bold tracking-wide uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#FF2E51] animate-pulse"></span>
                                <span><?= htmlspecialchars($b['badge']) ?></span>
                            </div>

                            <h2 class="text-lg sm:text-2xl lg:text-3xl font-black text-white tracking-tight leading-tight">
                                <?= htmlspecialchars($b['title']) ?>
                            </h2>

                            <p class="text-[11px] sm:text-xs lg:text-sm text-slate-300 leading-relaxed max-w-md">
                                <?= htmlspecialchars($b['subtitle']) ?>
                            </p>

                            <div class="pt-1 sm:pt-2">
                                <a href="<?= htmlspecialchars($b['link']) ?>" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2 sm:py-2.5 px-4 sm:px-5 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                                    <span><?= htmlspecialchars($b['link_text']) ?></span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Previous & Next Controls (Responsive) -->
            <button type="button" onclick="prevBannerSlide()" aria-label="Previous Banner" class="absolute left-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/60 hover:bg-[#FF2E51] border border-white/20 text-white flex items-center justify-center transition-all opacity-80 sm:opacity-0 sm:group-hover:opacity-100 z-20 shadow-lg cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button type="button" onclick="nextBannerSlide()" aria-label="Next Banner" class="absolute right-2.5 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/60 hover:bg-[#FF2E51] border border-white/20 text-white flex items-center justify-center transition-all opacity-80 sm:opacity-0 sm:group-hover:opacity-100 z-20 shadow-lg cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path></svg>
            </button>

            <!-- Navigation Indicators / Dots -->
            <div class="absolute bottom-3 inset-x-0 flex items-center justify-center gap-1.5 z-20 pointer-events-auto">
                <?php foreach ($dashboardBanners as $idx => $b): ?>
                    <button type="button" onclick="goToBannerSlide(<?= $idx ?>)" aria-label="Slide <?= $idx + 1 ?>" class="banner-dot h-1.5 rounded-full transition-all cursor-pointer <?= $idx === 0 ? 'w-6 bg-[#FF2E51]' : 'w-2 bg-slate-600/80 hover:bg-slate-400' ?>"></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Shop by Category Section (Exact Reference UI) -->
        <div class="space-y-3.5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 text-rose-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 23c-4.97 0-9-4.03-9-9 0-4.03 2.76-7.85 6.13-10.37.52-.39 1.25-.09 1.37.54.34 1.76 1.13 3.39 2.26 4.67.24.27.67.17.78-.17.65-2.02 2.01-3.76 3.73-4.98.53-.38 1.27-.05 1.36.6.38 2.68 1.51 5.16 3.23 7.07 1.48 1.64 2.14 3.76 2.14 5.64 0 4.97-4.03 9-9 9z"/></svg>
                    <h3 class="text-sm font-bold text-white tracking-wide">Shop by Category</h3>
                </div>
                <a href="/services" class="text-xs font-semibold text-rose-400 hover:text-rose-300 flex items-center gap-1">
                    <span>View All</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
            </div>

            <!-- Categories Horizontal Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-5 md:grid-cols-9 gap-2.5">
                <?php
                $categoryIcons = [
                    'diamonds' => '<svg class="w-5 h-5 text-cyan-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7zm0 3.2L18.4 9 12 18.5 5.6 9 12 5.2z"/></svg>',
                    'membership' => '<svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>',
                    'elite-pass' => '<svg class="w-5 h-5 text-rose-400" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h16v16H4V4zm2 2v12h12V6H6zm2 3h8v2H8V9zm0 4h8v2H8v-2z"/></svg>',
                    'character' => '<svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>',
                    'weapon-skin' => '<svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>',
                    'bundle' => '<svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                    'pet' => '<svg class="w-5 h-5 text-orange-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 10c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zm-5 1c-.83 0-1.5.67-1.5 1.5S6.17 14 7 14s1.5-.67 1.5-1.5S7.83 11 7 11zm10 0c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5z"/></svg>',
                    'id-uid' => '<svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>',
                    'special-offers' => '<svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 14h-2v-2h2v2zm0-4h-2V7h2v5z"/></svg>'
                ];
                foreach ($categories as $cat):
                    $icon = $categoryIcons[$cat['slug']] ?? '<svg class="w-5 h-5 text-rose-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7z"/></svg>';
                ?>
                    <a href="/services?category=<?= urlencode($cat['slug']) ?>" class="group bg-[#0D121F] border border-slate-800/90 hover:border-rose-500/50 rounded-2xl p-3 flex flex-col items-center justify-center text-center transition-all hover:scale-105 shadow-md">
                        <div class="w-10 h-10 rounded-xl bg-[#13192A] border border-slate-700/60 flex items-center justify-center mb-2 group-hover:border-rose-500/40 transition-colors">
                            <?= $icon ?>
                        </div>
                        <span class="text-[11px] font-semibold text-slate-300 group-hover:text-white transition-colors truncate w-full"><?= htmlspecialchars($cat['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Popular Services Section (Exact Reference UI) -->
        <div class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/></svg>
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-wide">Popular Services</h3>
                        <p class="text-[11px] text-slate-400">Most purchased services by our customers</p>
                    </div>
                </div>

                <!-- Filter Tabs: All, Diamonds, Membership, Elite Pass, More (Reference UI) -->
                <div class="flex items-center gap-1.5 bg-[#0D121F] p-1 rounded-xl border border-slate-800/80 text-xs">
                    <button type="button" onclick="filterServiceCards('all')" class="category-filter-btn px-3 py-1 rounded-lg font-semibold bg-[#FF2E51] text-white transition-all" data-filter="all">All</button>
                    <button type="button" onclick="filterServiceCards('diamonds')" class="category-filter-btn px-3 py-1 rounded-lg font-semibold text-slate-400 hover:text-white transition-all" data-filter="diamonds">Diamonds</button>
                    <button type="button" onclick="filterServiceCards('membership')" class="category-filter-btn px-3 py-1 rounded-lg font-semibold text-slate-400 hover:text-white transition-all" data-filter="membership">Membership</button>
                    <button type="button" onclick="filterServiceCards('elite-pass')" class="category-filter-btn px-3 py-1 rounded-lg font-semibold text-slate-400 hover:text-white transition-all" data-filter="elite-pass">Elite Pass</button>
                </div>
            </div>

            <!-- Popular Services Cards Grid (8 Cards matching Reference Image) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" id="servicesGrid">
                <?php foreach ($popularServices as $service): ?>
                    <div class="service-card group bg-[#0D121F] border border-slate-800/90 hover:border-rose-500/50 rounded-2xl p-4 transition-all duration-200 hover:shadow-xl hover:shadow-rose-600/10 flex flex-col justify-between" data-category="<?= htmlspecialchars($service['category_slug']) ?>">
                        
                        <div>
                            <!-- Visual Graphic Header based on Service Type -->
                            <div class="w-full h-28 rounded-xl bg-gradient-to-b from-[#141A2D] to-[#0A0D16] border border-slate-800 flex items-center justify-center relative overflow-hidden mb-3.5">
                                <span class="absolute top-2 right-2 text-[9px] font-bold tracking-wider uppercase px-1.5 py-0.5 rounded bg-black/60 text-slate-400 border border-slate-700/60 z-10">
                                    FREE FIRE
                                </span>

                                <?php if (!empty($service['image_url'])): ?>
                                    <img src="<?= htmlspecialchars($service['image_url']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="w-full h-full object-cover">
                                <?php elseif (strpos(strtolower($service['title']), 'membership') !== false): ?>
                                    <div class="text-amber-400 flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center font-gaming text-2xl font-bold">
                                            <?= strpos(strtolower($service['title']), 'weekly') !== false ? 'W' : 'M' ?>
                                        </div>
                                    </div>
                                <?php elseif (strpos(strtolower($service['title']), 'elite') !== false): ?>
                                    <div class="text-rose-400 flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-xl bg-rose-500/15 border border-rose-500/30 flex items-center justify-center font-gaming text-xl font-bold">
                                            PASS
                                        </div>
                                    </div>
                                <?php elseif (strpos(strtolower($service['title']), 'alok') !== false): ?>
                                    <div class="text-indigo-400 flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-xl bg-indigo-500/15 border border-indigo-500/30 flex items-center justify-center font-gaming text-sm font-bold">
                                            ALOK
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <!-- Diamonds Chest graphic -->
                                    <div class="flex items-center gap-1 text-cyan-400">
                                        <svg class="w-10 h-10 drop-shadow-[0_0_12px_rgba(34,211,238,0.4)]" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7zm0 3.2L18.4 9 12 18.5 5.6 9 12 5.2z"/></svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Title & Subtitle -->
                            <h4 class="text-sm font-bold text-white group-hover:text-rose-400 transition-colors leading-snug">
                                <?= htmlspecialchars($service['title']) ?>
                            </h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">
                                <?= htmlspecialchars($service['subtitle']) ?>
                            </p>

                            <!-- Badges (Instant, Popular, Top Selling, etc.) -->
                            <div class="flex items-center gap-1.5 mt-2.5">
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                                    <?= htmlspecialchars($service['badge1'] ?? 'Instant') ?>
                                </span>
                                <?php if (!empty($service['badge2'])): 
                                    $badgeClass = $service['badge2_color'] === 'emerald' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : ($service['badge2_color'] === 'amber' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-rose-500/10 text-rose-400 border-rose-500/20');
                                ?>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $badgeClass ?> border">
                                        <?= htmlspecialchars($service['badge2']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Price & Add to Cart / Recharge Button -->
                        <div class="mt-4 pt-3 border-t border-slate-800">
                            <div class="text-base font-extrabold text-white mb-2">
                                <?= formatCurrency((float)$service['price']) ?>
                            </div>
                            <button type="button" onclick="openOrderModal(<?= $service['id'] ?>, '<?= addslashes(htmlspecialchars($service['title'])) ?>', '<?= formatCurrency((float)$service['price']) ?>', <?= (float)$service['price'] ?>)" class="w-full inline-flex items-center justify-center gap-1.5 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2 px-3 rounded-xl shadow-md shadow-rose-600/25 transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <span>Add to Cart</span>
                            </button>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

    <!-- Right Column (Quick Recharge + Live Order Activity + Save More With Coupons) -->
    <div class="w-full xl:w-80 2xl:w-88 shrink-0 space-y-6">
        
        <!-- Quick Recharge Widget (Exact Reference UI) -->
        <div id="quick-recharge" class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-5 shadow-xl space-y-4">
            
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-lg bg-[#FF2E51]/15 text-[#FF2E51] flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Quick Recharge</h3>
                    <p class="text-[11px] text-slate-400">Top up your Free Fire account instantly</p>
                </div>
            </div>

            <!-- Quick Recharge Form -->
            <form action="/order" method="POST" class="space-y-3.5" id="quickRechargeForm">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="quick_recharge">

                <!-- Enter UID Input with Saved UID Selector -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="quick_uid" class="block text-xs font-semibold text-slate-300">Enter UID</label>
                        <?php if (!empty($userSavedUids)): ?>
                            <select onchange="document.getElementById('quick_uid').value=this.value" class="bg-transparent text-[11px] text-rose-400 font-semibold focus:outline-none cursor-pointer">
                                <option value="" class="bg-[#111728] text-slate-400">Select Saved UID</option>
                                <?php foreach ($userSavedUids as $suid): ?>
                                    <option value="<?= htmlspecialchars($suid['uid_number']) ?>" class="bg-[#111728] text-white">
                                        <?= htmlspecialchars($suid['uid_number']) ?> (<?= htmlspecialchars($suid['player_name'] ?? 'Player') ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                    <input type="text" id="quick_uid" name="player_uid" value="<?= htmlspecialchars($defaultUid) ?>" required placeholder="Enter your Free Fire UID" class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors">
                </div>

                <!-- Select Amount Dropdown -->
                <div>
                    <label for="quick_service" class="block text-xs font-semibold text-slate-300 mb-1.5">Select Amount</label>
                    <div class="relative">
                        <select id="quick_service" name="service_id" required class="w-full bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:outline-none focus:border-rose-500 appearance-none pr-8 transition-colors">
                            <?php foreach ($rechargePackages as $pkg): ?>
                                <option value="<?= $pkg['id'] ?>" <?= $pkg['id'] == 1 ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pkg['title']) ?> - <?= formatCurrency((float)$pkg['price']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                </div>

                <!-- Recharge Now Button -->
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                    <span>Recharge Now</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            </form>

        </div>

        <!-- Live Order Activity Widget (Exact Reference UI) -->
        <div class="bg-[#0D121F] border border-slate-800/90 rounded-2xl p-5 shadow-xl space-y-4">
            
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <h3 class="text-sm font-bold text-white">Live Order Activity</h3>
                    <span class="bg-[#FF2E51] text-white text-[9px] font-extrabold px-1.5 py-0.5 rounded uppercase tracking-wider animate-pulse">Live</span>
                </div>
                <a href="/orders" class="text-xs font-semibold text-rose-400 hover:text-rose-300">View All →</a>
            </div>

            <!-- List of Live Purchases -->
            <div class="space-y-3" id="liveOrderList">
                <?php foreach ($liveOrders as $order): 
                    $minAgo = max(1, (int)$order['minutes_ago']);
                ?>
                    <div class="flex items-center justify-between py-1 border-b border-slate-800/50 last:border-none">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7zm0 3.2L18.4 9 12 18.5 5.6 9 12 5.2z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-white"><?= htmlspecialchars($order['player_name'] ?: 'Customer') ?></div>
                                <div class="text-[10px] text-slate-400">Purchased <?= htmlspecialchars($order['service_title']) ?></div>
                                <div class="text-[9px] text-slate-400"><?= $minAgo ?> min ago</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            Completed
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>

        <!-- Save More With Coupons! Promo Card (Exact Reference UI) -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#1C0E14] via-[#140C12] to-[#0A070B] border border-rose-500/30 p-5 shadow-xl">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-rose-600/20 rounded-full blur-2xl pointer-events-none"></div>

            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-bold text-white leading-tight">
                    Save More<br>With <span class="text-[#FF2E51]">Coupons!</span>
                </h4>
                <div class="w-12 h-12 rounded-xl bg-rose-600/20 border border-rose-500/30 flex items-center justify-center text-rose-400">
                    <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V4a2 2 0 112 2h-2zm0 0V4a2 2 0 10-2 2h2zm-7 4h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2a1 1 0 011-1zm2 4h10v5a1 1 0 01-1 1H8a1 1 0 01-1-1v-5z"></path></svg>
                </div>
            </div>

            <p class="text-[11px] text-slate-400 mb-4 leading-relaxed">
                Use latest promo offers and get amazing instant discounts on every diamond order.
            </p>

            <button type="button" onclick="showToast('Active coupon available: FF10 gives flat discount at checkout!')" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all cursor-pointer">
                <span>View Coupons</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </button>
        </div>

    </div>

</main>

<!-- Order Placement Checkout Modal -->
<div id="orderModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="bg-[#0F1422] border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5 animate-in fade-in zoom-in duration-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                <span>⚡ Instant Order Confirmation</span>
            </h3>
            <button type="button" onclick="closeOrderModal()" class="text-slate-400 hover:text-white text-lg">&times;</button>
        </div>

        <form action="/order" method="POST" class="space-y-4">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="place_order">
            <input type="hidden" name="service_id" id="modal_service_id" value="">

            <!-- Item Summary -->
            <div class="bg-[#141A2D] border border-slate-800 rounded-xl p-3.5 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-white" id="modal_service_title">Item Title</div>
                    <div class="text-[10px] text-slate-400">Direct In-Game Delivery</div>
                </div>
                <div class="text-sm font-black text-rose-400" id="modal_service_price">₹ 0.00</div>
            </div>

            <!-- Player Free Fire UID -->
            <div>
                <label for="modal_player_uid" class="block text-xs font-semibold text-slate-300 mb-1.5">Free Fire Player UID *</label>
                <div class="flex gap-2">
                    <input type="text" id="modal_player_uid" name="player_uid" value="<?= htmlspecialchars($defaultUid) ?>" required placeholder="e.g. 2849182391" class="flex-1 bg-[#13192A] border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500">
                    <button type="button" onclick="verifyUidMock(document.getElementById('modal_player_uid').value)" class="bg-[#1C243B] hover:bg-slate-700 text-xs text-slate-300 px-3 py-2 rounded-xl border border-slate-700 font-semibold transition-colors">
                        Check UID
                    </button>
                </div>
                <p class="text-[10px] text-slate-400 mt-1" id="uidVerifyResult">Enter your numeric in-game Free Fire ID.</p>
            </div>

            <!-- Save UID for future toggle -->
            <div class="flex items-center gap-2 text-xs text-slate-300">
                <input type="checkbox" id="save_uid_toggle" name="save_uid" value="1" checked class="rounded border-slate-700 text-rose-600 focus:ring-0">
                <label for="save_uid_toggle" class="cursor-pointer">Save this UID to my profile for fast 1-click orders</label>
            </div>

            <!-- Payment Method & Balance Check -->
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
function openOrderModal(serviceId, title, priceFormatted, priceNum) {
    document.getElementById('modal_service_id').value = serviceId;
    document.getElementById('modal_service_title').innerText = title;
    document.getElementById('modal_service_price').innerText = priceFormatted;
    document.getElementById('orderModal').classList.remove('hidden');
}

function closeOrderModal() {
    document.getElementById('orderModal').classList.add('hidden');
}

function verifyUidMock(uid) {
    const res = document.getElementById('uidVerifyResult');
    if (!uid || uid.length < 6) {
        res.innerHTML = '<span class="text-rose-400">UID must be at least 6 digits</span>';
        return;
    }
    res.innerHTML = '<span class="text-emerald-400 font-semibold">✓ Verified Player: Active Indian Server</span>';
}

function filterServiceCards(catSlug) {
    const cards = document.querySelectorAll('.service-card');
    const buttons = document.querySelectorAll('.category-filter-btn');

    buttons.forEach(btn => {
        if (btn.getAttribute('data-filter') === catSlug) {
            btn.className = 'category-filter-btn px-3 py-1 rounded-lg font-semibold bg-[#FF2E51] text-white transition-all';
        } else {
            btn.className = 'category-filter-btn px-3 py-1 rounded-lg font-semibold text-slate-400 hover:text-white transition-all';
        }
    });

    cards.forEach(card => {
        if (catSlug === 'all' || card.getAttribute('data-category') === catSlug) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

// Banner Carousel Controller
let currentBannerIdx = 0;
const totalBanners = <?= count($dashboardBanners) ?>;
let bannerTimer = null;

function updateBannerDisplay() {
    const track = document.getElementById('bannerSlidesTrack');
    if (track) {
        track.style.transform = 'translateX(-' + (currentBannerIdx * 100) + '%)';
    }
    const dots = document.querySelectorAll('.banner-dot');
    dots.forEach((dot, idx) => {
        if (idx === currentBannerIdx) {
            dot.className = 'banner-dot h-1.5 w-6 rounded-full bg-[#FF2E51] transition-all cursor-pointer';
        } else {
            dot.className = 'banner-dot h-1.5 w-2 rounded-full bg-slate-600/80 hover:bg-slate-400 transition-all cursor-pointer';
        }
    });
}

function nextBannerSlide() {
    currentBannerIdx = (currentBannerIdx + 1) % totalBanners;
    updateBannerDisplay();
}

function prevBannerSlide() {
    currentBannerIdx = (currentBannerIdx - 1 + totalBanners) % totalBanners;
    updateBannerDisplay();
}

function goToBannerSlide(idx) {
    currentBannerIdx = idx;
    updateBannerDisplay();
    restartBannerTimer();
}

function startBannerTimer() {
    stopBannerTimer();
    bannerTimer = setInterval(nextBannerSlide, 5000);
}

function stopBannerTimer() {
    if (bannerTimer) {
        clearInterval(bannerTimer);
        bannerTimer = null;
    }
}

function restartBannerTimer() {
    stopBannerTimer();
    startBannerTimer();
}

document.addEventListener('DOMContentLoaded', function() {
    const carousel = document.getElementById('dashboardBannerCarousel');
    if (carousel) {
        carousel.addEventListener('mouseenter', stopBannerTimer);
        carousel.addEventListener('mouseleave', startBannerTimer);
        carousel.addEventListener('touchstart', stopBannerTimer, { passive: true });
        carousel.addEventListener('touchend', startBannerTimer, { passive: true });
        startBannerTimer();
    }
});
</script>

<?php require_once __DIR__ . '/../../includes/user_footer.php'; ?>
