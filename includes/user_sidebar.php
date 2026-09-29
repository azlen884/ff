<?php
// User Panel Sidebar - Exact Reference UI Match
$settings = getSiteSettings();
$activeSidebar = $activeSidebar ?? 'home';
?>
<!-- Left Sidebar (Desktop 64w / Mobile responsive) -->
<aside class="w-full lg:w-64 shrink-0 flex flex-col gap-5">
    
    <!-- Vertical Nav Menu Cards -->
    <div class="bg-[#0D121F] border border-slate-800/80 rounded-2xl p-2.5 space-y-1.5 shadow-xl">
        
        <!-- Home -->
        <a href="/user/dashboard.php" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all <?= $activeSidebar === 'home' ? 'bg-[#FF2E51] text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#13192A]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span>Home</span>
            </div>
        </a>

        <!-- Services (with 'New' badge) -->
        <a href="/user/services.php" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all <?= $activeSidebar === 'services' ? 'bg-[#FF2E51] text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#13192A]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Services</span>
            </div>
            <span class="bg-[#FF2E51] text-white text-[10px] font-bold px-2 py-0.5 rounded-full tracking-wider uppercase">New</span>
        </a>

        <!-- Orders -->
        <a href="/user/orders.php" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all <?= $activeSidebar === 'orders' ? 'bg-[#FF2E51] text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#13192A]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                <span>Orders</span>
            </div>
        </a>

        <!-- FF UIDs -->
        <a href="/user/uids.php" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all <?= $activeSidebar === 'uids' ? 'bg-[#FF2E51] text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#13192A]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path></svg>
                <span>FF UIDs</span>
            </div>
        </a>

        <!-- Wallet -->
        <a href="/user/dashboard.php#quick-recharge" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all <?= $activeSidebar === 'wallet' ? 'bg-[#FF2E51] text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#13192A]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                <span>Wallet</span>
            </div>
        </a>

        <!-- Referral -->
        <div class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-[#13192A] cursor-pointer" onclick="alert('Referral Program: Share code FF-REWARDS to earn ₹25 per referral.')">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Referral</span>
            </div>
        </div>

        <!-- Coupons -->
        <div class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-[#13192A] cursor-pointer" onclick="alert('Available Coupons: Use code FF10 for instant ₹10 OFF on orders!')">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 11h.01M7 15h.01M17 7h.01M17 11h.01M17 15h.01M10 7h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1V8a1 1 0 011-1z"></path></svg>
                <span>Coupons</span>
            </div>
        </div>

        <!-- Leaderboard -->
        <div class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-[#13192A] cursor-pointer" onclick="alert('Top Player of the Month: Aaris_Gaming with 48,000 Diamonds top-up!')">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                <span>Leaderboard</span>
            </div>
        </div>

        <!-- Notifications -->
        <div class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-[#13192A] cursor-pointer" onclick="alert('Notifications: 1. Account verified. 2. ₹1,250 balance active. 3. New Evo gun skins added.')">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                <span>Notifications</span>
            </div>
            <span class="bg-[#FF2E51] text-white text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center">3</span>
        </div>

        <!-- Support -->
        <a href="https://wa.me/919876543210" target="_blank" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold text-slate-400 hover:text-white hover:bg-[#13192A]">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Support</span>
            </div>
        </a>

        <!-- Profile -->
        <a href="/user/profile.php" class="flex items-center justify-between px-4 py-3 rounded-xl text-xs font-semibold transition-all <?= $activeSidebar === 'profile' ? 'bg-[#FF2E51] text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#13192A]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>Profile</span>
            </div>
        </a>

    </div>

    <!-- Promo Card: 'Get More With Your Orders' (Exact Reference UI) -->
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-b from-[#181D2E] via-[#121624] to-[#0D101C] border border-rose-500/20 p-5 text-center shadow-xl">
        <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-600/20 border border-rose-500/30 flex items-center justify-center text-[#FF2E51] mb-3 shadow-inner">
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M5 16L3 5l5.5 5L12 4l3.5 6L21 5l-2 11H5m14 3c0 .6-.4 1-1 1H6c-.6 0-1-.4-1-1v-1h14v1z"></path></svg>
        </div>
        <h4 class="text-sm font-bold text-white mb-1">Get More<br>With Your Orders</h4>
        <p class="text-[11px] text-slate-400 mb-4 leading-relaxed">Refer friends and earn exciting rewards!</p>
        <button type="button" onclick="alert('Referral link copied! Share with friends to get ₹25 per referral.')" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-semibold text-xs py-2.5 px-4 rounded-xl shadow-lg shadow-rose-600/25 transition-all">
            <span>Invite Now</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
        </button>
    </div>

    <!-- Sidebar Copyright Footer (Exact Reference UI) -->
    <div class="px-2 text-[11px] text-slate-500 space-y-1">
        <div>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?>.</div>
        <div>All rights reserved.</div>
    </div>

</aside>
