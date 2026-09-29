<?php
// Landing Page Header - Distinct Layout
$settings = getSiteSettings();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#0a0d14] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?> — Free Fire Diamonds & Top Up Portal</title>
    <meta name="description" content="Instant Free Fire Diamonds, Weekly & Monthly Memberships, and Elite Pass Top-Up at the best rates in India. Fast, Safe, 100% Legit.">
    <link rel="stylesheet" href="/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-gaming { font-family: 'Rajdhani', sans-serif; }
    </style>
</head>
<body class="min-h-full flex flex-col bg-[#080B11] text-slate-200 antialiased selection:bg-rose-600 selection:text-white">
    <!-- Top Announcement Bar -->
    <div class="bg-gradient-to-r from-rose-900/60 via-red-600/80 to-rose-900/60 border-b border-rose-500/20 text-xs py-2 px-4 text-center font-medium tracking-wide text-white">
        <span class="inline-flex items-center gap-2">
            <span class="animate-pulse flex h-2 w-2 rounded-full bg-rose-400"></span>
            <?= htmlspecialchars($settings['announcement'] ?? '⚡ Instant Auto Delivery • 100% Safe UID Top-Up • 24/7 WhatsApp Support') ?>
        </span>
    </div>

    <!-- Public Landing Navbar -->
    <header class="border-b border-slate-800/80 bg-[#0C101A]/95 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <!-- Brand Logo -->
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-rose-500 to-red-700 flex items-center justify-center shadow-lg shadow-rose-600/25 group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                    </svg>
                </div>
                <div>
                    <span class="font-gaming text-2xl font-bold tracking-wider text-white uppercase flex items-center gap-1">
                        FF PANEL <span class="text-rose-500">STORE</span>
                    </span>
                    <p class="text-[11px] text-slate-400 font-medium tracking-wider uppercase"><?= htmlspecialchars($settings['site_tagline'] ?? 'Fast • Safe • Reliable') ?></p>
                </div>
            </a>

            <!-- Public Nav Links -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="#services" class="text-sm font-semibold text-slate-300 hover:text-rose-400 transition-colors">Services</a>
                <a href="#pricing" class="text-sm font-semibold text-slate-300 hover:text-rose-400 transition-colors">Diamonds Pricing</a>
                <a href="#how-it-works" class="text-sm font-semibold text-slate-300 hover:text-rose-400 transition-colors">How It Works</a>
                <a href="#features" class="text-sm font-semibold text-slate-300 hover:text-rose-400 transition-colors">Why Choose Us</a>
                <a href="#faq" class="text-sm font-semibold text-slate-300 hover:text-rose-400 transition-colors">FAQ</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="flex items-center gap-3">
                <?php if ($currentUser): ?>
                    <a href="/user/dashboard.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-lg shadow-rose-600/20 hover:shadow-rose-600/35 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        User Dashboard
                    </a>
                <?php else: ?>
                    <a href="/login.php" class="text-sm font-semibold text-slate-300 hover:text-white px-4 py-2 rounded-lg hover:bg-slate-800/60 transition-colors">
                        Sign In
                    </a>
                    <a href="/register.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-500 hover:to-red-500 text-white font-semibold text-sm px-5 py-2.5 rounded-xl shadow-lg shadow-rose-600/25 transition-all">
                        Register Now
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>
