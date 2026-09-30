<?php
/**
 * FF Panel V2 - Admin Panel Sidebar
 */
$activeNav = $activeNav ?? 'dashboard';

$db = getDbConnection();
$pendingOrdersCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
$pendingDepositsCount = (int)$db->query("SELECT COUNT(*) FROM payments WHERE status = 'pending'")->fetchColumn();
$unreadAlertsCount = getUnreadNotificationCount(null, 'admin');
?>
<!-- Admin Sidebar (Compact Horizontal Navigation on Mobile, Full Vertical Sidebar on Desktop) -->
<aside class="w-full lg:w-64 shrink-0 flex flex-col gap-2.5 lg:gap-5">
    
    <!-- Mobile Compact Horizontal Navigation Strip (lg:hidden) -->
    <div class="lg:hidden w-full bg-[#0F1422] border border-slate-800/80 rounded-xl p-1.5 shadow-md">
        <div class="flex items-center gap-1 overflow-x-auto scrollbar-none py-0.5 px-0.5">
            <a href="/admin" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'dashboard' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>
            <a href="/admin/orders" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'orders' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span>Orders</span>
                <?php if ($pendingOrdersCount > 0): ?><span class="bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded text-[9px] font-bold"><?= $pendingOrdersCount ?></span><?php endif; ?>
            </a>
            <a href="/admin/payments" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'payments' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Deposits</span>
                <?php if ($pendingDepositsCount > 0): ?><span class="bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded text-[9px] font-bold"><?= $pendingDepositsCount ?></span><?php endif; ?>
            </a>
            <a href="/admin/wallet-transactions" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'wallet-transactions' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>Ledger</span>
            </a>
            <a href="/admin/services" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'services' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Services</span>
            </a>
            <a href="/admin/gateways" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'gateways' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Gateways</span>
            </a>
            <a href="/admin/providers" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'providers' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                <span>Providers</span>
            </a>
            <a href="/admin/coupons" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'coupons' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M17 7h.01M17 11h.01M17 15h.01M10 7h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1V8a1 1 0 011-1z"></path></svg>
                <span>Coupons</span>
            </a>
            <a href="/admin/users" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'users' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Users</span>
            </a>
            <a href="/admin/referrals" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'referrals' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Referrals</span>
            </a>
            <a href="/admin/notifications" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'notifications' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span>Alerts</span>
                <?php if ($unreadAlertsCount > 0): ?><span class="bg-[#FF2E51] text-white px-1.5 py-0.5 rounded-full text-[9px] font-bold"><?= $unreadAlertsCount ?></span><?php endif; ?>
            </a>
            <a href="/admin/settings" class="shrink-0 flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-[11px] font-semibold whitespace-nowrap transition-colors <?= $activeNav === 'settings' ? 'bg-rose-600 text-white shadow-sm' : 'bg-[#141A2E] text-slate-300 hover:text-white' ?>">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- Desktop Full Vertical Sidebar (Exact Reference UI, hidden on mobile) -->
    <div class="hidden lg:flex lg:flex-col gap-5">
        <div class="bg-[#0F1422] border border-slate-800/80 rounded-2xl p-3 space-y-1 shadow-xl">
        
        <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Operations</div>

        <!-- Dashboard -->
        <a href="/admin" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'dashboard' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </div>
        </a>

        <!-- Manage Orders -->
        <a href="/admin/orders" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'orders' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span>Orders</span>
            </div>
            <?php if ($pendingOrdersCount > 0): ?>
                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $pendingOrdersCount ?></span>
            <?php endif; ?>
        </a>

        <!-- Deposits & Payments -->
        <a href="/admin/payments" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'payments' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Deposits / UTR</span>
            </div>
            <?php if ($pendingDepositsCount > 0): ?>
                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full animate-pulse"><?= $pendingDepositsCount ?></span>
            <?php endif; ?>
        </a>

        <!-- Global Wallet Ledger -->
        <a href="/admin/wallet-transactions" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'wallet-transactions' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>Wallet Ledger</span>
            </div>
        </a>

        <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-t border-slate-800">Catalog & System</div>

        <!-- Manage Services -->
        <a href="/admin/services" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'services' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Services & Margins</span>
            </div>
        </a>

        <!-- Payment Gateways -->
        <a href="/admin/gateways" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'gateways' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Payment Gateways</span>
            </div>
        </a>

        <!-- API Providers -->
        <a href="/admin/providers" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'providers' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                <span>API Providers</span>
            </div>
        </a>

        <!-- Discount Coupons -->
        <a href="/admin/coupons" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'coupons' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M17 7h.01M17 11h.01M17 15h.01M10 7h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1V8a1 1 0 011-1z"></path></svg>
                <span>Discount Coupons</span>
            </div>
        </a>

        <div class="px-3 pt-3 pb-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-t border-slate-800">Growth & Admin</div>

        <!-- Manage Users -->
        <a href="/admin/users" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'users' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Users</span>
            </div>
        </a>

        <!-- Referral Program -->
        <a href="/admin/referrals" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'referrals' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span>Referral Program</span>
            </div>
        </a>

        <!-- Notifications & Alerts -->
        <a href="/admin/notifications" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'notifications' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span>Alerts & Dispatch</span>
            </div>
            <?php if ($unreadAlertsCount > 0): ?>
                <span class="bg-[#FF2E51] text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center"><?= $unreadAlertsCount ?></span>
            <?php endif; ?>
        </a>

        <!-- Website Settings -->
        <a href="/admin/settings" class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'settings' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Store Settings</span>
            </div>
        </a>

        <div class="border-t border-slate-800 my-2 pt-1"></div>

        <!-- Admin Logout -->
        <a href="/admin/logout" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:text-white hover:bg-rose-950/40 transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span>Admin Logout</span>
        </a>

    </div>

    <!-- Admin Store Summary Widget -->
    <div class="bg-[#0F1422] border border-slate-800/80 rounded-2xl p-4 text-xs space-y-2.5 text-slate-400">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Store Operations</div>
        <div class="flex items-center justify-between text-slate-300">
            <span>Store Status</span>
            <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Online
            </span>
        </div>
        <div class="flex items-center justify-between text-slate-300">
            <span>Order Processing</span>
            <span class="text-rose-400 font-semibold">Automated UID</span>
        </div>
        <div class="flex items-center justify-between text-slate-300">
            <span>Store Currency</span>
            <span class="text-white font-semibold">INR (₹)</span>
        </div>
    </div>

    </div>

</aside>
