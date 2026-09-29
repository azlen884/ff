<?php
// Admin Panel Sidebar - Distinct Admin Navigation
$activeNav = $activeNav ?? 'dashboard';

// Pending orders count for badge
$db = getDbConnection();
$pendingOrdersCount = (int)$db->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>
<!-- Admin Sidebar -->
<aside class="w-full lg:w-64 shrink-0 flex flex-col gap-5">
    
    <div class="bg-[#0F1422] border border-slate-800/80 rounded-2xl p-3 space-y-1.5 shadow-xl">
        
        <div class="px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">Main Menu</div>

        <!-- Dashboard -->
        <a href="/admin/dashboard.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'dashboard' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </div>
        </a>

        <!-- Manage Orders -->
        <a href="/admin/orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'orders' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <span>Manage Orders</span>
            </div>
            <?php if ($pendingOrdersCount > 0): ?>
                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-bold px-2 py-0.5 rounded-full"><?= $pendingOrdersCount ?></span>
            <?php endif; ?>
        </a>

        <!-- Manage Services / Products -->
        <a href="/admin/services.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'services' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <span>Manage Services</span>
            </div>
        </a>

        <!-- Manage Users -->
        <a href="/admin/users.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'users' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span>Manage Users</span>
            </div>
        </a>

        <!-- Basic Website Settings -->
        <a href="/admin/settings.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all <?= $activeNav === 'settings' ? 'bg-rose-600 text-white shadow-lg shadow-rose-600/30' : 'text-slate-400 hover:text-white hover:bg-[#141A2E]' ?>">
            <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span>Website Settings</span>
            </div>
        </a>

        <div class="border-t border-slate-800 my-2 pt-2"></div>

        <!-- Admin Logout -->
        <a href="/admin/logout.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-400 hover:text-white hover:bg-rose-950/40 transition-colors">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
            <span>Admin Logout</span>
        </a>

    </div>

    <!-- Admin System Status Widget -->
    <div class="bg-[#0F1422] border border-slate-800/80 rounded-2xl p-4 text-xs space-y-2 text-slate-400">
        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Database Engine</div>
        <div class="flex items-center justify-between text-slate-300">
            <span>MySQL Version</span>
            <span class="font-mono text-emerald-400">10.11 MariaDB</span>
        </div>
        <div class="flex items-center justify-between text-slate-300">
            <span>Server Mode</span>
            <span class="text-rose-400 font-semibold">Production V1</span>
        </div>
        <div class="flex items-center justify-between text-slate-300">
            <span>Security</span>
            <span class="text-emerald-400 font-semibold">PDO Prepared</span>
        </div>
    </div>

</aside>
