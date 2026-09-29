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

    <!-- Admin Top Navbar (Distinct from Landing & User) -->
    <header class="border-b border-slate-800/80 bg-[#0F1422] z-30">
        <div class="w-full px-4 lg:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="/admin" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-rose-600 to-red-800 flex items-center justify-center text-white font-bold text-sm shadow-md shadow-rose-600/30">
                        ⚡
                    </div>
                    <div>
                        <div class="text-sm font-bold text-white tracking-wide uppercase flex items-center gap-2">
                            <span>FF PANEL</span>
                            <span class="bg-rose-500/20 text-rose-400 text-[10px] font-bold px-2 py-0.5 rounded border border-rose-500/30">ADMIN V1</span>
                        </div>
                        <div class="text-[10px] text-slate-400">Store Management Console</div>
                    </div>
                </a>
            </div>

            <!-- Admin Center Status & Live Time -->
            <div class="hidden md:flex items-center gap-5 text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>MySQL Database: <strong class="text-emerald-400">Connected</strong></span>
                </div>
                <span>•</span>
                <div>
                    Pending Orders: <strong class="text-amber-400"><?= $pendingOrdersCount ?></strong>
                </div>
            </div>

            <!-- Admin Profile & Quick Links -->
            <div class="flex items-center gap-4">
                <a href="/dashboard" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-white bg-[#141A2E] border border-slate-700/60 px-3 py-1.5 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>View Customer Store</span>
                </a>

                <div class="flex items-center gap-3 border-l border-slate-800 pl-4">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-rose-600 to-amber-500 flex items-center justify-center text-white font-bold text-xs">
                        A
                    </div>
                    <div class="text-left hidden sm:block">
                        <div class="text-xs font-bold text-white"><?= htmlspecialchars($currentAdmin['name']) ?></div>
                        <div class="text-[10px] text-rose-400 font-semibold uppercase">Super Admin</div>
                    </div>
                    <a href="/admin/logout" title="Sign Out" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Admin Body Container -->
    <div class="w-full flex-1 flex flex-col lg:flex-row px-4 lg:px-8 py-6 gap-6">
