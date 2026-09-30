<?php
// User Panel Header - Exact Reference UI Match
$settings = getSiteSettings();
$currentUser = requireUser();
$activeNav = $activeNav ?? 'home';
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#080B11] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'User Dashboard') ?> — <?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?></title>
    <link rel="stylesheet" href="/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-gaming { font-family: 'Rajdhani', sans-serif; }
        /* Scrollbar styling */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #0b0e17; }
        ::-webkit-scrollbar-thumb { background: #1f293d; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #ff2e51; }
    </style>
</head>
<body class="min-h-full bg-[#080B11] text-slate-100 antialiased selection:bg-rose-600 selection:text-white flex flex-col">

    <!-- Flash Notifications -->
    <?php if ($flash): ?>
        <div class="px-4 py-2.5 text-sm font-medium flex items-center justify-between <?= $flash['type'] === 'success' ? 'bg-emerald-950/80 border-b border-emerald-500/30 text-emerald-300' : 'bg-rose-950/80 border-b border-rose-500/30 text-rose-300' ?>">
            <div class="max-w-7xl mx-auto w-full flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <?php if ($flash['type'] === 'success'): ?>
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <?php else: ?>
                        <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <?php endif; ?>
                    <span><?= htmlspecialchars($flash['message']) ?></span>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-slate-400 hover:text-white">&times;</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- User Panel Top Navbar (Compact on Mobile & Reference UI on Desktop) -->
    <header class="bg-[#0D121F] border-b border-slate-800/80 sticky-none z-30">
        <div class="w-full px-2.5 sm:px-4 lg:px-8 py-2 sm:py-3.5 flex items-center justify-between gap-1.5 sm:gap-4">
            
            <!-- Left: Mobile Menu Toggle & Brand Logo -->
            <div class="flex items-center gap-1.5 sm:gap-6 shrink-0">
                <!-- Mobile Navigation Toggle Button -->
                <button type="button" onclick="toggleUserMobileNav()" aria-label="Toggle Menu" class="lg:hidden w-8 h-8 rounded-lg bg-[#13192A] border border-slate-700/60 flex items-center justify-center text-slate-300 hover:text-white shrink-0 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>

                <a href="/dashboard" class="flex items-center gap-2 group shrink-0">
                    <div class="w-7 h-7 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-gradient-to-br from-[#FF2E51] to-[#D91438] flex items-center justify-center shadow-lg shadow-rose-600/30 group-hover:scale-105 transition-transform duration-200 shrink-0">
                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                        </svg>
                    </div>
                    <div class="shrink-0">
                        <div class="font-gaming text-xs sm:text-base lg:text-xl font-bold tracking-wider text-white uppercase whitespace-nowrap">
                            FF PANEL <span class="text-[#FF2E51]">STORE</span>
                        </div>
                        <div class="hidden sm:block text-[10px] text-slate-400 font-medium tracking-wider uppercase"><?= htmlspecialchars($settings['site_tagline'] ?? 'Fast • Safe • Reliable') ?></div>
                    </div>
                </a>

                <!-- Search Input Bar (Reference UI Desktop) -->
                <div class="hidden xl:block w-80 2xl:w-96">
                    <form action="/services" method="GET" class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="q" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" placeholder="Search for services (e.g. Diamond, UID, ID, etc...)" class="w-full bg-[#13192A] border border-slate-700/60 rounded-xl pl-10 pr-4 py-2 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-rose-500 transition-colors">
                    </form>
                </div>
            </div>

            <!-- Middle: Horizontal Nav Tabs (Reference UI Desktop) -->
            <div class="hidden lg:flex items-center gap-1 bg-[#13192A]/80 p-1 rounded-2xl border border-slate-800/80">
                <a href="/dashboard" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'home' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Home
                </a>
                <a href="/services" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'services' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    Services
                </a>
                <a href="/orders" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'orders' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Orders
                </a>
                <a href="/wallet" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'wallet' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Wallet
                </a>
                <a href="/referrals" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'referrals' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Referrals
                </a>
                <a href="/uids" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'uids' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    UIDs
                </a>
                <a href="/profile" class="flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'profile' ? 'bg-[#FF2E51] text-white shadow-md shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/50' ?>">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profile
                </a>
            </div>

            <!-- Right: Notifications, Wallet Balance & User Profile -->
            <div class="flex items-center gap-1.5 sm:gap-3.5 shrink-0">
                
                <!-- Notification Bell with Real MySQL Unread Count Badge -->
                <?php $userUnread = getUnreadNotificationCount($currentUser['id'], 'user'); ?>
                <div class="relative shrink-0">
                    <a href="/notifications" aria-label="Notifications" class="w-7 h-7 sm:w-10 sm:h-10 rounded-lg sm:rounded-xl bg-[#13192A] border border-slate-700/60 flex items-center justify-center text-slate-300 hover:text-white hover:border-slate-600 transition-colors">
                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </a>
                    <?php if ($userUnread > 0): ?>
                        <span class="absolute -top-1 -right-1 w-3.5 h-3.5 sm:w-5 sm:h-5 rounded-full bg-[#FF2E51] text-white text-[9px] sm:text-[10px] font-bold flex items-center justify-center border border-[#0D121F]">
                            <?= $userUnread > 9 ? '9+' : $userUnread ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Wallet Balance Card (Compact on mobile) -->
                <a href="/wallet" title="Wallet Balance" class="flex items-center gap-1 sm:gap-2.5 bg-[#13192A] border border-slate-700/60 rounded-lg sm:rounded-xl px-2 py-1 sm:px-3.5 sm:py-2 hover:border-rose-500/40 transition-colors shrink-0">
                    <div class="w-5 h-5 sm:w-7 sm:h-7 rounded bg-rose-500/15 border border-rose-500/25 flex items-center justify-center text-[#FF2E51] shrink-0">
                        <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div class="leading-none text-left">
                        <div class="text-[11px] sm:text-xs font-bold text-white font-mono whitespace-nowrap"><?= formatCurrency((float)$currentUser['wallet_balance']) ?></div>
                        <div class="hidden sm:block text-[9px] text-slate-400 font-medium mt-0.5">Wallet</div>
                    </div>
                </a>

                <!-- User Profile Capsule with Avatar & Dropdown -->
                <div class="relative group shrink-0" id="userMenuDropdown">
                    <button type="button" aria-label="User Account" class="flex items-center gap-1.5 sm:gap-2.5 bg-[#13192A] border border-slate-700/60 rounded-lg sm:rounded-xl p-0.5 sm:px-3 sm:py-1.5 hover:border-slate-600 transition-colors">
                        <div class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-tr from-[#FF2E51] to-red-400 flex items-center justify-center text-white font-bold text-[10px] sm:text-xs shadow-md shadow-rose-600/20 shrink-0">
                            <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                        </div>
                        <div class="text-left hidden md:block">
                            <div class="text-xs font-bold text-white tracking-wide leading-tight truncate max-w-[90px]"><?= htmlspecialchars($currentUser['name']) ?></div>
                            <div class="text-[10px] text-slate-400 font-medium capitalize"><?= htmlspecialchars($currentUser['role']) ?></div>
                        </div>
                        <svg class="w-3 h-3 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <!-- Dropdown Box -->
                    <div class="hidden group-hover:block absolute right-0 mt-1 w-48 bg-[#111728] border border-slate-700/80 rounded-xl shadow-2xl py-2 z-50">
                        <div class="px-4 py-2 border-b border-slate-800 text-[11px] text-slate-400">
                            Signed in as <span class="text-white font-semibold block truncate"><?= htmlspecialchars($currentUser['email']) ?></span>
                        </div>
                        <a href="/profile" class="block px-4 py-2 text-xs text-slate-300 hover:text-white hover:bg-slate-800/60">My Profile</a>
                        <a href="/uids" class="block px-4 py-2 text-xs text-slate-300 hover:text-white hover:bg-slate-800/60">Manage FF UIDs</a>
                        <a href="/orders" class="block px-4 py-2 text-xs text-slate-300 hover:text-white hover:bg-slate-800/60">Order History</a>
                        <a href="/deposit" class="block px-4 py-2 text-xs text-emerald-400 hover:bg-slate-800/60">+ Add Funds</a>
                        <div class="border-t border-slate-800 my-1"></div>
                        <a href="/logout" class="block px-4 py-2 text-xs text-rose-400 hover:text-rose-300 hover:bg-rose-950/30">Sign Out</a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Mobile Expandable Navigation Drawer (Compact Grid, Toggled by Mobile Hamburger) -->
        <div id="userMobileDrawer" class="hidden lg:hidden border-t border-slate-800/80 bg-[#0A0E18] px-3 py-2.5 animate-fadeIn">
            <div class="grid grid-cols-4 gap-1.5 pb-2">
                <a href="/dashboard" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'home' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Home</span>
                </a>
                <a href="/services" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'services' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    <span>Services</span>
                </a>
                <a href="/orders" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'orders' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span>Orders</span>
                </a>
                <a href="/wallet" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'wallet' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    <span>Wallet</span>
                </a>
                <a href="/deposit" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center bg-emerald-950/40 border border-emerald-500/30 text-emerald-400">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    <span>Deposit</span>
                </a>
                <a href="/uids" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'uids' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                    <span>UIDs</span>
                </a>
                <a href="/referrals" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'referrals' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Refer</span>
                </a>
                <a href="/profile" class="flex flex-col items-center justify-center p-2 rounded-xl text-[11px] font-semibold text-center transition-colors <?= $activeNav === 'profile' ? 'bg-[#FF2E51] text-white shadow-sm' : 'bg-[#13192A] text-slate-300 hover:text-white' ?>">
                    <svg class="w-4 h-4 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Profile</span>
                </a>
            </div>
            <div class="border-t border-slate-800/80 pt-2 flex items-center justify-between text-xs px-1">
                <span class="text-slate-400 truncate max-w-[200px]">User: <strong class="text-white"><?= htmlspecialchars($currentUser['name']) ?></strong></span>
                <a href="/logout" class="text-rose-400 hover:text-rose-300 font-semibold shrink-0">Sign Out</a>
            </div>
        </div>
    </header>

    <script>
    function toggleUserMobileNav() {
        const drawer = document.getElementById('userMobileDrawer');
        if (drawer) {
            drawer.classList.toggle('hidden');
        }
    }
    </script>

    <!-- App Body Container: Sidebar + Main Content -->
    <div class="max-w-7xl mx-auto w-full flex-1 flex flex-col lg:flex-row px-3 sm:px-4 lg:px-8 py-3 sm:py-6 gap-3 sm:gap-6">
