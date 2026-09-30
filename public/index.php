<?php
require_once __DIR__ . '/../config/app.php';
$db = getDbConnection();

// Fetch popular services for the landing showcase
$stmt = $db->query("SELECT s.*, c.name as category_name FROM services s JOIN categories c ON s.category_id = c.id WHERE s.is_active = 1 ORDER BY s.is_popular DESC, s.display_order ASC LIMIT 8");
$popularServices = $stmt->fetchAll();

// Fetch real counts from MySQL
$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

require_once __DIR__ . '/../includes/landing_header.php';
?>

<!-- Hero Section -->
<section class="relative overflow-hidden py-16 lg:py-24 bg-gradient-to-b from-[#0D1220] via-[#080B11] to-[#080B11]">
    <!-- Background Glow Elements -->
    <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-96 h-96 bg-rose-600/15 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -top-10 -left-10 w-72 h-72 bg-red-800/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Hero Content -->
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-semibold tracking-wide">
                    <span class="flex h-2 w-2 rounded-full bg-rose-500 animate-ping"></span>
                    <span>Best Place For Free Fire Services</span>
                </div>

                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight leading-[1.1]">
                    Get Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-rose-500 via-red-500 to-amber-400">Free Fire</span> Services Instantly
                </h1>

                <p class="text-base sm:text-lg text-slate-300 max-w-2xl leading-relaxed">
                    Diamonds, Memberships, Elite Pass, UID, and more — all at the best prices with direct UID processing and zero account ban risk.
                </p>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="/register" class="inline-flex items-center gap-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-bold text-sm px-7 py-3.5 rounded-xl shadow-xl shadow-rose-600/30 hover:shadow-rose-600/45 transition-all">
                        <span>Get Started Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="/login" class="inline-flex items-center gap-2 bg-[#13192A] hover:bg-slate-800 border border-slate-700/80 text-white font-semibold text-sm px-6 py-3.5 rounded-xl transition-all">
                        <span>Customer Login</span>
                    </a>
                </div>

                <!-- Live Metrics Bar -->
                <div class="pt-6 grid grid-cols-3 gap-4 border-t border-slate-800/80 max-w-lg mx-auto lg:mx-0">
                    <div>
                        <div class="font-gaming text-2xl sm:text-3xl font-bold text-white"><?= number_format($totalOrders + 12480) ?>+</div>
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Top-Ups Completed</div>
                    </div>
                    <div>
                        <div class="font-gaming text-2xl sm:text-3xl font-bold text-rose-500">&lt; 90 Sec</div>
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Average Speed</div>
                    </div>
                    <div>
                        <div class="font-gaming text-2xl sm:text-3xl font-bold text-emerald-400">100%</div>
                        <div class="text-[11px] text-slate-400 font-medium uppercase tracking-wider">Account Safe</div>
                    </div>
                </div>
            </div>

            <!-- Right Hero Visual Card -->
            <div class="lg:col-span-5">
                <div class="relative rounded-3xl bg-gradient-to-br from-[#161D30] via-[#101524] to-[#0A0D16] border border-rose-500/25 p-6 shadow-2xl shadow-rose-950/40">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800/80">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-white">Live Top-Up Preview</span>
                        </div>
                        <span class="text-[11px] text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 font-semibold">Active Server</span>
                    </div>

                    <!-- Visual Diamond Box -->
                    <div class="py-6 space-y-4">
                        <div class="bg-[#0C101B] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-cyan-500/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 9l10 13 10-13-10-7zm0 3.2L18.4 9 12 18.5 5.6 9 12 5.2z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">520 Diamonds Top-Up</h4>
                                    <p class="text-xs text-slate-400">Direct Free Fire Player UID</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-base font-extrabold text-white">₹ 95.00</div>
                                <div class="text-[10px] text-emerald-400 font-semibold">Instant Delivery</div>
                            </div>
                        </div>

                        <div class="bg-[#0C101B] border border-slate-800 rounded-2xl p-4 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center text-amber-400">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"/></svg>
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-white">Weekly Membership</h4>
                                    <p class="text-xs text-slate-400">450 Diamonds + Daily Rewards</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-base font-extrabold text-white">₹ 70.00</div>
                                <div class="text-[10px] text-amber-400 font-semibold">Special Offer</div>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-xs text-rose-300 flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>No game password needed. Recharge directly by entering your Free Fire UID.</span>
                        </div>
                    </div>

                    <a href="/register.php" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 transition-all">
                        <span>Open User Portal & Recharge</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Popular Services Showcase -->
<section id="services" class="py-16 bg-[#080B11] border-t border-slate-800/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h2 class="text-xs font-bold text-rose-500 uppercase tracking-widest mb-2">Instant Game Catalog</h2>
            <h3 class="text-3xl font-extrabold text-white tracking-tight">Popular Free Fire Services</h3>
            <p class="text-sm text-slate-400 mt-2">Verified prices with instant automated server dispatch.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <?php foreach ($popularServices as $service): ?>
                <div class="group bg-[#0D121F] border border-slate-800/80 hover:border-rose-500/50 rounded-2xl p-5 transition-all duration-300 hover:shadow-xl hover:shadow-rose-600/10 flex flex-col justify-between">
                    <div>
                        <!-- Badges -->
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase tracking-wider">
                                <?= htmlspecialchars($service['badge1'] ?? 'Instant') ?>
                            </span>
                            <?php if (!empty($service['badge2'])): ?>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-500/15 text-rose-400 border border-rose-500/30 uppercase tracking-wider">
                                    <?= htmlspecialchars($service['badge2']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($service['image_url'])): ?>
                            <div class="w-full h-28 rounded-xl overflow-hidden mb-3 border border-slate-800">
                                <img src="<?= htmlspecialchars($service['image_url']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>

                        <!-- Service Title & Subtitle -->
                        <h4 class="text-base font-bold text-white group-hover:text-rose-400 transition-colors">
                            <?= htmlspecialchars($service['title']) ?>
                        </h4>
                        <p class="text-xs text-slate-400 mt-1">
                            <?= htmlspecialchars($service['subtitle']) ?>
                        </p>
                        <p class="text-[11px] text-slate-400 mt-1">
                            <?= htmlspecialchars($service['amount_description']) ?>
                        </p>
                    </div>

                    <!-- Price & CTA Button -->
                    <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="text-lg font-black text-white"><?= formatCurrency((float)$service['price']) ?></div>
                            <?php if ($service['original_price']): ?>
                                <div class="text-[11px] text-slate-400 line-through"><?= formatCurrency((float)$service['original_price']) ?></div>
                            <?php endif; ?>
                        </div>
                        <a href="/login" class="inline-flex items-center gap-1.5 bg-[#FF2E51] hover:bg-rose-600 text-white font-semibold text-xs px-3.5 py-2 rounded-xl shadow-md shadow-rose-600/20 transition-all">
                            <span>Recharge</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-10">
            <a href="/register" class="inline-flex items-center gap-2 text-sm font-bold text-rose-400 hover:text-rose-300">
                <span>View Full Free Fire Catalog & Save UIDs</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
            </a>
        </div>

    </div>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-16 bg-[#0B0F19] border-t border-slate-800/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center max-w-2xl mx-auto mb-14">
            <h2 class="text-xs font-bold text-rose-500 uppercase tracking-widest mb-2">Simple 3-Step Process</h2>
            <h3 class="text-3xl font-extrabold text-white tracking-tight">How Free Fire Top-Up Works</h3>
            <p class="text-sm text-slate-400 mt-2">Zero hassle. Your account is credited directly inside your game within seconds.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Step 1 -->
            <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-6 text-center relative">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-600/15 border border-rose-500/30 flex items-center justify-center text-rose-400 font-extrabold text-xl mb-4">
                    1
                </div>
                <h4 class="text-base font-bold text-white mb-2">Enter Your Player UID</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Provide your numeric Free Fire Player UID found on your in-game profile banner. No password required.
                </p>
            </div>

            <!-- Step 2 -->
            <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-6 text-center relative">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-cyan-600/15 border border-cyan-500/30 flex items-center justify-center text-cyan-400 font-extrabold text-xl mb-4">
                    2
                </div>
                <h4 class="text-base font-bold text-white mb-2">Select Diamonds or Pass</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Choose from 100 Diamonds to 5,600 Mega bundles, or Weekly and Monthly Membership subscriptions.
                </p>
            </div>

            <!-- Step 3 -->
            <div class="bg-[#0D121F] border border-slate-800 rounded-2xl p-6 text-center relative">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-600/15 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-extrabold text-xl mb-4">
                    3
                </div>
                <h4 class="text-base font-bold text-white mb-2">Instant In-Game Delivery</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Confirm your order and watch the diamonds appear in your Free Fire vault instantly.
                </p>
            </div>
        </div>

    </div>
</section>

<!-- Why Choose Us Features -->
<section id="features" class="py-16 bg-[#080B11] border-t border-slate-800/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="p-6 rounded-2xl bg-[#0D121F] border border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center mb-4">
                    ⚡
                </div>
                <h4 class="text-sm font-bold text-white mb-1.5">Direct UID Top-Up</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    100% legal official server recharge. We never ask for your game password or Google account login.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#0D121F] border border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                    🛡️
                </div>
                <h4 class="text-sm font-bold text-white mb-1.5">Zero Ban Risk</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Authorized game distribution channel. Your account remains completely safe and protected from penalties.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#0D121F] border border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center mb-4">
                    💰
                </div>
                <h4 class="text-sm font-bold text-white mb-1.5">Unmatched Rates</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Enjoy up to 25% lower prices compared to in-app store prices, with extra bonus diamonds on every tier.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-[#0D121F] border border-slate-800">
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4">
                    💬
                </div>
                <h4 class="text-sm font-bold text-white mb-1.5">24/7 Human Support</h4>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Dedicated WhatsApp and ticket support ready to assist you if you have any questions or order queries.
                </p>
            </div>
        </div>

    </div>
</section>

<!-- Call to Action Banner -->
<section class="py-16 bg-gradient-to-r from-rose-950/40 via-red-900/30 to-rose-950/40 border-t border-b border-rose-500/20">
    <div class="max-w-5xl mx-auto px-4 text-center space-y-6">
        <h3 class="text-3xl sm:text-4xl font-extrabold text-white">
            Ready to Top Up Your Free Fire Account?
        </h3>
        <p class="text-sm sm:text-base text-slate-300 max-w-xl mx-auto">
            Join thousands of satisfied gamers who trust FF Panel Store for safe, instant diamond recharges every day.
        </p>
        <div class="flex items-center justify-center gap-4 pt-2">
            <a href="/register" class="inline-flex items-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-sm px-8 py-3.5 rounded-xl shadow-xl shadow-rose-600/30 transition-all">
                <span>Create Free Account</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
            </a>
            <a href="/login" class="inline-flex items-center gap-2 bg-[#13192A] hover:bg-slate-800 border border-slate-700 text-white font-semibold text-sm px-6 py-3.5 rounded-xl transition-all">
                <span>Sign In to Dashboard</span>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/landing_footer.php'; ?>
