<?php
// Landing Page Footer - Distinct Layout
$settings = getSiteSettings();
?>
    <!-- Public Landing Footer -->
    <footer class="mt-auto border-t border-slate-800/80 bg-[#070A10] text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
                <!-- Col 1: Brand & Bio -->
                <div class="space-y-4 md:col-span-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-red-700 flex items-center justify-center text-white font-bold shadow-md shadow-rose-600/20">
                            FF
                        </div>
                        <div>
                            <span class="font-gaming text-xl font-bold tracking-wider text-white uppercase">
                                FF PANEL <span class="text-rose-500">STORE</span>
                            </span>
                            <p class="text-[10px] text-slate-400 font-medium tracking-wider uppercase"><?= htmlspecialchars($settings['site_tagline'] ?? 'Fast • Safe • Reliable') ?></p>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed">
                        Leading Free Fire game top-up infrastructure providing instant direct UID recharge, verified memberships, and special bundles with 100% account safety guarantee.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> 99.9% Uptime
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                            <svg class="w-3.5 h-3.5 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            <span>Instant UID Top-Up</span>
                        </span>
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Quick Links</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="/#services" class="hover:text-rose-400 transition-colors">Free Fire Diamonds</a></li>
                        <li><a href="/#services" class="hover:text-rose-400 transition-colors">Weekly & Monthly Pass</a></li>
                        <li><a href="/#services" class="hover:text-rose-400 transition-colors">Elite Pass Season</a></li>
                        <li><a href="/#pricing" class="hover:text-rose-400 transition-colors">Wholesale Rates</a></li>
                        <li><a href="/login" class="hover:text-rose-400 transition-colors">Customer Login</a></li>
                        <li><a href="/register" class="hover:text-rose-400 transition-colors">Create Free Account</a></li>
                    </ul>
                </div>

                <!-- Col 3: Support & Security -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Security & Trust</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>No Game Password Required (UID Only)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Official Server Direct Processing</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Zero Account Ban Risk (100% Legit)</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>256-Bit Encrypted Database Security</span>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Admin -->
                <div>
                    <h4 class="text-sm font-bold text-white uppercase tracking-wider mb-4">Customer Support</h4>
                    <p class="text-xs text-slate-400 mb-3">Our dedicated game support desk is online 24/7 to assist with your orders.</p>
                    <div class="space-y-2 text-xs">
                        <p class="flex items-center gap-2 text-slate-300">
                            <span class="text-rose-400">Email:</span> <?= htmlspecialchars($settings['support_email'] ?? 'support@ffpanel.com') ?>
                        </p>
                        <p class="flex items-center gap-2 text-slate-300">
                            <span class="text-emerald-400">WhatsApp:</span> <?= htmlspecialchars($settings['support_whatsapp'] ?? '+91 98765 43210') ?>
                        </p>
                    </div>
                    <div class="pt-4">
                        <a href="/admin/login" class="text-[11px] text-slate-500 hover:text-rose-400 transition-colors inline-flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Admin Portal Access
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Subfooter -->
            <div class="mt-12 pt-6 border-t border-slate-800/60 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>© <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? 'FF Panel Store') ?>. All rights reserved.</p>
                <div class="flex items-center gap-6">
                    <span>Privacy Policy</span>
                    <span>Terms of Service</span>
                    <span>Refund Policy</span>
                </div>
            </div>
        </div>
    </footer>
    <script src="/js/app.js"></script>
</body>
</html>
