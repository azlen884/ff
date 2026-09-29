<?php
/**
 * FF Panel Store - 404 Not Found Page
 * Clean, themed error page with helpful navigation
 */
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-[#080B11] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Page Not Found — FF Panel Store</title>
    <link rel="stylesheet" href="/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Rajdhani:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .font-gaming { font-family: 'Rajdhani', sans-serif; }
    </style>
</head>
<body class="min-h-full bg-[#080B11] text-slate-200 antialiased selection:bg-rose-600 selection:text-white flex flex-col items-center justify-center p-4 sm:p-6 lg:p-10">

    <div class="max-w-md w-full text-center space-y-6">
        
        <!-- Brand Header -->
        <a href="/" class="inline-flex items-center gap-3 group">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-[#FF2E51] to-red-700 flex items-center justify-center shadow-xl shadow-rose-600/30 group-hover:scale-105 transition-transform">
                <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                </svg>
            </div>
            <div class="text-left">
                <div class="font-gaming text-2xl font-bold tracking-wider text-white uppercase flex items-center gap-1.5">
                    FF PANEL <span class="text-[#FF2E51]">STORE</span>
                </div>
                <div class="text-[10px] text-slate-400 font-medium tracking-wider uppercase">Fast • Safe • Reliable</div>
            </div>
        </a>

        <!-- 404 Card -->
        <div class="bg-[#0D121F] border border-slate-800/90 rounded-3xl p-8 sm:p-10 shadow-2xl space-y-5">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-[#FF2E51] font-gaming text-4xl font-extrabold tracking-widest shadow-inner">
                404
            </div>

            <div class="space-y-2">
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight">Page Not Found</h1>
                <p class="text-xs text-slate-400 leading-relaxed max-w-sm mx-auto">
                    The requested route or URL does not exist or may have been updated to a clean URL.
                </p>
            </div>

            <!-- Helpful Navigation Actions -->
            <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="/" class="w-full inline-flex items-center justify-center gap-2 bg-[#FF2E51] hover:bg-rose-600 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-lg shadow-rose-600/30 hover:shadow-rose-600/50 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Store Home</span>
                </a>
                <a href="/dashboard" class="w-full inline-flex items-center justify-center gap-2 bg-[#13192A] hover:bg-slate-800 text-slate-200 hover:text-white font-semibold text-xs py-3 px-4 rounded-xl border border-slate-700/80 transition-all">
                    <span>User Dashboard</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>

            <div class="pt-2 border-t border-slate-800/80 text-[11px] text-slate-500">
                Are you an administrator? <a href="/admin/login" class="text-rose-400 hover:text-rose-300 font-semibold underline ml-1">Access Admin Console &rarr;</a>
            </div>
        </div>

        <div class="text-[11px] text-slate-500">
            FF Panel Store • Secure URL Router
        </div>

    </div>

</body>
</html>
