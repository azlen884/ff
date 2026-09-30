<?php
// Admin Panel Header - Completely Separate Layout
$settings = getSiteSettings();
$currentAdmin = requireAdmin();
$activeNav = $activeNav ?? 'dashboard';
$flash = getFlash();

// Real Counts for badges
$db = getDbConnection();
$pendingOrdersCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0A0D14] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Control') ?> — FF Panel Admin Portal</title>
    <link rel="stylesheet" href="/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0c101a; }
        ::-webkit-scrollbar-thumb { background: #1f293d; border-radius: 4px; }
    </style>
</head>
<body class="min-h-full bg-[#0A0D14] text-slate-200 antialiased flex flex-col selection:bg-rose-600 selection:text-white">

    <!-- Flash message -->
    <?php if ($flash): ?>
        <div class="px-4 py-2.5 text-xs font-semibold flex items-center justify-between <?= $flash['type'] === 'success' ? 'bg-emerald-950/90 border-b border-emerald-500/40 text-emerald-300' : 'bg-rose-950/90 border-b border-rose-500/40 text-rose-300' ?>">
            <div class="max-w-7xl mx-auto w-full flex items-center justify-between">
                <span><?= htmlspecialchars($flash['message']) ?></span>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Admin Top Navbar (Compact on Mobile & Desktop Management Console) -->
    <header class="border-b border-slate-800/80 bg-[#0F1422] z-30">
        <div class="w-full px-2.5 sm:px-4 lg:px-8 py-2 sm:py-3.5 flex items-center justify-between gap-1.5 sm:gap-4">
            <div class="flex items-center gap-1.5 sm:gap-4 shrink-0">
                <!-- Mobile Navigation Toggle Button -->
                <button type="button" onclick="toggleAdminMobileNav()" aria-label="Toggle Admin Menu" class="lg:hidden w-8 h-8 rounded-lg bg-[#141A2E] border border-slate-700/60 flex items-center justify-center text-slate-300 hover:text-white shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <a href="/admin" class="flex items-center gap-2 group shrink-0">
                    <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-lg sm:rounded-xl bg-gradient-to-br from-rose-600 to-red-800 flex items-center justify-center text-white font-bold text-xs sm:text-sm shadow-md shadow-rose-600/30 shrink-0">
                        ⚡
                    </div>
                    <div class="shrink-0">
                        <div class="text-xs sm:text-sm font-bold text-white tracking-wide uppercase flex items-center gap-1.5">
                            <span class="whitespace-nowrap">FF PANEL</span>
                            <span class="bg-rose-500/20 text-rose-400 text-[9px] sm:text-[10px] font-bold px-1.5 py-0.5 rounded border border-rose-500/30">ADMIN</span>
                        </div>
                        <div class="hidden sm:block text-[10px] text-slate-400">Store Management Console</div>
                    </div>
                </a>
            </div>

            <!-- Admin Center Status & Live Time (Desktop only) -->
            <div class="hidden md:flex items-center gap-5 text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>MySQL: <strong class="text-emerald-400">Connected</strong></span>
                </div>
                <span>•</span>
                <div>
                    Pending Orders: <strong class="text-amber-400"><?= $pendingOrdersCount ?></strong>
                </div>
            </div>

            <!-- Admin Profile & Quick Links -->
            <div class="flex items-center gap-1.5 sm:gap-4 shrink-0">
                <a href="/dashboard" target="_blank" title="View Customer Store" class="flex items-center gap-1.5 text-xs font-semibold text-slate-300 hover:text-white bg-[#141A2E] border border-slate-700/60 p-1.5 sm:px-3 sm:py-1.5 rounded-lg transition-colors shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span class="hidden sm:inline">Customer Store</span>
                </a>

                <div class="flex items-center gap-1.5 sm:gap-3 border-l border-slate-800 pl-1.5 sm:pl-4 shrink-0">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-xs shrink-0">
                        A
                    </div>
                    <div class="text-left hidden sm:block">
                        <div class="text-xs font-bold text-white truncate max-w-[90px]"><?= htmlspecialchars($currentAdmin['name']) ?></div>
                        <div class="text-[9px] text-rose-400 font-semibold uppercase">Super Admin</div>
                    </div>
                    <a href="/admin/logout" title="Sign Out" class="text-slate-400 hover:text-rose-400 p-1 sm:p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Mobile Expandable Admin Navigation Drawer (Compact Grid) -->
        <div id="adminMobileDrawer" class="hidden lg:hidden border-t border-slate-800/80 bg-[#0A0D15] px-3 py-2.5 animate-fadeIn">
            <div class="grid grid-cols-4 gap-1.5 pb-2">
                <a href="/admin" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'dashboard' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">⚡</span>
                    <span>Dash</span>
                </a>
                <a href="/admin/orders" class="relative flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'orders' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">📦</span>
                    <span>Orders</span>
                    <?php if ($pendingOrdersCount > 0): ?><span class="absolute top-1 right-1 bg-amber-500 text-black font-bold px-1 rounded-full text-[9px] leading-tight"><?= $pendingOrdersCount ?></span><?php endif; ?>
                </a>
                <a href="/admin/payments" class="relative flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'payments' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">💳</span>
                    <span>Deposits</span>
                    <?php if (!empty($pendingDepositsCount)): ?><span class="absolute top-1 right-1 bg-amber-500 text-black font-bold px-1 rounded-full text-[9px] leading-tight"><?= $pendingDepositsCount ?></span><?php endif; ?>
                </a>
                <a href="/admin/wallet-transactions" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'wallet-transactions' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">💼</span>
                    <span>Ledger</span>
                </a>
                <a href="/admin/services" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'services' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">💎</span>
                    <span>Services</span>
                </a>
                <a href="/admin/gateways" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'gateways' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">🏦</span>
                    <span>Gateways</span>
                </a>
                <a href="/admin/providers" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'providers' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">🔌</span>
                    <span>Providers</span>
                </a>
                <a href="/admin/coupons" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'coupons' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">🎟️</span>
                    <span>Coupons</span>
                </a>
                <a href="/admin/users" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'users' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">👥</span>
                    <span>Users</span>
                </a>
                <a href="/admin/referrals" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'referrals' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">🤝</span>
                    <span>Referrals</span>
                </a>
                <a href="/admin/notifications" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'notifications' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">🔔</span>
                    <span>Alerts</span>
                </a>
                <a href="/admin/settings" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center <?= $activeNav === 'settings' ? 'bg-rose-600 text-white' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                    <span class="text-xs mb-0.5">⚙️</span>
                    <span>Settings</span>
                </a>
            </div>
            <div class="border-t border-slate-800/80 pt-2 flex items-center justify-between text-xs px-1">
                <a href="/dashboard" target="_blank" class="text-slate-400 hover:text-white">View Customer Store ↗</a>
                <a href="/admin/logout" class="text-rose-400 hover:text-rose-300 font-semibold">Sign Out</a>
            </div>
        </div>
    </header>

    <script>
    function toggleAdminMobileNav() {
        const drawer = document.getElementById('adminMobileDrawer');
        if (drawer) {
            drawer.classList.toggle('hidden');
        }
    }
    </script>

    <!-- Admin Body Container -->
    <div class="w-full flex-1 flex flex-col lg:flex-row px-3 sm:px-4 lg:px-8 py-3 sm:py-6 gap-3 sm:gap-6">
